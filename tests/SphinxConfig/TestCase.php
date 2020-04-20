<?php

namespace Ergnuor\SphinxConfig\Tests;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function getValueOfInaccessibleProperty($obj, $property)
    {
        $reflection = new \ReflectionClass(get_class($obj));
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);
        return $property->getValue($obj);
    }
}