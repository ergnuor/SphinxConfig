<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Tests\Exception;

use Ergnuor\SphinxConfig\Exception\InvalidArgumentException as SphinxInvalidArgumentException;
use Ergnuor\SphinxConfig\Exception\LogicException as SphinxLogicException;
use Ergnuor\SphinxConfig\Exception\SphinxConfigException;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Throwable;

final class ExceptionTest extends TestCase
{
    /**
     * @param class-string<Throwable> $expectedSplExceptionClass
     */
    #[DataProvider('exceptionProvider')]
    public function testExceptionsKeepSplSemanticsAndLibraryMarker(
        Throwable $exception,
        string $expectedSplExceptionClass,
    ): void {
        $this->assertInstanceOf($expectedSplExceptionClass, $exception);
        $this->assertInstanceOf(SphinxConfigException::class, $exception);
    }

    /**
     * @return iterable<string, array{Throwable, class-string<Throwable>}>
     */
    public static function exceptionProvider(): iterable
    {
        yield 'invalid argument' => [
            new SphinxInvalidArgumentException(),
            InvalidArgumentException::class,
        ];

        yield 'logic' => [
            new SphinxLogicException(),
            LogicException::class,
        ];
    }
}
