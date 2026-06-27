<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Tests\Section\SourceConfig;

use Ergnuor\SphinxConfig\Section\{Reader, SourceConfig\Assembler};
use Ergnuor\SphinxConfig\Tests\Section\SectionCase;
use Ergnuor\SphinxConfig\Tests\TestDouble\Stub\ReaderAdapter as ReaderAdapterStub;

class AssemblerCase extends SectionCase
{
    /**
     * @var Assembler
     */
    protected $assembler;

    /**
     * @var ReaderAdapterStub
     */
    protected $readerAdapterStub;

    /**
     * @var Reader
     */
    protected $reader;

    public function setUp(): void
    {
        parent::setUp();
        $this->readerAdapterStub = new ReaderAdapterStub();
        $this->reader = new Reader($this->readerAdapterStub);
        $this->assembler = new Assembler($this->reader);
    }
}
