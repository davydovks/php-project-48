<?php

namespace Differ\Formatters;

use function Differ\Formatters\Stylish\stylish;
use function Differ\Formatters\Plain\plain;
use function Differ\Formatters\Json\json;

function genOutputFromDiff(array $diff, string $format)
{
    switch ($format) {
        case 'stylish':
            return stylish($diff);

        case 'plain':
            return plain($diff);

        case 'json':
            return json($diff);

        default:
            throw new \Exception('Unknown format: ' . $format);
    }
}
