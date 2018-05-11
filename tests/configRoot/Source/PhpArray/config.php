<?php
return [
    'source' => [
        'configInOneFile' => [
            'sql_query_pre' => [
                'configInOneFile',
            ],
        ],

        'sameNameBlock' => [
            'sql_query_pre' => [
                'mustBeOverwriten',
            ],
        ],
    ],

    'indexer' => [
        'mem_limit' => 1024,
        'sameNameParam' => 'mustBeOverwriten',
        'uniqueParam_1' => true,
    ],
];