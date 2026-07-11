<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Schema;

use Ergnuor\SphinxConfig\Exception\InvalidArgumentException;
use Ergnuor\SphinxConfig\Support\ArrayGuard;
use Ergnuor\SphinxConfig\Support\StringGuard;

/**
 * @phpstan-type RawSchemaArray array<array-key, mixed>
 *
 * @phpstan-type NormalizedSchemaArray array<string, array{
 *      blockMode: SectionBlockMode,
 *      multiValueParameters: list<string>
 * }>
 */
final class ConfigSchemaFactory
{
    /**
     * Expected input shape:
     * array<string, array{
     *      blockMode: value-of<SectionBlockMode>,
     *      multiValueParameters: array<array-key, string>
     * }>
     *
     * @param RawSchemaArray $schema
     * @return ConfigSchemaInterface
     */
    public static function fromArray(array $schema): ConfigSchemaInterface
    {
        return self::fromNormalizedSchemaArray(
            self::normalizeSchemaArray($schema)
        );
    }

    /**
     * @param RawSchemaArray $schema
     * @return NormalizedSchemaArray
     */
    private static function normalizeSchemaArray(array $schema): array
    {
        $normalizedSchema = [];

        foreach ($schema as $rawSectionName => $sectionConfig) {
            if (!is_string($rawSectionName)) {
                throw new InvalidArgumentException('Section name must be a string.');
            }

            $sectionName = StringGuard::requireTrimmedNotEmpty(
                $rawSectionName,
                'Section name cannot be empty.',
            );

            if (!is_array($sectionConfig)) {
                throw new InvalidArgumentException('Section config must be an array.');
            }

            ArrayGuard::requireOnlyAllowedKeys(
                $sectionConfig,
                ['blockMode', 'multiValueParameters'],
                "Schema for section '$sectionName' contains unsupported keys.",
            );

            if (!array_key_exists('blockMode', $sectionConfig)) {
                throw new InvalidArgumentException('Section block mode is not specified.');
            }

            $rawBlockMode = $sectionConfig['blockMode'];

            if (!is_string($rawBlockMode)) {
                throw new InvalidArgumentException('Block mode must be a string.');
            }

            $blockMode = SectionBlockMode::tryFrom($rawBlockMode);

            if ($blockMode === null) {
                throw new InvalidArgumentException("Unknown section block mode '$rawBlockMode'.");
            }

            if (!array_key_exists('multiValueParameters', $sectionConfig)) {
                throw new InvalidArgumentException('Section multi-value parameter list is not specified.');
            }

            $rawMultiValueParameters = $sectionConfig['multiValueParameters'];

            if (!is_array($rawMultiValueParameters)) {
                throw new InvalidArgumentException('Multi-value parameters list must be an array.');
            }

            $multiValueParameters = [];
            foreach ($rawMultiValueParameters as $multiValueParameterName) {
                if (!is_string($multiValueParameterName)) {
                    throw new InvalidArgumentException('Multi-value parameter must be a string.');
                }

                $multiValueParameters[] = StringGuard::requireTrimmedNotEmpty(
                    $multiValueParameterName,
                    'Multi-value parameter cannot be empty.',
                );
            }

            if (array_key_exists($sectionName, $normalizedSchema)) {
                throw new InvalidArgumentException("Duplicate section schema '$sectionName'.");
            }

            $normalizedSchema[$sectionName] = [
                'blockMode' => $blockMode,
                'multiValueParameters' => $multiValueParameters,
            ];
        }

        return $normalizedSchema;
    }

    /**
     * @param NormalizedSchemaArray $schema
     * @return ConfigSchemaInterface
     */
    private static function fromNormalizedSchemaArray(array $schema): ConfigSchemaInterface
    {
        $sections = [];
        foreach ($schema as $sectionName => $sectionConfig) {
            $multiValueParameterSchemas = [];
            foreach ($sectionConfig['multiValueParameters'] as $multiValueParameterName) {
                $multiValueParameterSchemas[] = new ParameterSchema(
                    $multiValueParameterName,
                    true,
                );
            }

            $sections[] = new SectionSchema(
                $sectionName,
                $sectionConfig['blockMode'],
                $multiValueParameterSchemas,
            );
        }

        return new ConfigSchema($sections);
    }
}
