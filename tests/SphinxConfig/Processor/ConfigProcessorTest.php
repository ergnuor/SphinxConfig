<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Tests\Processor;

use Ergnuor\SphinxConfig\Domain\Config;
use Ergnuor\SphinxConfig\Domain\ConfigName;
use Ergnuor\SphinxConfig\Exception\InvalidArgumentException;
use Ergnuor\SphinxConfig\Processor\ConfigProcessor;
use Ergnuor\SphinxConfig\Processor\ProcessorInterface;
use Override;
use PHPUnit\Framework\TestCase;

final class ConfigProcessorTest extends TestCase
{
    public function testProcessorsMustNotBeEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('Processors list cannot be empty.');

        new ConfigProcessor([]);
    }

    public function testRejectsInvalidProcessor(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains("Processor must implement '" . ProcessorInterface::class . "'.");

        // @phpstan-ignore argument.type (Intentionally passing invalid element type to verify defensive runtime guard)
        new ConfigProcessor([
            new PassThroughProcessor(),
            'not processor',
        ]);
    }

    public function testCallsProcessorsInCorrectOrder(): void
    {
        $initialConfig = $this->newConfig('initial');
        $firstConfig = $this->newConfig('first');
        $secondConfig = $this->newConfig('second');
        $thirdConfig = $this->newConfig('third');

        $eventRecorder = new EventRecorder();

        $configProcessor = new ConfigProcessor([
            new EventLogProcessor(
                'initialProcessor',
                $firstConfig,
                $eventRecorder,
            ),
            new EventLogProcessor(
                'firstProcessor',
                $secondConfig,
                $eventRecorder,
            ),
            new EventLogProcessor(
                'secondProcessor',
                $thirdConfig,
                $eventRecorder,
            ),
        ]);

        $processedConfig = $configProcessor->process($initialConfig);

        $this->assertSame(
            [
                ['initialProcessor', $initialConfig],
                ['firstProcessor', $firstConfig],
                ['secondProcessor', $secondConfig],
            ],
            $eventRecorder->calls,
        );

        $this->assertSame($thirdConfig, $processedConfig);
    }

    private function newConfig(string $configName): Config
    {
        return new Config(
            new ConfigName($configName),
            [],
        );
    }
}

final readonly class PassThroughProcessor implements ProcessorInterface
{
    #[Override]
    public function process(Config $config): Config
    {
        return $config;
    }
}

final readonly class EventLogProcessor implements ProcessorInterface
{
    public function __construct(
        private string $name,
        private Config $result,
        private EventRecorder $eventRecorder,
    ) {}

    #[Override]
    public function process(Config $config): Config
    {
        $this->eventRecorder->record($this->name, $config);

        return $this->result;
    }
}

final class EventRecorder
{
    /** @var list<array{string, Config}> */
    public array $calls = [];

    public function record(string $name, Config $config): void
    {
        $this->calls[] = [$name, $config];
    }
}
