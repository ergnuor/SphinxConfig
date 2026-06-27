<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Tests\Section\Utility;

use Ergnuor\SphinxConfig\Section\Utility\ArrayUtility;
use PHPUnit\Framework\TestCase;

class ArrayUtilityTest extends TestCase
{
    public function testFindByPath(): void
    {
        $expectedValue = 'level2Value';
        $array = [
            'level1' => [
                'level2' => $expectedValue,
            ],
        ];

        $this->assertSame(
            $expectedValue,
            ArrayUtility::findByPath($array, 'level1.level2')
        );
    }

    public function testReturnNullIfPathNotExists(): void
    {
        $this->assertNull(
            ArrayUtility::findByPath([], 'level1.level2')
        );
    }
}
