<?php

namespace Ergnuor\SphinxConfig\Section;

class Type
{
    const SOURCE = 'source';
    const INDEX = 'index';
    const INDEXER = 'indexer';
    const SEARCHD = 'searchd';
    const COMMON = 'common';

    public static function getTypes(): array
    {
        return [
            self::SOURCE,
            self::INDEX,
            self::INDEXER,
            self::SEARCHD,
            self::COMMON,
        ];
    }

    public static function isMultiBlock(string $sectionType): bool
    {
        return in_array(
            $sectionType,
            [
                self::SOURCE,
                self::INDEX,
            ]
        );
    }

    public static function isSingleBlock(string $sectionType): bool
    {
        return in_array(
            $sectionType,
            [
                self::INDEXER,
                self::SEARCHD,
                self::COMMON,
            ]
        );
    }
}