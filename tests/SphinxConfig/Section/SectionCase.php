<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Tests\Section;

use Ergnuor\SphinxConfig\Section\{Context, SourceConfig\Normalizer, Utility\Type as SectionType};
use PHPUnit\Framework\TestCase;

class SectionCase extends TestCase
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Context
     */
    protected $readContext;

    public function setUp(): void
    {
        $this->setContext('configName', SectionType::SOURCE);
    }

    private function setContext(string $configName, string $sectionType): void
    {
        $this->context = $this->createContext($configName, $sectionType);
    }

    protected function createContext(string $configName, string $sectionType = null): Context
    {
        return new Context(
            $configName,
            $sectionType ?? $this->context->getSectionType()
        );
    }

    protected function setConfigName(string $configName): void
    {
        $this->setContext($configName, $this->context->getSectionType());
    }

    protected function setSectionType(string $sectionType): void
    {
        $this->setContext($this->context->getConfigName(), $sectionType);
    }

    protected function normalizeConfig(array $config, Context $readContext = null): array
    {
        $normalizer = new Normalizer($this->context);
        return $normalizer->normalize($readContext ?? $this->context, $config);
    }
}