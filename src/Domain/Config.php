<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Domain;

use Ergnuor\SphinxConfig\Exception\InvalidArgumentException;

final readonly class Config
{
    /** @var array<string, Section> */
    public array $sections;

    /**
     * @param list<Section> $sections
     */
    public function __construct(
        public ConfigName $name,
        array $sections,
    ) {

        $sectionsByName = [];
        foreach ($sections as $section) {
            // @phpstan-ignore instanceof.alwaysTrue (Defensive runtime guard because PHP cannot enforce iterable value types)
            if (!($section instanceof Section)) {
                throw new InvalidArgumentException("Section must be an instance of '" . Section::class . "'.");
            }

            $key = (string) $section->name;

            if (array_key_exists($key, $sectionsByName)) {
                throw new InvalidArgumentException("Duplicate section '$key'.");
            }

            $sectionsByName[$key] = $section;
        }

        $this->sections = $sectionsByName;
    }
}
