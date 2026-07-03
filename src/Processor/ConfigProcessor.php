<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Processor;

use Ergnuor\SphinxConfig\Domain\Config;
use Ergnuor\SphinxConfig\Exception\InvalidArgumentException;

final class ConfigProcessor
{
    /**
     * @var ProcessorInterface[]
     */
    private array $processors;

    /**
     * @param array<ProcessorInterface> $processors
     */
    public function __construct(
        array $processors
    ) {
        if ($processors === []) {
            throw new InvalidArgumentException('Processors must not be empty.');
        }

        foreach ($processors as $processor) {
            // @phpstan-ignore instanceof.alwaysTrue (Defensive runtime guard because PHP cannot enforce array value types)
            if (!($processor instanceof ProcessorInterface)) {
                throw new InvalidArgumentException('Processor must implement ' . ProcessorInterface::class);
            }
        }

        $this->processors = $processors;
    }

    public function process(Config $config): Config
    {
        foreach ($this->processors as $processor) {
            $config = $processor->process($config);
        }

        return $config;
    }
}
