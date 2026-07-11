<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Exception;

use Throwable;

final class ConfigProcessingExceptionHandler
{
    /**
     * @template T
     * @param callable(ConfigContext): T $callback
     * @return T
     */
    public static function runInConfigContext(ConfigContext $context, callable $callback): mixed
    {
        try {
            return $callback($context);
        } catch (SphinxConfigExceptionInterface $exception) {
            $exception->addOuterConfigContext($context);

            throw $exception;
        } catch (Throwable $exception) {
            throw new ConfigProcessingException($context, $exception);
        }
    }
}
