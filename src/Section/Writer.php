<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Section;

use Ergnuor\SphinxConfig\Exception\{Message as ExceptionMessage, WriterException};
use Ergnuor\SphinxConfig\Section\{Utility\Type, Writer\Adapter as WriterAdapter};

class Writer
{
    /**
     * @var WriterAdapter
     */
    private $writerAdapter;

    public function __construct(WriterAdapter $writerAdapter)
    {
        $this->writerAdapter = $writerAdapter;
    }

    public function reset(): void
    {
        $this->writerAdapter->reset();
    }

    /**
     * @param array $config
     * @param Context $context
     * @throws WriterException
     */
    final public function write(array $config, Context $context): void
    {
        $sectionType = $context->getSectionType();

        if (Type::isSingleBlock($sectionType)) {
            $this->writeSingleBlockSection($config, $context);
        } elseif (Type::isMultiBlock($sectionType)) {
            $this->writeMultiBlockSection($config, $context);
        } else {
            throw new WriterException(
                ExceptionMessage::forContext($context, 'unknown section type')
            );
        }

        $this->writerAdapter->write($context);
    }

    private function writeSingleBlockSection(array $config, Context $context): void
    {
        $sectionType = $context->getSectionType();

        if (empty($config[$sectionType]['config'])) {
            return;
        }

        $this->writerAdapter->startSingleBlockSection($sectionType);
        $this->writeParams($config[$sectionType]['config']);
        $this->writerAdapter->endSingleBlockSection();
    }

    private function writeParams(array $params): void
    {
        foreach ($params as $paramName => $paramValue) {
            $this->writeParamValues($paramName, (array)$paramValue);
        }
    }

    private function writeParamValues(string $paramName, array $paramValue): void
    {
        foreach ($paramValue as $curParamValue) {
            $this->writerAdapter->writeParam($paramName, $curParamValue);
        }
    }

    private function writeMultiBlockSection(array $config, Context $context): void
    {
        foreach ($config as $blockName => $block) {
            $extends = $block['extends'] ?? null;

            $this->writerAdapter->startMultiBlockSection($context->getSectionType(), $blockName, $extends);
            $this->writeParams($block['config']);
            $this->writerAdapter->endMultiBlockSection();
        }
    }
}