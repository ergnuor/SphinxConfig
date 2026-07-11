<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Input\Decoder;

use Ergnuor\SphinxConfig\Domain\ConfigName;
use Ergnuor\SphinxConfig\Exception\ConfigContext;
use Ergnuor\SphinxConfig\Exception\ConfigProcessingExceptionHandler;
use Ergnuor\SphinxConfig\Exception\InvalidArgumentException;
use Ergnuor\SphinxConfig\Input\Declaration\BlockInheritance;
use Ergnuor\SphinxConfig\Input\Declaration\BlockReference;
use Ergnuor\SphinxConfig\Input\Declaration\Config;
use Ergnuor\SphinxConfig\Input\Declaration\Parameter;
use Ergnuor\SphinxConfig\Input\Declaration\PlaceholderValue;
use Ergnuor\SphinxConfig\Input\Declaration\SectionBlock;
use Ergnuor\SphinxConfig\Input\Payload\PayloadInterface;
use Ergnuor\SphinxConfig\Input\Payload\PhpArray\ConfigFragment;
use Ergnuor\SphinxConfig\Input\Payload\PhpArray\SectionBlockFragment;
use Ergnuor\SphinxConfig\Input\Payload\PhpArray\SectionFragment;
use Ergnuor\SphinxConfig\Input\Payload\PhpArrayPayload;
use Ergnuor\SphinxConfig\Schema\ConfigSchemaInterface;
use Ergnuor\SphinxConfig\Schema\SectionSchemaInterface;
use Ergnuor\SphinxConfig\Support\StringGuard;
use Override;

/**
 * @phpstan-import-type RawSectionBlockConfigType from PhpArraySectionBlockConfigNormalizer
 * @phpstan-import-type NormalizedSectionBlockConfigType from PhpArraySectionBlockConfigNormalizer
 * @phpstan-import-type NormalizedConfigParameterType from PhpArraySectionBlockConfigNormalizer
 */
