<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Tests\Section\Processor;

use Ergnuor\SphinxConfig\Section\Processor\Cleaner;
use Ergnuor\SphinxConfig\Tests\Section\SectionCase;

class CleanerTest extends SectionCase
{
    public function testProcess(): void
    {
        $this->assertSame(
            [
                'block1' => ['extends' => 'block4', 'config' => ['block1Param' => 'block1Value',]],
                'block4' => ['config' => ['block4Param' => 'block4Value',],],
            ],
            Cleaner::process(
                $this->normalizeConfig(
                    [
                        'block1' => ['extends' => 'block2', 'isPseudo' => false, 'block1Param' => 'block1Value',],
                        'block2' => ['extends' => 'block3', 'isPseudo' => true],
                        'block3' => ['extends' => 'block4', 'isPseudo' => true,],
                        'block4' => ['isPseudo' => false, 'block4Param' => 'block4Value',],
                    ]
                )
            )
        );
    }
}
