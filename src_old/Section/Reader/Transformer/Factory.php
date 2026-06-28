<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfigOld\Section\Reader\Transformer;

use Ergnuor\SphinxConfigOld\Section\{Context, Reader\Transformer, Utility\Type};

class Factory
{
    public static function getTransformer(Context $context): Transformer
    {
        if (Type::isSingleBlock($context->getSectionType())) {
            return new SingleBlock($context);
        }

        return new MultiBlock($context);
    }
}