final readonly class PhpArrayDecoder implements DecoderInterface
{
    private PhpArraySectionBlockConfigNormalizer $sectionBlockConfigNormalizer;

    public function __construct()
    {
        $this->sectionBlockConfigNormalizer = new PhpArraySectionBlockConfigNormalizer();
    }

    #[Override]
    public function decode(ConfigName $configName, PayloadInterface $payload, ConfigSchemaInterface $schema): Config
    {
        $configContext = ConfigContext::config((string) $configName);

        return ConfigProcessingExceptionHandler::runInConfigContext(
            $configContext,
            fn(ConfigContext $currentContext) => $this->doDecode($payload, $currentContext, $schema),
        );
    }

    private function doDecode(
        PayloadInterface $payload,
        ConfigContext $configContext,
        ConfigSchemaInterface $schema
    ): Config {
        if (!($payload instanceof PhpArrayPayload)) {
            throw new InvalidArgumentException("Payload must be an instance of '" . PhpArrayPayload::class . "'.");
        }

        $sectionBlocks = [];
        foreach ($payload->fragments as $fragment) {
            if ($fragment instanceof ConfigFragment) {
                foreach ($fragment->data as $sectionName => $sectionConfig) {
                    $newSectionBlocks = $this->decodeSection($sectionName, $sectionConfig, $configContext, $schema);
                    foreach ($newSectionBlocks as $sectionBlock) {
                        $sectionBlocks[] = $sectionBlock;
                    }
                }

            } elseif ($fragment instanceof SectionFragment) {
                $newSectionBlocks = $this->decodeSection($fragment->sectionName, $fragment->data, $configContext, $schema);
                foreach ($newSectionBlocks as $sectionBlock) {
                    $sectionBlocks[] = $sectionBlock;
                }

            } elseif ($fragment instanceof SectionBlockFragment) {
                $sectionBlocks[] = $this->decodeSectionBlock(
                    $fragment->sectionName,
                    $fragment->blockName,
                    $fragment->data,
                    $configContext->withSection($fragment->sectionName),
                    $schema,
                );

            } else {
                throw new InvalidArgumentException("Unknown fragment type '" . get_debug_type($fragment) . "'.");
            }

        }

        return new Config($sectionBlocks);
    }

    /**
     * @return list<SectionBlock>
     */
    private function decodeSection(
        mixed $sectionName,
        mixed $sectionConfig,
        ConfigContext $configContext,
        ConfigSchemaInterface $schema,
    ): array {
        $sectionName = $this->validateAndNormalizeSectionName($sectionName);

        return ConfigProcessingExceptionHandler::runInConfigContext(
            $configContext->withSection($sectionName),
            fn(ConfigContext $currentContext) => $this->doDecodeSection(
                $sectionName,
                $sectionConfig,
                $currentContext,
                $schema
            ),
        );
    }

    /**
     * @return list<SectionBlock>
     */
    private function doDecodeSection(
        string $sectionName,
        mixed $sectionConfig,
        ConfigContext $configContext,
        ConfigSchemaInterface $schema,
    ): array {
        if (!is_array($sectionConfig)) {
            throw new InvalidArgumentException('Section config must be an array.');
        }

        $sectionSchema = $schema->getSectionSchema($sectionName);

        $sectionBlockDeclarations = [];
        if ($sectionSchema->isMultiBlock()) {
            foreach ($sectionConfig as $blockName => $blockConfig) {
                $blockName = $this->validateAndNormalizeBlockName($blockName);
                if (!is_array($blockConfig)) {
                    throw new InvalidArgumentException('Section block config must be an array.');
                }

                $sectionBlockDeclarations[] = $this->decodeSectionBlock(
                    $sectionName,
                    $blockName,
                    $blockConfig,
                    $configContext,
                    $schema,
                );
            }
        } else {
            $sectionBlockDeclarations[] = $this->decodeSectionBlock(
                $sectionName,
                $sectionName,
                $sectionConfig,
                $configContext,
                $schema,
            );
        }

        return $sectionBlockDeclarations;
    }

    private function validateAndNormalizeSectionName(mixed $sectionName): string
    {
        return $this->validateAndNormalizeNotEmptyString(
            $sectionName,
            'Section name must be a string.',
            'Section name cannot be empty.'
        );
    }

    private function validateAndNormalizeBlockName(mixed $blockName): string
    {
        return $this->validateAndNormalizeNotEmptyString(
            $blockName,
            'Block name must be a string.',
            'Block name cannot be empty.'
        );
    }

    private function validateAndNormalizeNotEmptyString(
        mixed $value,
        string $notStringMessage,
        string $emptyMessage
    ): string {
        if (!is_string($value)) {
            throw new InvalidArgumentException($notStringMessage);
        }

        return StringGuard::requireTrimmedNotEmpty(
            $value,
            $emptyMessage,
        );
    }

    /**
     * @param RawSectionBlockConfigType $sectionBlockConfig
     */
    private function decodeSectionBlock(
        string $sectionName,
        string $blockName,
        array $sectionBlockConfig,
        ConfigContext $configContext,
        ConfigSchemaInterface $schema,
    ): SectionBlock {
        return ConfigProcessingExceptionHandler::runInConfigContext(
            $configContext->withBlock($blockName),
            fn(ConfigContext $currentContext) => $this->doDecodeSectionBlock(
                $sectionName,
                $blockName,
                $sectionBlockConfig,
                $currentContext,
                $schema,
            ),
        );
    }

    /**
     * @param RawSectionBlockConfigType $sectionBlockConfig
     */
    private function doDecodeSectionBlock(
        string $sectionName,
        string $blockName,
        array $sectionBlockConfig,
        ConfigContext $configContext,
        ConfigSchemaInterface $schema,
    ): SectionBlock {
        $normalizedSectionBlockConfig = $this->sectionBlockConfigNormalizer->normalize($sectionBlockConfig, $configContext);

        return new SectionBlock(
            $sectionName,
            $blockName,
            $this->decodeSectionBlockConfigParameters(
                $normalizedSectionBlockConfig,
                $sectionName,
                $configContext,
                $schema
            ),
            $normalizedSectionBlockConfig['isTemplate'],
            $this->decodeSectionBlockInheritance($normalizedSectionBlockConfig),
            $this->decodeSectionBlockPlaceholderValues($normalizedSectionBlockConfig)
        );
    }

    /**
     * @param NormalizedSectionBlockConfigType $normalizedSectionBlockConfig
     * @return list<Parameter>
     */
    private function decodeSectionBlockConfigParameters(
        array $normalizedSectionBlockConfig,
        string $sectionName,
        ConfigContext $configContext,
        ConfigSchemaInterface $schema,
    ): array {
        $config = $normalizedSectionBlockConfig['config'];

        $sectionSchema = $schema->getSectionSchema($sectionName);

        $parameters = [];
        foreach ($config as $parameterName => $parameterValue) {
            $parameterName = StringGuard::requireTrimmedNotEmpty(
                $parameterName,
                'Parameter name cannot be empty.',
            );

            $newParameters = ConfigProcessingExceptionHandler::runInConfigContext(
                $configContext->withParameter($parameterName),
                fn(ConfigContext $currentContext) => $this->decodeConfigParameter(
                    $parameterName,
                    $sectionSchema,
                    $parameterValue
                ),
            );

            foreach ($newParameters as $parameter) {
                $parameters[] = $parameter;
            }
        }

        return $parameters;
    }

    /**
     * @param NormalizedConfigParameterType $parameterValue
     * @return list<Parameter>
     */
    private function decodeConfigParameter(
        string $parameterName,
        SectionSchemaInterface $sectionSchema,
        string|array $parameterValue,
    ): array {
        $parameterSchema = $sectionSchema->getParameterSchema($parameterName);

        if ($parameterSchema->isMultiValue) {
            if (!is_array($parameterValue)) {
                throw new InvalidArgumentException('Multi-value parameter value must be an array.');
            }

            $parameters = [];
            foreach ($parameterValue as $key => $value) {
                $alias = null;
                if (is_string($key)) {
                    $alias = $key;
                }

                $parameters[] = new Parameter(
                    $parameterName,
                    $value,
                    $alias
                );
            }

            return $parameters;
        }

        if (!is_string($parameterValue)) {
            throw new InvalidArgumentException('Single-value parameter value must be a string.');
        }

        return [
            new Parameter(
                $parameterName,
                $parameterValue,
            ),
        ];
    }

    /**
     * @param NormalizedSectionBlockConfigType $normalizedSectionBlockConfig
     */
    private function decodeSectionBlockInheritance(array $normalizedSectionBlockConfig): ?BlockInheritance
    {
        $inheritance = $normalizedSectionBlockConfig['inheritance'];

        if ($inheritance === null) {
            return null;
        }

        $extendParts = explode('@', $inheritance['extends']);

        if (count($extendParts) > 2) {
            throw new InvalidArgumentException("Invalid inheritance.extends value '{$inheritance['extends']}'; expected 'block' or 'config@block'.");
        }

        if (isset($extendParts[1])) {
            $extendsConfigName = $extendParts[0];
            $extendsBlockName = $extendParts[1];
        } else {
            $extendsConfigName = null;
            $extendsBlockName = $inheritance['extends'];
        }

        $blockReference = new BlockReference(
            $extendsBlockName,
            $extendsConfigName,
        );

        return new BlockInheritance(
            $blockReference,
            $inheritance['appendValuesFor'],
        );
    }

    /**
     * @param NormalizedSectionBlockConfigType $normalizedSectionBlockConfig
     * @return list<PlaceholderValue>
     */
    private function decodeSectionBlockPlaceholderValues(array $normalizedSectionBlockConfig): array
    {
        $placeholderValues = [];

        foreach ($normalizedSectionBlockConfig['placeholderValues'] as $placeholderName => $placeholderValue) {

            $placeholderValues[] = new PlaceholderValue(
                $placeholderName,
                $placeholderValue,
            );
        }

        return $placeholderValues;
    }
}
