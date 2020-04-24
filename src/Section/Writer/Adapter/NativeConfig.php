<?php

namespace Ergnuor\SphinxConfig\Section\Writer\Adapter;

use Ergnuor\SphinxConfig\{
    Section\Writer\Adapter,
    Exception\WriterException
};

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
     * @param string $configName
     * @throws WriterException
     */
    public function write(string $configName): void
    {
        $handle = fopen($this->getFilePath($configName), 'w');
        fwrite($handle, $this->buffer);
        fclose($handle);
    }

    /**
     * @param string $configName
     * @return string
     * @throws WriterException
     */
    private function getFilePath(string $configName): string
    {
        if (is_null($this->dstPath)) {
            return 'php://stdout';
        }

        if (!is_dir($this->dstPath)) {
            throw new WriterException("'{$this->dstPath}' is not a directory");
        }

        if (!is_writable($this->dstPath)) {
            throw new WriterException("Destination directory '{$this->dstPath}' is not writable");
        }

        $path = realpath($this->dstPath);
        if ($path === false) {
            throw new WriterException("Getting real path for '{$this->dstPath}' directory is failed");
        }

        return $path . DIRECTORY_SEPARATOR . $configName . '.conf';
    }

    public function startMultiBlockSection(
        string $sectionName,
        string $blockName,
        string $extends = null
    ): void
    {
        $blockStartText = "{$sectionName} {$blockName}";
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

    public function startSingleBlockSection(string $sectionName): void
    {
        $this->writeBlock($sectionName);
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