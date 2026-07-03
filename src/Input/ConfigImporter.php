<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Input;

use Ergnuor\SphinxConfig\Domain\Config;
use Ergnuor\SphinxConfig\Domain\ConfigName;
use Ergnuor\SphinxConfig\Exception\LogicException;
use Ergnuor\SphinxConfig\Input\Decoder\DecoderInterface;
use Ergnuor\SphinxConfig\Input\Reader\ReaderInterface;

final class ConfigImporter
{
    /** @var array<string, Config> */
    private array $cache = [];

    public function __construct(
        private readonly ReaderInterface $reader,
        private readonly DecoderInterface $decoder,
        private readonly Normalizer $normalizer,
    ) {}

    public function import(ConfigName $configName): Config
    {
        $cacheKey = (string) $configName;
        if (!array_key_exists($cacheKey, $this->cache)) {
            $payload = $this->reader->read($configName);
            $rawConfig = $this->decoder->decode($configName, $payload);

            if (!$configName->equals($rawConfig->name)) {
                throw new LogicException(sprintf(
                    'Decoder returned config \'%s\' while \'%s\' was requested',
                    $rawConfig->name,
                    $configName
                ));
            }

            $this->cache[$cacheKey] = $this->normalizer->normalize($rawConfig);
        }

        return $this->cache[$cacheKey];
    }
}
