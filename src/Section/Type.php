<?php

namespace Ergnuor\SphinxConfig\Section;

class Type
{
    const SOURCE = 'source';
    const INDEX = 'index';
    const INDEXER = 'indexer';
    const SEARCHD = 'searchd';
    const COMMON = 'common';

    public static function getTypes()
    {
        return [
            self::SOURCE,
            self::INDEX,
            self::INDEXER,
            self::SEARCHD,
            self::COMMON,
        ];
    }

    public static function isMultiBlock($sectionType)
    {
        return in_array(
            $sectionType,
            [
                self::SOURCE,
                self::INDEX,
            ]
        );
    }

    public static function isSingleBlock($sectionType)
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