<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Tests\Section;

use Ergnuor\SphinxConfig\Exception\WriterException;
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

    /**
     * @var MockObject|WriterAdapter
     */
    protected $writerAdapter;

    public function setUp(): void
    {
        parent::setUp();
        $this->writerAdapter = $this->getWriterAdapterMock();
        $this->writer = new Writer($this->writerAdapter);
    }

    /**
     * @return MockObject|WriterAdapter
     */
    private function getWriterAdapterMock(): MockObject
    {
        return $this->getMockForAbstractClass(WriterAdapter::class);
    }

    /**
     * @param array $config
     * @throws WriterException
     */
    protected function write(array $config): void
    {
        $this->writer->write($config, $this->context);
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