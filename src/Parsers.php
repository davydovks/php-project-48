<?php

namespace Differ\Parsers;

use Symfony\Component\Yaml\Yaml;

function parse(string $string, string $extension)
{
    return match ($extension) {
        'json' => json_decode($string, true),
        'yml', 'yaml' => Yaml::parse($string),
        default => throw new \Exception("Cannot parse unknown file format: {$extension}")
    };
}
