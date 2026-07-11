<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Exception;

final readonly class ConfigContext
{
    private function __construct(
        public string $configName,
        public ?string $sectionName = null,
        public ?string $blockName = null,
        public ?string $parameterName = null,
    ) {}

    public static function config(string $name): self
    {
        return new self($name);
    }

    public function withSection(string $sectionName): self
    {
        return new self($this->configName, $sectionName);
    }

    public function withBlock(string $blockName): self
    {
        return new self($this->configName, $this->sectionName, $blockName);
    }

    public function withParameter(string $parameterName): self
    {
        return new self($this->configName, $this->sectionName, $this->blockName, $parameterName);
    }

    public function isMoreSpecificThan(self $other): bool
    {
        $thisLevel = $this->deepestDefinedLevel();
        $otherLevel = $other->deepestDefinedLevel();

        if ($thisLevel <= $otherLevel) {
            return false;
        }

        $thisParts = $this->parts();
        $otherParts = $other->parts();

        for ($level = 0; $level < $otherLevel; ++$level) {
            if ($otherParts[$level] !== $thisParts[$level]) {
                return false;
            }
        }

        return true;
    }

    public function equals(self $other): bool
    {
        return $this->parts() === $other->parts();
    }

    /**
     * @return list<string|null>
     */
    private function parts(): array
    {
        return [
            $this->configName,
            $this->sectionName,
            $this->blockName,
            $this->parameterName,
        ];
    }

    private function deepestDefinedLevel(): int
    {
        $parts = $this->parts();

        for ($i = count($parts) - 1; $i >= 0; $i--) {
            if ($parts[$i] !== null) {
                return ++$i;
            }
        }

        return 0;
    }
}
