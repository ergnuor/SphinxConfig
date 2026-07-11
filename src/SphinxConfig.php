<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig;

use Ergnuor\SphinxConfig\Domain\Config;
use Ergnuor\SphinxConfig\Domain\ConfigName;
use Ergnuor\SphinxConfig\Exception\ConfigContext;
use Ergnuor\SphinxConfig\Exception\ConfigProcessingExceptionHandler;
use Ergnuor\SphinxConfig\Input\ConfigImporter;
use Ergnuor\SphinxConfig\Input\Decoder\DecoderInterface;
use Ergnuor\SphinxConfig\Input\DomainConfigFactory;
use Ergnuor\SphinxConfig\Input\Reader\ReaderInterface;
use Ergnuor\SphinxConfig\Output\ConfigExporter;
use Ergnuor\SphinxConfig\Output\NativeSphinxConfigEncoder;
use Ergnuor\SphinxConfig\Output\Writer\WriterInterface;
use Ergnuor\SphinxConfig\Processor\ConfigProcessor;
use Ergnuor\SphinxConfig\Processor\ProcessorInterface;
use Ergnuor\SphinxConfig\Resolver\ExternalBlockResolver;
use Ergnuor\SphinxConfig\Schema\ConfigSchemaInterface;
use Override;

//TODO а какие фишки версии PHP 8.4 можно применить? А какие атрибуты? А стоит ли рассматривать версию PHP 8.5?
final readonly class SphinxConfig
{
    private ExternalBlockResolver $externalResolver;
    private ConfigProcessor $processor;
    private ConfigExporter $configExporter;

    public function __construct(
        private ReaderInterface $reader,
        private DecoderInterface $decoder,
        WriterInterface $writer,
        private ConfigSchemaInterface $schema,
    ) {
        $this->externalResolver = new ExternalBlockResolver();

        $this->processor = new ConfigProcessor([
            new class implements ProcessorInterface {
                #[Override]
                public function process(Config $config): Config
                {
                    return $config;
                }
            },
        ]);

        $this->configExporter = new ConfigExporter(
            new NativeSphinxConfigEncoder(),
            $writer,
        );
    }

    public function transform(string $requestedConfigName): void
    {
        $configName = new ConfigName($requestedConfigName);

        ConfigProcessingExceptionHandler::runInConfigContext(
            ConfigContext::config((string) $configName),
            fn(ConfigContext $_) => $this->doTransform($configName),
        );
    }

    private function doTransform(ConfigName $configName): void
    {
        $configImporter = new ConfigImporter(
            $this->reader,
            $this->decoder,
            new DomainConfigFactory(),
            $this->schema,
        );

        $config = $configImporter->import($configName);
        $config = $this->externalResolver->resolve($config, $configImporter);
        $config = $this->processor->process($config);
        $this->configExporter->export($config);
    }
}
