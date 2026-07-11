<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Input\Declaration;

use Ergnuor\SphinxConfig\Support\StringGuard;

final readonly class BlockReference
{
    public string $blockName;
    public ?string $configName;

    public function __construct(
        string $blockName,
        ?string $configName = null,
    ) {
        $this->blockName = StringGuard::requireTrimmedNotEmpty(
            $blockName,
            'Block name cannot be empty.'
        );

        if ($configName !== null) {
            $configName = StringGuard::requireTrimmedNotEmpty(
                $configName,
                'Config name cannot be empty.'
            );
        }

        $this->configName = $configName;
    }
}
