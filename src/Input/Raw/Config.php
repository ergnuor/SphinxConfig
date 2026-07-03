<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Input\Raw;

use Ergnuor\SphinxConfig\Domain\ConfigName;
use Ergnuor\SphinxConfig\Exception\InvalidArgumentException;

final readonly class Config
{
    /** @var array<string, Section> */
    public array $sections;

    /**
     * @param iterable<Section> $sections
     */
    public function __construct(
        public ConfigName $name,
        iterable $sections
    ) {
        /** @var array<string, Section> $sectionsByName */
        $sectionsByName = [];
        foreach ($sections as $section) {
            // @phpstan-ignore instanceof.alwaysTrue (Defensive runtime guard because PHP cannot enforce iterable value types)
            if (!($section instanceof Section)) {
                throw new InvalidArgumentException('Raw section must be instance of ' . Section::class);
            }

            if (array_key_exists($section->name, $sectionsByName)) {
                throw new InvalidArgumentException("Duplicate section name '{$section->name}'");
            }

            $sectionsByName[$section->name] = $section;
        }

        $this->sections = $sectionsByName;
    }
}
