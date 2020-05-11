<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Section\SourceConfig;

use Ergnuor\SphinxConfig\Exception\{Message as ExceptionMessage, ReaderException, SectionException};
use Ergnuor\SphinxConfig\Section\{Context, Reader};

class Assembler
{
    /**
     * @var array
     */
    private $sourceConfig;

    /**
     * @var array
     */
    private $externalConfigs;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var array
     */
    private $pulledBlocksHistory = [];

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var Normalizer
     */
    private $normalizer;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @param Context $context
     * @return array
     * @throws SectionException
     */
    public function assemble(Context $context): array
    {
        $this->context = $context;

        $this->normalizer = new Normalizer($context);

        $this->readSourceConfig();
        $this->pullExternalConfigBlocksToSourceConfig();

        return $this->sourceConfig;
    }

    /**
     * @throws ReaderException
     */
    private function readSourceConfig(): void
    {
        $this->sourceConfig = $this->readConfig($this->context->getConfigName());
    }

    /**
     * @param string $configName
     * @return array
     * @throws ReaderException
     */
    private function readConfig(string $configName): array
    {
        $context = new Context(
            $configName,
            $this->context->getSectionType()
        );

        $config = $this->reader->read($context);

        return $this->normalizer->normalize($context, $config);
    }

    /**
     * @throws SectionException
     */
    private function pullExternalConfigBlocksToSourceConfig(): void
    {
        $this->clearPulledBlocksHistory();
        $this->externalConfigs = [];

        $blocksToLookInheritanceIn = $this->sourceConfig;

        while (!empty($blocksToLookInheritanceIn)) {
            $candidateBlock = array_shift($blocksToLookInheritanceIn);

            if (!$this->isInheritExternalConfig($candidateBlock)) {
                continue;
            }

            $externalBlock = $this->getExternalBlockForBlock($candidateBlock);

            if ($this->isBlockAlreadyPulled($externalBlock)) {
                continue;
            }

            $this->addPulledBlockToHistory($externalBlock);
            $this->addBlockToSourceConfig($externalBlock);
            array_push($blocksToLookInheritanceIn, $externalBlock);
        }
    }

    private function clearPulledBlocksHistory(): void
    {
        $this->pulledBlocksHistory = [];
    }

    private function isInheritExternalConfig(array $block): bool
    {
        return (
            isset($block['extendsConfig']) &&
            $block['extendsConfig'] != $this->context->getConfigName()
        );
    }

    /**
     * @param array $block
     * @return array
     * @throws SectionException
     */
    private function getExternalBlockForBlock(array $block): array
    {
        $configName = $block['extendsConfig'];
        $blockName = $block['extends'];

        $externalConfig = $this->getExternalConfig($configName);

        if (!isset($externalConfig[$blockName])) {
            throw new SectionException(
                ExceptionMessage::forContext($this->context, "Block '{$configName}@{$blockName}' does not exists")
            );
        }

        return $externalConfig[$blockName];
    }

    /**
     * @param string $configName
     * @return array
     * @throws ReaderException
     */
    private function getExternalConfig(string $configName): array
    {
        if (!isset($this->externalConfigs[$configName])) {
            $this->externalConfigs[$configName] = $this->readConfig($configName);
        }

        return $this->externalConfigs[$configName];
    }

    /**
     * @param array $block
     * @return bool
     */
    private function isBlockAlreadyPulled(array $block): bool
    {
        return in_array($block['fullBlockName'], $this->pulledBlocksHistory);
    }

    private function addPulledBlockToHistory($block)
    {
        $this->pulledBlocksHistory[] = $block['fullBlockName'];
    }

    /**
     * @param $block
     * @throws SectionException
     */
    private function addBlockToSourceConfig(array $block): void
    {
        $this->checkForNameConflict($block);
        $this->sourceConfig[$block['name']] = $block;
    }

    /**
     * @param $block
     * @throws SectionException
     */
    private function checkForNameConflict(array $block): void
    {
        $blockName = $block['name'];

        if (isset($this->sourceConfig[$blockName])) {
            throw new SectionException(
                ExceptionMessage::forContext(
                    $this->context,
                    "There is a name conflict while pulling external block '{$block['fullBlockName']}'." .
                    " Block with that name already exists: '{$this->sourceConfig[$blockName]['fullBlockName']}'."
                )
            );
        }
    }
}