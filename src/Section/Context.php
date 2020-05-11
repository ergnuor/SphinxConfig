<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Section;

class Context
{
    /**
     * @var string
     */
    private $configName;

    /**
     * @var string
     */
    private $sectionType;

    public function __construct(string $configName, string $sectionType)
    {
        $this->configName = $configName;
        $this->sectionType = $sectionType;
    }

    /**
     * @return string
     */
    public function getConfigName(): string
    {
        return $this->configName;
    }

    /**
     * @return string
     */
    public function getSectionType(): string
    {
        return $this->sectionType;
    }
}