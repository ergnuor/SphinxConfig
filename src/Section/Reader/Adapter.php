<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Section\Reader;

use Ergnuor\SphinxConfig\Exception\ReaderException;
use Ergnuor\SphinxConfig\Section\Context;

interface Adapter
{
    /**
     * Method called before the formation of sections
     */
    public function reset(): void;

    /**
     * @param Context $context
     * @return array
     * @throws ReaderException
     */
    public function read(Context $context): array;

    /**
     * Reads section blocks
     *
     * Allow you to store settings in separate blocks for sections like 'indexer', 'searchd' and 'common'
     * It may be useful for storing common parameters used by different configurations
     * @param Context $context
     * @return array
     * @throws ReaderException
     */
    public function readSeparateBlocks(Context $context): array;
}