<?php

namespace Ergnuor\SphinxConfig\Tests;

use Ergnuor\SphinxConfig\Section\Type as SectionType;
use Ergnuor\SphinxConfig\Section\Writer;
use Ergnuor\SphinxConfig\Section\Writer\Adapter as WriterAdapter;

class WriterCase extends SectionCase
{
    protected $writer;

    public function setUp()
    {
        $this->writerAdapter = $this->getWriterAdapterMock();
        $this->writer = new Writer($this->writerAdapter);

        $this->setCurrentConfigName('whatever');
        $this->setUpSectionParameterObject();
    }

    private function getWriterAdapterMock()
    {
        return $this->getMockForAbstractClass(WriterAdapter::class);
    }

    protected function write($config)
    {
        $this->writer->write($config, $this->sectionParameterObject);
    }

    protected function adapterExpectations(...$expectations)
    {
        foreach ($expectations as $expectationConfig) {
            $mocker = $this->writerAdapter->expects($this->exactly($expectationConfig[0]));
            $mocker->method($expectationConfig[1]);
            if (isset($expectationConfig[2])) {
                $mocker->withConsecutive(...$expectationConfig[2]);
            }
        }
    }

    private function writeSingleSection($config)
    {
        $this->setSectionName(SectionType::SEARCHD);
        $this->write($config);
    }

    private function writeMultiSection($config)
    {
        $this->setSectionName(SectionType::SOURCE);
        $this->write($config);
    }
}