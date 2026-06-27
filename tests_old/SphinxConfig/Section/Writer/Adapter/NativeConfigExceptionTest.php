<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Tests\Section\Writer\Adapter;

use Ergnuor\SphinxConfig\Exception\{Message as ExceptionMessage, WriterException};
use Ergnuor\SphinxConfig\Tests\TestEnv\FileSystem;

class NativeConfigExceptionTest extends NativeConfigCase
{
    public function testDestinationDirectoryIsNotDirectoryException(): void
    {
        $dstPath = FileSystem::getWriterAdapterRootPath() . 'unknownDirectory';
        $this->expectException(WriterException::class);
        $this->expectExceptionMessage(
            ExceptionMessage::forContext($this->context, "'{$dstPath}' is not a directory")
        );

        $this->setUpAdapter($dstPath);

        $this->writeSections();
    }
}