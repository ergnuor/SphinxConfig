<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Input;

use Ergnuor\SphinxConfig\Domain\Config;
use Ergnuor\SphinxConfig\Domain\ConfigName;
use Ergnuor\SphinxConfig\Exception\ConfigContext;
use Ergnuor\SphinxConfig\Exception\ConfigProcessingExceptionHandler;
use Ergnuor\SphinxConfig\Input\Decoder\DecoderInterface;
use Ergnuor\SphinxConfig\Input\Reader\ReaderInterface;
use Ergnuor\SphinxConfig\Schema\ConfigSchemaInterface;

final class ConfigImporter
{
    /** @var array<string, Config> */
    private array $cache = [];

    public function __construct(
        private readonly ReaderInterface $reader,
        private readonly DecoderInterface $decoder,
        private readonly DomainConfigFactory $domainConfigFactory,
        private readonly ConfigSchemaInterface $schema,
    ) {}

    public function import(ConfigName $configName): Config
    {
        return ConfigProcessingExceptionHandler::runInConfigContext(
            ConfigContext::config((string) $configName),
            fn(ConfigContext $_) => $this->doImport($configName),
        );
    }

    private function doImport(ConfigName $configName): Config
    {
        $cacheKey = (string) $configName;
        if (!array_key_exists($cacheKey, $this->cache)) {
            $payload = $this->reader->read($configName);
            $configDeclaration = $this->decoder->decode($configName, $payload, $this->schema);
            $this->cache[$cacheKey] = $this->domainConfigFactory->create($configName, $configDeclaration, $this->schema);
        }

        return $this->cache[$cacheKey];
    }
}
