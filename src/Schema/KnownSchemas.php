<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Schema;

final class KnownSchemas
{
    public static function sphinx(): ConfigSchemaInterface
    {
        return ConfigSchemaFactory::fromArray([
            'source' => [
                'blockMode' => SectionBlockMode::Multi->value,
                'multiValueParameters' => [
                    'sql_query_pre',
                    'unpack_mysqlcompress',
                    'unpack_zlib',

                    // Removed
                    'sql_joined_field',

                    // Deprecated
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
                ],
            ],

            'index' => [
                'blockMode' => SectionBlockMode::Multi->value,
                'multiValueParameters' => [
                    'source',
                    'local',
                    'agent',
                    'agent_persistent',
                    'agent_blackhole',
                    'mappings',
                    'morphdict',
                    'regexp_filter',

                    // Deprecated
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
                ],
            ],

            'indexer' => [
                'blockMode' => SectionBlockMode::Single->value,
                'multiValueParameters' => [],
            ],

            'searchd' => [
                'blockMode' => SectionBlockMode::Single->value,
                'multiValueParameters' => [
                    'listen',
                ],
            ],

            'common' => [
                'blockMode' => SectionBlockMode::Single->value,
                'multiValueParameters' => [],
            ],
        ]);
    }

    public static function manticore(): ConfigSchemaInterface
    {
        return ConfigSchemaFactory::fromArray([
            'source' => [
                'blockMode' => SectionBlockMode::Multi->value,
                'multiValueParameters' => [
                    'sql_query_pre',
                    'sql_query_pre_all',
                    'sql_attr_uint',
                    'sql_attr_bool',
                    'sql_attr_timestamp',
                    'sql_attr_float',
                    'sql_attr_bigint',
                    'sql_attr_multi',
                    'sql_query_post',
                    'sql_query_post_index',
                    'xmlpipe_field',
                    'xmlpipe_attr_uint',
                    'xmlpipe_attr_timestamp',
                    'xmlpipe_attr_bool',
                    'xmlpipe_attr_float',
                    'xmlpipe_attr_bigint',
                    'xmlpipe_attr_multi',
                    'xmlpipe_attr_multi_64',
                    'xmlpipe_attr_string',
                    'xmlpipe_attr_json',
                    'xmlpipe_field_string',
                    'unpack_zlib',
                    'unpack_mysqlcompress',
                    'sql_joined_field',
                    'sql_attr_string',
                    'sql_field_string',
                    'sql_file_field',
                    'sql_attr_json',
                    'tsvpipe_field',
                    'tsvpipe_attr_uint',
                    'tsvpipe_attr_timestamp',
                    'tsvpipe_attr_bool',
                    'tsvpipe_attr_float',
                    'tsvpipe_attr_bigint',
                    'tsvpipe_attr_multi',
                    'tsvpipe_attr_multi_64',
                    'tsvpipe_attr_string',
                    'tsvpipe_attr_json',
                    'tsvpipe_field_string',
                    'csvpipe_field',
                    'csvpipe_attr_uint',
                    'csvpipe_attr_timestamp',
                    'csvpipe_attr_bool',
                    'csvpipe_attr_float',
                    'csvpipe_attr_bigint',
                    'csvpipe_attr_multi',
                    'csvpipe_attr_multi_64',
                    'csvpipe_attr_string',
                    'csvpipe_attr_json',
                    'csvpipe_field_string',

                    // Removed
                    'sql_attr_str2ordinal',
                    'xmlpipe_attr_str2ordinal',
                    'xmlpipe_attr_wordcount',
                    'xmlpipe_field_wordcount',
                    'sql_str2ordinal_column',
                    'sql_attr_str2wordcount',
                    'sql_field_str2wordcount',

                ],
            ],

            'table' => [
                'blockMode' => SectionBlockMode::Multi->value,
                'multiValueParameters' => [
                    'source',
                    'wordforms',
                    'local',
                    'agent',
                    'agent_blackhole',
                    'agent_persistent',
                    'rt_field',
                    'rt_attr_uint',
                    'rt_attr_bigint',
                    'rt_attr_float',
                    'rt_attr_float_vector',
                    'rt_attr_timestamp',
                    'rt_attr_string',
                    'rt_attr_multi',
                    'rt_attr_multi_64',
                    'rt_attr_json',
                    'rt_attr_bool',
                    'regexp_filter',
                ],
            ],

            'indexer' => [
                'blockMode' => SectionBlockMode::Single->value,
                'multiValueParameters' => [],
            ],

            'searchd' => [
                'blockMode' => SectionBlockMode::Single->value,
                'multiValueParameters' => [
                    'listen',
                ],
            ],

            'common' => [
                'blockMode' => SectionBlockMode::Single->value,
                'multiValueParameters' => [],
            ],
        ]);
    }
}
