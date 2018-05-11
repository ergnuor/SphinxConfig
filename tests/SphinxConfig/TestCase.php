<?php

namespace Ergnuor\SphinxConfig\Tests;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function getConfigRoot()
    {
        return dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'configRoot';
    }

    /*protected function assertArraysEqual($array1, $array2) {
        $this->assertEquals(
            serialize($array1),
            serialize($array2)
        );
    }*/
}