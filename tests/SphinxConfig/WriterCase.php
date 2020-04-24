<?php

namespace Ergnuor\SphinxConfig\Tests;

use PHPUnit\Framework\MockObject\MockObject;
use Ergnuor\SphinxConfig\Section\{
    Writer,
    Writer\Adapter as WriterAdapter
};

class WriterCase extends SectionCase
{
    /**
     * @var Writer
     */
    protected $writer;

    public function setUp(): void
    {
        $this->writerAdapter = $this->getWriterAdapterMock();
        $this->writer = new Writer($this->writerAdapter);

        $this->setCurrentConfigName('whatever');
        $this->setUpSectionParameterObject();
    }

    /**
     * @return MockObject|WriterAdapter
     */
    private function getWriterAdapterMock(): MockObject
    {
        return $this->getMockForAbstractClass(WriterAdapter::class);
    }

    protected function write(array $config): void
    {
        $this->writer->write($config, $this->sectionParameterObject);
    }

    protected function adapterExpectations(array ...$expectations): void
    {
        foreach ($expectations as $expectationConfig) {
            $mocker = $this->writerAdapter->expects($this->exactly($expectationConfig[0]));
            $mocker->method($expectationConfig[1]);
            if (isset($expectationConfig[2])) {
                $mocker->withConsecutive(...$expectationConfig[2]);
            }
        }
    }
}