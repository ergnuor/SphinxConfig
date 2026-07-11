<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Exception;

use RuntimeException;
use Throwable;

final class ConfigProcessingException extends RuntimeException implements SphinxConfigExceptionInterface
{
    use ConfigContextAwareTrait;

    public function __construct(
        ConfigContext $configContext,
        Throwable $previous
    ) {
        parent::__construct(
            $previous->getMessage(),
            0,
            $previous
        );

        $this->addOuterConfigContext($configContext);
    }
}
