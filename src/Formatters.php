<?php

namespace Differ\Formatters;

function genOutputFromDiff(array $diff, string $format)
{
    return match ($format) {
        'stylish' => Stylish\formatDiff($diff),
        'plain' => Plain\formatDiff($diff),
        'json' => Json\formatDiff($diff),
        default => throw new \DomainException("Unknown format: {$format}")
    };
}
