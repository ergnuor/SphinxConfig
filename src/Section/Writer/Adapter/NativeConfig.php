<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Section\Writer\Adapter;

use Ergnuor\SphinxConfig\Exception\{Message as ExceptionMessage, WriterException};
use Ergnuor\SphinxConfig\Section\{Context, Writer\Adapter};

class NativeConfig implements Adapter
{
    /**
     * @var string
     */
    private $buffer = '';

    /**
     * @var string
     */
    private $dstPath;

    public function __construct(string $dstPath = null)
    {
        $this->dstPath = $dstPath;
    }

    /**
     * {@inheritdoc}
     */
    public function reset(): void
    {
        $this->buffer = '';
    }

    /**
     * @param Context $context
     * @throws WriterException
     */
    public function write(Context $context): void
    {
        $handle = fopen($this->getFilePath($context), 'w');
        fwrite($handle, $this->buffer);
        fclose($handle);
    }

    /**
     * @param Context $context
     * @return string
     * @throws WriterException
     */
    private function getFilePath(Context $context): string
    {
        if (is_null($this->dstPath)) {
            return 'php://stdout';
        }

        if (!is_dir($this->dstPath)) {
            throw new WriterException(
                ExceptionMessage::forContext($context, "'{$this->dstPath}' is not a directory")
            );
        }

        if (!is_writable($this->dstPath)) {
            throw new WriterException(
                ExceptionMessage::forContext($context, "destination directory '{$this->dstPath}' is not writable")
            );
        }

        $path = realpath($this->dstPath);
        if ($path === false) {
            throw new WriterException(
                ExceptionMessage::forContext($context, "getting real path for '{$this->dstPath}' directory is failed")
            );
        }

        return $path . DIRECTORY_SEPARATOR . $context->getConfigName() . '.conf';
    }

    public function startMultiBlockSection(
        string $sectionType,
        string $blockName,
        string $extends = null
    ): void {
        $blockStartText = "{$sectionType} {$blockName}";
        if (isset($extends)) {
            $blockStartText .= ' : ' . $extends;
        }

        $this->writeBlock($blockStartText);
    }

    private function writeBlock(string $fullBlockName): void
    {
        $this->buffer .= "{$fullBlockName} {" . PHP_EOL;
    }

    public function endMultiBlockSection(): void
    {
        $this->endSection();
    }

    private function endSection(): void
    {
        $this->buffer .= "}" . PHP_EOL . PHP_EOL;
    }

    public function startSingleBlockSection(string $sectionType): void
    {
        $this->writeBlock($sectionType);
    }

    public function endSingleBlockSection(): void
    {
        $this->endSection();
    }

    public function writeParam(string $paramName, string $paramValue): void
    {
        $this->buffer .= "\t{$paramName} = $paramValue" . PHP_EOL;
    }
}