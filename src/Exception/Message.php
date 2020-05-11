<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Exception;

use Ergnuor\SphinxConfig\Section\Context;

class Message
{
    public static function forContext(Context $context, string $message): string
    {
        return sprintf(
            "Error occurred in section '%s' of config '%s': %s",
            $context->getSectionType(),
            $context->getConfigName(),
            $message
        );
    }
}