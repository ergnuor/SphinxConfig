<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Output;

use Ergnuor\SphinxConfig\Domain\Config;
use Ergnuor\SphinxConfig\Output\Writer\WriterInterface;

final readonly class ConfigExporter
{
    public function __construct(
        private NativeSphinxConfigEncoder $encoder,
        private WriterInterface $writer
    ) {}

    public function export(Config $config): void
    {
        $contents = $this->encoder->encode($config);
        $this->writer->write($config->name, $contents);
    }
}
