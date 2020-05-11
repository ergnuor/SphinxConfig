<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Tests\Section\Utility;

use Ergnuor\SphinxConfig\Section\Utility\Parameter;
use PHPUnit\Framework\TestCase;

class ParameterTest extends TestCase
{
    /**
     * @var array
     */
    private $multiValueParamList = [
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
     * @var array
     */
    private $systemParamsList = [
        'extends',
        'extendsConfig',
        'config',
        'placeholderValues',
        'isPseudo',
    ];

    public function testParseName(): void
    {
        $expectedParamName = 'paramName';
        $expectedParamModifier = 'paramModifier';

        $this->parseNameAssertion(
            $expectedParamName,
            $expectedParamModifier,
            "$expectedParamName:$expectedParamModifier"
        );
    }

    private function parseNameAssertion(
        string $expectedParamName,
        ?string $expectedParamModifier,
        string $paramToParse
    ): void {
        ['name' => $paramName, 'modifier' => $paramModifier] = Parameter::parseName($paramToParse);

        $this->assertSame(
            $expectedParamName,
            $paramName
        );
        $this->assertSame(
            $expectedParamModifier,
            $paramModifier
        );
    }

    public function testParseNameNoModifier(): void
    {
        $expectedParamName = 'paramName';

        $this->parseNameAssertion(
            $expectedParamName,
            null,
            $expectedParamName
        );
    }

    public function testIsCustomParam(): void
    {
        $this->assertTrue(
            Parameter::isCustomParam('_customParam')
        );

        $this->assertFalse(
            Parameter::isCustomParam('notCustomParam')
        );
    }

    public function testGetSystemParamsList(): void
    {
        $this->assertSame(
            $this->systemParamsList,
            Parameter::getSystemParamsList()
        );
    }

    /**
     * @dataProvider isMultiValueParamDataProvider
     * @param string $param
     */
    public function testIsMultiValueParam(string $param): void
    {
        $this->assertTrue(
            Parameter::isMultiValueParam($param)
        );
    }

    public function isMultiValueParamDataProvider(): array
    {
        return array_combine(
            $this->multiValueParamList,
            array_map(
                function ($param) {
                    return [$param];
                },
                $this->multiValueParamList
            )
        );
    }

    public function testIsNotMultiValueParam(): void
    {
        $this->assertFalse(
            Parameter::isMultiValueParam('someNotMultiValueParam')
        );
    }
}
