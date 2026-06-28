<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfigOld\Section\Reader\Transformer;

use Ergnuor\SphinxConfigOld\Section\{Context, Reader\Transformer};

class MultiBlock implements Transformer
{
    /**
     * @var Context
     */
    protected $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    public function afterRead(array $config): array
    {
        return $config;
    }

    public function afterAddSeparateBlocks(array $config): array
    {
        return $config;
    }
}