<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Schema;

enum SectionBlockMode: string
{
    case Single = 'single';
    case Multi = 'multi';
}
