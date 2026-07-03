<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Tests\Input;

use Ergnuor\SphinxConfig\Domain\ConfigName;
use Ergnuor\SphinxConfig\Exception\LogicException;
use Ergnuor\SphinxConfig\Input\ConfigImporter;
use Ergnuor\SphinxConfig\Input\Decoder\DecoderInterface;
use Ergnuor\SphinxConfig\Input\Normalizer;
use Ergnuor\SphinxConfig\Input\Payload\PayloadInterface;
use Ergnuor\SphinxConfig\Input\Raw\Config as RawConfig;
use Ergnuor\SphinxConfig\Input\Reader\ReaderInterface;
use PHPUnit\Framework\TestCase;

final class ConfigImporterTest extends TestCase
{
    public function testImportsConfigThroughStages(): void
    {
        $requestedConfigName = new ConfigName('config');

        $payload = new ConfigImporterPayload();

        $importerFixture = $this->newConfigImporterFixture(
            [$payload],
            [$requestedConfigName],
        );

        $config = $importerFixture->importer->import($requestedConfigName);

        $this->assertTrue($requestedConfigName->equals($config->name));

        $this->assertImportStagesWereCalled(
            $importerFixture,
            [
                [$requestedConfigName, $payload],
            ],
        );
    }

    public function testCachesImportedConfigByName(): void
    {
        $sameConfigName = 'config';
        $firstConfigName = new ConfigName($sameConfigName);
        $secondConfigName = new ConfigName($sameConfigName);

        $payload = new ConfigImporterPayload();

        $importerFixture = $this->newConfigImporterFixture(
            [$payload],
            [$firstConfigName],
        );

        $firstConfig = $importerFixture->importer->import($firstConfigName);
        $secondConfig = $importerFixture->importer->import($secondConfigName);

        $this->assertSame($firstConfig, $secondConfig);

        $this->assertImportStagesWereCalled(
            $importerFixture,
            [
                [$firstConfigName, $payload],
            ],
        );
    }

    public function testKeepsSeparateCacheEntriesForDifferentConfigNames(): void
    {
        $firstConfigName = new ConfigName('firstConfig');
        $secondConfigName = new ConfigName('secondConfig');

        $firstPayload = new ConfigImporterPayload();
        $secondPayload = new ConfigImporterPayload();

        $importerFixture = $this->newConfigImporterFixture(
            [$firstPayload, $secondPayload],
            [
                $firstConfigName,
                $secondConfigName,
            ],
        );

        $firstConfig = $importerFixture->importer->import($firstConfigName);
        $secondConfig = $importerFixture->importer->import($secondConfigName);

        $this->assertNotSame($firstConfig, $secondConfig);

        $this->assertTrue($firstConfigName->equals($firstConfig->name));
        $this->assertTrue($secondConfigName->equals($secondConfig->name));

        $this->assertImportStagesWereCalled(
            $importerFixture,
            [
                [$firstConfigName, $firstPayload],
                [$secondConfigName, $secondPayload],
            ],
        );
    }

    public function testRejectsDecoderResultWithDifferentConfigName(): void
    {
        $requestedConfigName = new ConfigName('requestedConfig');
        $decodedConfigName = new ConfigName('decodedConfig');

        $importerFixture = $this->newConfigImporterFixture(
            [new ConfigImporterPayload()],
            [$decodedConfigName],
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessageIsOrContains(
            "Decoder returned config 'decodedConfig' while 'requestedConfig' was requested",
        );

        $importerFixture->importer->import($requestedConfigName);
    }

    public function testDoesNotCacheRejectedDecoderResult(): void
    {
        $requestedConfigName = new ConfigName('requestedConfig');
        $decodedConfigName = new ConfigName('decodedConfig');

        $firstPayload = new ConfigImporterPayload();
        $secondPayload = new ConfigImporterPayload();

        $importerFixture = $this->newConfigImporterFixture(
            [$firstPayload, $secondPayload],
            [$decodedConfigName, $requestedConfigName],
        );

        try {
            $importerFixture->importer->import($requestedConfigName);
            $this->fail('Decoder contract violation was not rejected.');
        } catch (LogicException) {
        }

        $config = $importerFixture->importer->import($requestedConfigName);

        $this->assertTrue($requestedConfigName->equals($config->name));

        $this->assertImportStagesWereCalled(
            $importerFixture,
            [
                [$requestedConfigName, $firstPayload],
                [$requestedConfigName, $secondPayload],
            ],
        );
    }

    /**
     * @param list<PayloadInterface> $payloads
     * @param list<ConfigName> $configNames
     */
    private function newConfigImporterFixture(array $payloads, array $configNames): ConfigImporterFixture
    {
        $reader = new ConfigImporterReaderSpy($payloads);

        $decoder = new ConfigImporterDecoderSpy(
            array_map(
                fn(ConfigName $configName): RawConfig => $this->newRawConfig($configName),
                $configNames,
            ),
        );

        $configImporter = new ConfigImporter(
            $reader,
            $decoder,
            new Normalizer(),
        );

        return new ConfigImporterFixture(
            $reader,
            $decoder,
            $configImporter,
        );
    }

    private function newRawConfig(ConfigName $configName): RawConfig
    {
        return new RawConfig($configName, []);
    }

    /**
     * @param list<array{ConfigName, PayloadInterface}> $expectedCalls
     */
    private function assertImportStagesWereCalled(
        ConfigImporterFixture $importerFixture,
        array $expectedCalls
    ): void {
        $this->assertSame(
            array_map(
                fn(array $element): ConfigName => $element[0],
                $expectedCalls,
            ),
            $importerFixture->reader->requestedConfigNames,
        );
        $this->assertSame(
            $expectedCalls,
            $importerFixture->decoder->decodeCalls,
        );
    }
}

final readonly class ConfigImporterFixture
{
    public function __construct(
        public ConfigImporterReaderSpy $reader,
        public ConfigImporterDecoderSpy $decoder,
        public ConfigImporter $importer,
    ) {}
}

final class ConfigImporterReaderSpy implements ReaderInterface
{
    /** @var list<ConfigName> */
    public array $requestedConfigNames = [];

    /**
     * @param list<PayloadInterface> $payloads
     */
    public function __construct(
        private array $payloads,
    ) {}

    public function read(ConfigName $configName): PayloadInterface
    {
        $this->requestedConfigNames[] = $configName;

        if ($this->payloads === []) {
            throw new \LogicException('No payload queued for reader spy.');
        }

        return array_shift($this->payloads);
    }
}

final class ConfigImporterDecoderSpy implements DecoderInterface
{
    /** @var list<array{ConfigName, PayloadInterface}> */
    public array $decodeCalls = [];

    /**
     * @param list<RawConfig> $rawConfigs
     */
    public function __construct(
        private array $rawConfigs,
    ) {}

    public function decode(ConfigName $configName, PayloadInterface $payload): RawConfig
    {
        $this->decodeCalls[] = [$configName, $payload];

        if ($this->rawConfigs === []) {
            throw new \LogicException('No raw config queued for decoder spy.');
        }

        return array_shift($this->rawConfigs);
    }
}

final class ConfigImporterPayload implements PayloadInterface {}
