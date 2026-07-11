<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Exception;

class InvalidArgumentException extends \InvalidArgumentException implements SphinxConfigExceptionInterface
{
    use ConfigContextAwareTrait;
}
