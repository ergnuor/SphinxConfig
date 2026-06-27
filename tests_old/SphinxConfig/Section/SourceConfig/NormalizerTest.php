<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Tests\Section\SourceConfig;

use Ergnuor\SphinxConfig\Section\SourceConfig\Normalizer;
use Ergnuor\SphinxConfig\Tests\Section\SectionCase;

class NormalizerTest extends SectionCase
{
    public function testNormalize(): void
    {
        $readContext = $this->createContext('readConfigName');

        $normalizer = new Normalizer($this->context);

        $this->assertSame(
            [
                'level2' => [
                    'extends' => 'levelN',
                    'isPseudo' => true,
                    'placeholderValues' => ['key' => 'value'],
                    'extendsConfig' => 'otherConfig',
                    'config' => [
                        'level2SphinxParam1' => 'level2SphinxValue1',
                    ],
                    'name' => 'level2',
                    'fullBlockName' => $readContext->getConfigName() . '@level2',
                    'paramModifier' => [],
                ],
                'level1' => [
                    'extends' => 'level2',
                    'extendsConfig' => $this->context->getConfigName(),
                    'config' => [
                        'level1SphinxParam1' => 'level1SphinxValue1',
                        'paramWithModifier' => [],
                        'sql_query_pre' => ['willTransformToArray'],
                    ],
                    'name' => 'level1',
                    'fullBlockName' => $readContext->getConfigName() . '@level1',
                    'isPseudo' => false,
                    'placeholderValues' => [],
                    'paramModifier' => [
                        'paramWithModifier' => 'clear',
                    ],
                ]
            ],
            $normalizer->normalize(
                $readContext,
                [
                    'level2' => [
                        'extends' => 'otherConfig@levelN',
                        'isPseudo' => true,
                        'placeholderValues' => ['key' => 'value'],

                        'level2SphinxParam1' => 'level2SphinxValue1',
                    ],
                    'level1' => [
                        'extends' => 'level2',

                        'level1SphinxParam1' => 'level1SphinxValue1',
                        'paramWithModifier:clear' => [],
                        'sql_query_pre' => 'willTransformToArray',
                        '_customParam' => 'willBeRemoved',
                    ]
                ]
            )
        );
    }
}
