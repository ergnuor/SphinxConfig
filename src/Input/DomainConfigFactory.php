<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Input;

use Ergnuor\SphinxConfig\Domain\AliasedValue;
use Ergnuor\SphinxConfig\Domain\Block;
use Ergnuor\SphinxConfig\Domain\BlockInheritance;
use Ergnuor\SphinxConfig\Domain\BlockName;
use Ergnuor\SphinxConfig\Domain\BlockReference;
use Ergnuor\SphinxConfig\Domain\Config;
use Ergnuor\SphinxConfig\Domain\ConfigName;
use Ergnuor\SphinxConfig\Domain\MultiValueParameter;
use Ergnuor\SphinxConfig\Domain\ParameterInterface;
use Ergnuor\SphinxConfig\Domain\ParameterName;
use Ergnuor\SphinxConfig\Domain\PlaceholderValue;
use Ergnuor\SphinxConfig\Domain\Section;
use Ergnuor\SphinxConfig\Domain\SectionName;
use Ergnuor\SphinxConfig\Domain\SingleValueParameter;
use Ergnuor\SphinxConfig\Exception\ConfigContext;
use Ergnuor\SphinxConfig\Exception\ConfigProcessingExceptionHandler;
use Ergnuor\SphinxConfig\Exception\InvalidArgumentException;
use Ergnuor\SphinxConfig\Input\Declaration as InputDeclaration;
use Ergnuor\SphinxConfig\Schema\ConfigSchemaInterface;
use Ergnuor\SphinxConfig\Schema\SectionSchemaInterface;

/**
 * @phpstan-type GroupedParameterDeclarations non-empty-list<InputDeclaration\Parameter>
 */
