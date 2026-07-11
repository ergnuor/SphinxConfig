<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Exception;

use Exception;
use Override;

/**
 * @phpstan-require-extends Exception
 * @phpstan-require-implements SphinxConfigExceptionInterface
 */
trait ConfigContextAwareTrait
{
    private ?string $baseMessage = null;

    /** @var list<ConfigContext> */
    public private(set) array $configContexts = [];

    #[Override]
    public function addOuterConfigContext(ConfigContext $configContext): void
    {
        $this->baseMessage ??= $this->getMessage();

        $this->configContexts = ConfigContextNormalizer::normalize([
            $configContext,
            ...$this->configContexts,
        ]);

        $this->message = ConfigContextFormatter::format(
            $this->configContexts
        ) . ': ' . $this->baseMessage;
    }
}
