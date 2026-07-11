<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Input\Payload\PhpArray;

use Ergnuor\SphinxConfig\Support\StringGuard;

/**
 * @phpstan-import-type PhpArraySectionBlockConfigType from FragmentInterface
 */
final readonly class SectionBlockFragment implements FragmentInterface
{
    public string $sectionName;
    public string $blockName;

    /**
     * @param PhpArraySectionBlockConfigType $data
     */
    public function __construct(
        string $sectionName,
        string $blockName,
        public array $data
    ) {
        $this->sectionName = StringGuard::requireTrimmedNotEmpty(
            $sectionName,
            'Section name cannot be empty.',
        );

        $this->blockName = StringGuard::requireTrimmedNotEmpty(
            $blockName,
            'Block name cannot be empty.',
        );
    }
}
