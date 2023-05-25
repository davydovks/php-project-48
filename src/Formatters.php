<?php

namespace Differ\Formatters;

use function Differ\Formatters\Stylish\stylish;
use function Differ\Formatters\Plain\plain;

function genOutputFromDiff(array $diff, string $format)
{
    switch ($format) {
        case 'stylish':
            return stylish($diff);

        case 'plain':
            return plain($diff);

        default:
            throw new \Exception('Unknown format: ' . $format);
    }
}
