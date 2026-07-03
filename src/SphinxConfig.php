<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig;

use Ergnuor\SphinxConfig\Domain\ConfigName;
use Ergnuor\SphinxConfig\Input\ConfigImporter;
use Ergnuor\SphinxConfig\Input\Decoder\DecoderInterface;
use Ergnuor\SphinxConfig\Input\Normalizer;
use Ergnuor\SphinxConfig\Input\Reader\ReaderInterface;
use Ergnuor\SphinxConfig\Output\ConfigExporter;
use Ergnuor\SphinxConfig\Output\NativeSphinxConfigEncoder;
use Ergnuor\SphinxConfig\Output\Writer\WriterInterface;
use Ergnuor\SphinxConfig\Processor\ConfigProcessor;
use Ergnuor\SphinxConfig\Resolver\ExternalBlockResolver;

final readonly class SphinxConfig
{
    private ConfigImporter $configImporter;
    private ExternalBlockResolver $externalResolver;
    private ConfigProcessor $processor;
    private ConfigExporter $configExporter;

    public function __construct(
        ReaderInterface $reader,
        DecoderInterface $decoder,
        WriterInterface $writer,
    ) {
        $this->configImporter = new ConfigImporter(
            $reader,
            $decoder,
            new Normalizer()
        );

        $this->externalResolver = new ExternalBlockResolver(
            //            $this->configImporter,
        );

        $this->processor = new ConfigProcessor([]);

        $this->configExporter = new ConfigExporter(
            new NativeSphinxConfigEncoder(),
            $writer,
        );
    }

    public function transform(string $requestedConfigName): void
    {
        $configName = new ConfigName($requestedConfigName);

        $config = $this->configImporter->import($configName);
        $config = $this->externalResolver->resolve($config);
        $config = $this->processor->process($config);
        $this->configExporter->export($config);
    }
}
