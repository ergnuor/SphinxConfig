<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Input\Decoder;

use Ergnuor\SphinxConfig\Exception\ConfigContext;
use Ergnuor\SphinxConfig\Exception\ConfigProcessingExceptionHandler;
use Ergnuor\SphinxConfig\Exception\InvalidArgumentException;
use Ergnuor\SphinxConfig\Support\ArrayGuard;

/**
 * @phpstan-type RawSectionBlockConfigType array<array-key, mixed>
 *
 * @phpstan-type NormalizedConfigParameterType string|array<array-key, string>
 * @phpstan-type NormalizedConfigType array<string, NormalizedConfigParameterType>
 *
 * @phpstan-type NormalizedInheritanceConfigType array{
 *           extends: string,
 *           appendValuesFor: list<string>,
 *      }
 *
 * @phpstan-type NormalizedPlaceholderValuesType array<string, string>
 *
 * @phpstan-type NormalizedSectionBlockConfigType array{
 *     config: NormalizedConfigType,
 *     isTemplate: bool,
 *     inheritance: NormalizedInheritanceConfigType|null,
 *     placeholderValues: NormalizedPlaceholderValuesType,
 * }
 */
final readonly class PhpArraySectionBlockConfigNormalizer
{
    /**
     * @param RawSectionBlockConfigType $sectionBlockConfig
     * @return NormalizedSectionBlockConfigType
     */
    public function normalize(array $sectionBlockConfig, ConfigContext $configContext): array
    {
        ArrayGuard::requireOnlyAllowedKeys(
            $sectionBlockConfig,
            ['config', 'isTemplate', 'inheritance', 'placeholderValues'],
            'Section block config contains unsupported keys.',
        );

        return [
            'config' => $this->normalizeConfig($sectionBlockConfig, $configContext),
            'isTemplate' => $this->normalizeIsTemplate($sectionBlockConfig),
            'inheritance' => $this->normalizeInheritanceConfig($sectionBlockConfig),
            'placeholderValues' => $this->normalizePlaceholderValues($sectionBlockConfig),
        ];
    }

    /**
     * @param RawSectionBlockConfigType $sectionBlockConfig
     */
    private function normalizeIsTemplate(array $sectionBlockConfig): bool
    {
        $isTemplate = false;
        if (array_key_exists('isTemplate', $sectionBlockConfig)) {
            if (!is_bool($sectionBlockConfig['isTemplate'])) {
                throw new InvalidArgumentException("'isTemplate' must be a boolean.");
            }

            $isTemplate = $sectionBlockConfig['isTemplate'];
        }

        return $isTemplate;
    }

    /**
     * @param RawSectionBlockConfigType $sectionBlockConfig
     * @return NormalizedConfigType
     */
    private function normalizeConfig(array $sectionBlockConfig, ConfigContext $configContext): array
    {
        if (!array_key_exists('config', $sectionBlockConfig)) {
            return [];
        }

        $config = $sectionBlockConfig['config'];

        if (!is_array($config)) {
            throw new InvalidArgumentException('Config must be an array.');
        }

        /** @var NormalizedConfigType $normalizedConfig */
        $normalizedConfig = [];

        foreach ($config as $parameterName => $parameterValue) {
            if (!is_string($parameterName)) {
                throw new InvalidArgumentException('Config parameter name must be a string.');
            }

            $normalizedConfig[$parameterName] = ConfigProcessingExceptionHandler::runInConfigContext(
                $configContext->withParameter($parameterName),
                fn(ConfigContext $currentContext) => $this->normalizeConfigParameter($parameterValue),
            );
        }

        return $normalizedConfig;
    }

    /**
     * @return NormalizedConfigParameterType
     */
    private function normalizeConfigParameter(mixed $parameterValue): string|array
    {
        if (is_string($parameterValue)) {
            return $parameterValue;
        }

        if (is_array($parameterValue)) {
            $normalizedParameters = [];

            foreach ($parameterValue as $key => $value) {
                if (!is_string($value)) {
                    throw new InvalidArgumentException('Multi-value parameter value must be a string.');
                }

                $normalizedParameters[$key] = $value;
            }

            return $normalizedParameters;
        }

        throw new InvalidArgumentException('Config parameter value must be an array or string.');
    }

    /**
     * @param RawSectionBlockConfigType $sectionBlockConfig
     * @return ?NormalizedInheritanceConfigType
     */
    private function normalizeInheritanceConfig(array $sectionBlockConfig): ?array
    {
        if (!array_key_exists('inheritance', $sectionBlockConfig)) {
            return null;
        }

        if (!is_array($sectionBlockConfig['inheritance'])) {
            throw new InvalidArgumentException("'inheritance' must be an array.");
        }

        $rawInheritance = $sectionBlockConfig['inheritance'];

        ArrayGuard::requireOnlyAllowedKeys(
            $rawInheritance,
            ['extends', 'appendValuesFor'],
            'Inheritance configuration contains unsupported keys.',
        );

        if (!array_key_exists('extends', $rawInheritance)) {
            throw new InvalidArgumentException("Required key 'inheritance.extends' is missing.");
        }

        $extends = $rawInheritance['extends'];

        if (!is_string($extends)) {
            throw new InvalidArgumentException("'inheritance.extends' must be a string.");
        }

        $extends = trim($extends);

        if ($extends === '') {
            throw new InvalidArgumentException("'inheritance.extends' cannot be empty.");
        }

        $appendValuesFor = [];
        if (array_key_exists('appendValuesFor', $rawInheritance)) {
            $appendValuesFor = $rawInheritance['appendValuesFor'];

            if (!is_array($appendValuesFor)) {
                throw new InvalidArgumentException("'inheritance.appendValuesFor' must be an array.");
            }

            if (!array_is_list($appendValuesFor)) {
                throw new InvalidArgumentException("'inheritance.appendValuesFor' must be a list.");
            }

            foreach ($appendValuesFor as $value) {
                if (!is_string($value)) {
                    throw new InvalidArgumentException("'inheritance.appendValuesFor' value must be a string.");
                }
            }
        }

        return [
            'extends' => $extends,
            'appendValuesFor' => $appendValuesFor,
        ];
    }

    /**
     * @param RawSectionBlockConfigType $sectionBlockConfig
     * @return NormalizedPlaceholderValuesType
     */
    private function normalizePlaceholderValues(array $sectionBlockConfig): array
    {

        if (!array_key_exists('placeholderValues', $sectionBlockConfig)) {
            return [];
        }

        if (!is_array($sectionBlockConfig['placeholderValues'])) {
            throw new InvalidArgumentException('Placeholder values must be an array.');
        }

        foreach ($sectionBlockConfig['placeholderValues'] as $placeholderName => $placeholderValue) {
            if (!is_string($placeholderName)) {
                throw new InvalidArgumentException('Placeholder name must be a string.');
            }

            if (!is_string($placeholderValue)) {
                throw new InvalidArgumentException('Placeholder value must be a string.');
            }
        }

        return $sectionBlockConfig['placeholderValues'];
    }
}
