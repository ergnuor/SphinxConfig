<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Exception;

class LogicException extends \LogicException implements SphinxConfigExceptionInterface
{
    use ConfigContextAwareTrait;
}
