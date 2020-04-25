<?php

namespace Ergnuor\SphinxConfig\Tests\TestCase;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @param object $obj
     * @param string $property
     * @return mixed
     * @throws \ReflectionException
     */
    protected function getValueOfInaccessibleProperty(object $obj, string $property)
    {
        $reflection = new \ReflectionClass(get_class($obj));
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);
        return $property->getValue($obj);
    }
}