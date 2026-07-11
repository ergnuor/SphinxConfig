<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Input\Payload\PhpArray;

use Ergnuor\SphinxConfig\Support\StringGuard;

/**
 * @phpstan-import-type PhpArraySectionConfigType from FragmentInterface
 */
final readonly class SectionFragment implements FragmentInterface
{
    public string $sectionName;

    /**
     * @param PhpArraySectionConfigType $data
     */
    public function __construct(
        string $sectionName,
        public array $data
    ) {
        $this->sectionName = StringGuard::requireTrimmedNotEmpty(
            $sectionName,
            'Section name cannot be empty.',
        );
    }
}
