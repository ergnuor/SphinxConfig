<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Tests\Input;

use Ergnuor\SphinxConfig\Domain\ConfigName;
use Ergnuor\SphinxConfig\Input\ConfigImporter;
use Ergnuor\SphinxConfig\Input\Declaration\Config as ConfigDeclaration;
use Ergnuor\SphinxConfig\Input\Decoder\DecoderInterface;
use Ergnuor\SphinxConfig\Input\DomainConfigFactory;
use Ergnuor\SphinxConfig\Input\Payload\PayloadInterface;
use Ergnuor\SphinxConfig\Input\Reader\ReaderInterface;
use Ergnuor\SphinxConfig\Schema\ConfigSchemaInterface;
use Ergnuor\SphinxConfig\Schema\KnownSchemas;
use LogicException;
use Override;
use PHPUnit\Framework\TestCase;

final class ConfigImporterTest extends TestCase
{
    public function testImportsConfigThroughStages(): void
    {
        $requestedConfigName = new ConfigName('config');

        $payload = new ConfigImporterPayload();

        $importerFixture = $this->newConfigImporterFixture(
            [$payload],
            [new ConfigDeclaration([])],
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
            [new ConfigDeclaration([])],
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
                new ConfigDeclaration([]),
                new ConfigDeclaration([]),
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

    /**
     * @param list<PayloadInterface> $payloads
     * @param list<ConfigDeclaration> $configDeclarations
     */
    private function newConfigImporterFixture(array $payloads, array $configDeclarations): ConfigImporterFixture
    {
        $reader = new ConfigImporterReaderSpy($payloads);
        $schema = KnownSchemas::sphinx();

        $decoder = new ConfigImporterDecoderSpy($configDeclarations);

        $configImporter = new ConfigImporter(
            $reader,
            $decoder,
            new DomainConfigFactory(),
            $schema,
        );

        return new ConfigImporterFixture(
            $reader,
            $decoder,
            $configImporter,
            $schema,
        );
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
            array_map(
                fn(array $element): array => [$element[0], $element[1], $importerFixture->schema],
                $expectedCalls,
            ),
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
        public ConfigSchemaInterface $schema,
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

    #[Override]
    public function read(ConfigName $configName): PayloadInterface
    {
        $this->requestedConfigNames[] = $configName;

        if ($this->payloads === []) {
            throw new LogicException('No payload queued for reader spy.');
        }

        return array_shift($this->payloads);
    }
}

final class ConfigImporterDecoderSpy implements DecoderInterface
{
    /** @var list<array{ConfigName, PayloadInterface, ConfigSchemaInterface}> */
    public array $decodeCalls = [];

    /**
     * @param list<ConfigDeclaration> $configDeclarations
     */
    public function __construct(
        private array $configDeclarations,
    ) {}

    #[Override]
    public function decode(
        ConfigName $configName,
        PayloadInterface $payload,
        ConfigSchemaInterface $schema,
    ): ConfigDeclaration {
        $this->decodeCalls[] = [$configName, $payload, $schema];

        if ($this->configDeclarations === []) {
            throw new LogicException('No config declaration queued for decoder spy.');
        }

        return array_shift($this->configDeclarations);
    }
}

final class ConfigImporterPayload implements PayloadInterface {}
