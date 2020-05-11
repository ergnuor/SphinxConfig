<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Section\Utility;

class Parameter
{
    /**
     * @var array
     */
    private static $multiValueParamList = [
        'sql_query_pre',
        'sql_joined_field',
        'sql_attr_uint',
        'sql_attr_bool',
        'sql_attr_bigint',
        'sql_attr_timestamp',
        'sql_attr_float',
        'sql_attr_multi',
        'sql_attr_string',
        'sql_attr_json',
        'sql_field_string',
        'xmlpipe_field',
        'xmlpipe_field_string',
        'xmlpipe_attr_uint',
        'xmlpipe_attr_bigint',
        'xmlpipe_attr_bool',
        'xmlpipe_attr_timestamp',
        'xmlpipe_attr_float',
        'xmlpipe_attr_multi',
        'xmlpipe_attr_multi_64',
        'xmlpipe_attr_string',
        'xmlpipe_attr_json',
        'unpack_zlib',
        'unpack_mysqlcompress',
        'source',
        'listen',
        'local',
        'agent',
        'agent_persistent',
        'agent_blackhole',
        'rt_field',
        'rt_attr_uint',
        'rt_attr_bool',
        'rt_attr_bigint',
        'rt_attr_float',
        'rt_attr_multi',
        'rt_attr_multi_64',
        'rt_attr_timestamp',
        'rt_attr_string',
        'rt_attr_json',
        'regexp_filter',
    ];

    /**
     * List of non sphinx parameters.
     * Used at a specific stage to separate the sphinx parameters from others
     *
     * @var array
     */
    private static $systemParamsList = [
        'extends',
        'extendsConfig',
        'config',
        'placeholderValues',
        'isPseudo',
    ];

    public static function parseName(string $paramName): array
    {
        $paramNameParts = explode(':', $paramName);
        return [
            'name' => $paramNameParts[0],
            'modifier' => $paramNameParts[1] ?? null,
        ];
    }

    public static function getSystemParamsList(): array
    {
        return self::$systemParamsList;
    }

    public static function isCustomParam(string $paramName): bool
    {
        return $paramName[0] == '_';
    }

    public static function isMultiValueParam(string $paramName): bool
    {
        return in_array(
            $paramName,
            self::$multiValueParamList
        );
    }
}