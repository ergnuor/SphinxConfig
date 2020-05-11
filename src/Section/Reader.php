<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Section;

use Ergnuor\SphinxConfig\Exception\ReaderException;
use Ergnuor\SphinxConfig\Section\Reader\{Adapter as ReaderAdapter, Transformer\Factory as TransformerFactory};

class Reader
{
    /**
     * @var ReaderAdapter
     */
    private $readerAdapter;

    public function __construct(ReaderAdapter $readerAdapter)
    {
        $this->readerAdapter = $readerAdapter;
    }

    public function reset(): void
    {
        $this->readerAdapter->reset();
    }

    /**
     * @param Context $context
     * @return array
     * @throws ReaderException
     */
    final public function read(Context $context): array
    {
        $transformer = TransformerFactory::getTransformer($context);

        $config = $this->readerAdapter->read($context);
        $config = $transformer->afterRead($config);

        $config = $this->addSeparateBlocks($context, $config);
        $config = $transformer->afterAddSeparateBlocks($config);

        return $config;
    }

    /**
     * @param Context $context
     * @param array $config
     * @return array
     * @throws ReaderException
     */
    private function addSeparateBlocks(Context $context, array $config)
    {
        return array_replace(
            $config,
            $this->readerAdapter->readSeparateBlocks($context)
        );
    }
}