final readonly class DomainConfigFactory
{
    public function __construct() {}

    public function create(
        ConfigName $configName,
        InputDeclaration\Config $configDeclaration,
        ConfigSchemaInterface $schema
    ): Config {
        return ConfigProcessingExceptionHandler::runInConfigContext(
            ConfigContext::config((string) $configName),
            fn(ConfigContext $currentContext) => $this->doCreate($configName, $configDeclaration, $currentContext, $schema),
        );
    }
    private function doCreate(
        ConfigName $configName,
        InputDeclaration\Config $configDeclaration,
        ConfigContext $configContext,
        ConfigSchemaInterface $schema,
    ): Config {
        $sectionBlocksBySectionName = [];
        foreach ($configDeclaration->sectionBlocks as $sectionBlockDeclaration) {
            $key = $sectionBlockDeclaration->sectionName;

            $sectionBlocksBySectionName[$key] ??= [];

            $sectionBlocksBySectionName[$key][] = ConfigProcessingExceptionHandler::runInConfigContext(
                $configContext->withSection($sectionBlockDeclaration->sectionName),
                fn(ConfigContext $currentContext) => $this->createBlock(
                    $configName,
                    $sectionBlockDeclaration,
                    $currentContext,
                    $schema,
                ),
            );
        }

        $sections = [];

        foreach ($sectionBlocksBySectionName as $sectionName => $blocks) {
            $sections[] = ConfigProcessingExceptionHandler::runInConfigContext(
                $configContext->withSection($sectionName),
                function (ConfigContext $currentContext) use ($sectionName, $blocks, $schema) {
                    $sectionSchema = $schema->getSectionSchema($sectionName);
                    return new Section(
                        new SectionName($sectionName),
                        $sectionSchema->isMultiBlock(),
                        $blocks,
                    );
                },
            );
        }

        return new Config(
            $configName,
            $sections,
        );
    }

    private function createBlock(
        ConfigName $configName,
        InputDeclaration\SectionBlock $sectionBlockDeclaration,
        ConfigContext $configContext,
        ConfigSchemaInterface $schema,
    ): Block {
        $sectionSchema = $schema->getSectionSchema($sectionBlockDeclaration->sectionName);

        return ConfigProcessingExceptionHandler::runInConfigContext(
            $configContext->withBlock($sectionBlockDeclaration->blockName),
            function (ConfigContext $currentContext) use ($configName, $sectionSchema, $sectionBlockDeclaration) {
                return new Block(
                    new BlockName($sectionBlockDeclaration->blockName),
                    $this->createBlockParameters($sectionBlockDeclaration, $sectionSchema, $currentContext),
                    $sectionBlockDeclaration->isTemplate,
                    $this->createBlockInheritance($configName, $sectionBlockDeclaration, $sectionSchema),
                    $this->createBlockPlaceholderValues($sectionBlockDeclaration),
                );
            }
        );
    }

    private function createBlockInheritance(
        ConfigName $configName,
        InputDeclaration\SectionBlock $sectionBlockDeclaration,
        SectionSchemaInterface $sectionSchema,
    ): ?BlockInheritance {
        if ($sectionBlockDeclaration->inheritance === null) {
            return null;
        }

        $inheritance = $sectionBlockDeclaration->inheritance;

        $blockReference = new BlockReference(
            new BlockName($inheritance->blockReference->blockName),
            new ConfigName($inheritance->blockReference->configName ?? $configName->value),
        );

        $normalizedAppendValuesFor = [];
        foreach ($inheritance->appendValuesFor as $rawParameterName) {
            $parameterName = new ParameterName($rawParameterName);
            $parameterSchema = $sectionSchema->getParameterSchema((string) $parameterName);

            if (!$parameterSchema->isMultiValue) {
                throw new InvalidArgumentException("Parameter '$parameterName' listed in inheritance.appendValuesFor is not multi-value.");
            }

            $normalizedAppendValuesFor[] = $parameterName;
        }

        return new BlockInheritance(
            $blockReference,
            $normalizedAppendValuesFor,
        );
    }

    /**
     * @return list<PlaceholderValue>
     */
    private function createBlockPlaceholderValues(
        InputDeclaration\SectionBlock $sectionBlockDeclaration
    ): array {
        $placeholderValues = [];

        foreach ($sectionBlockDeclaration->placeholderValues as $placeholderValueDeclaration) {
            $placeholderValues[] = new PlaceholderValue(
                $placeholderValueDeclaration->placeholder,
                $placeholderValueDeclaration->value,
            );
        }

        return $placeholderValues;
    }

    /**
     * @return list<ParameterInterface>
     */
    private function createBlockParameters(
        InputDeclaration\SectionBlock $sectionBlockDeclaration,
        SectionSchemaInterface $sectionSchema,
        ConfigContext $configContext,
    ): array {
        $groupedParameterDeclarations = $this->groupParameterDeclarations($sectionBlockDeclaration);

        $parameters = [];

        foreach ($groupedParameterDeclarations as $parameterName => $parameterDeclarations) {
            $parameters[] = ConfigProcessingExceptionHandler::runInConfigContext(
                $configContext->withParameter($parameterName),
                fn(ConfigContext $currentContext) => $this->createBlockParameter($parameterName, $parameterDeclarations, $sectionSchema),
            );
        }

        return $parameters;
    }

    /**
     * @return array<string, GroupedParameterDeclarations>
     */
    private function groupParameterDeclarations(InputDeclaration\SectionBlock $sectionBlockDeclaration): array
    {
        $groupedParameterDeclarations = [];

        foreach ($sectionBlockDeclaration->parameters as $parameterDeclaration) {
            $key = $parameterDeclaration->name;

            $groupedParameterDeclarations[$key] ??= [];
            $groupedParameterDeclarations[$key][] = $parameterDeclaration;
        }

        return $groupedParameterDeclarations;
    }

    /**
     * @param GroupedParameterDeclarations $parameterDeclarations
     */
    private function createBlockParameter(
        string $rawParameterName,
        array $parameterDeclarations,
        SectionSchemaInterface $sectionSchema,
    ): ParameterInterface {
        $parameterName = new ParameterName($rawParameterName);

        $parameterSchema = $sectionSchema->getParameterSchema((string) $parameterName);

        if ($parameterSchema->isMultiValue) {
            $aliasedValues = [];

            foreach ($parameterDeclarations as $parameterDeclaration) {
                $aliasedValues[] = new AliasedValue(
                    $parameterDeclaration->value,
                    $parameterDeclaration->alias,
                );
            }

            return new MultiValueParameter(
                $parameterName,
                $aliasedValues,
            );
        }

        if (count($parameterDeclarations) > 1) {
            throw new InvalidArgumentException('Single-value parameter cannot have more than one value.');
        }

        $parameterDeclaration = $parameterDeclarations[0];

        if ($parameterDeclaration->alias !== null) {
            throw new InvalidArgumentException('Single-value parameter cannot have aliases.');
        }

        return new SingleValueParameter(
            $parameterName,
            $parameterDeclaration->value,
        );
    }
}
