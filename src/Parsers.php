<?php

namespace Differ\Parsers;

use Symfony\Component\Yaml\Yaml;

function parseFile(string $filename, string $extension)
{
    return match ($extension) {
        'json' => json_decode(strval(file_get_contents($filename)), true),
        'yml', 'yaml' => Yaml::parseFile($filename),
        default => throw new \Exception("Unknown file format: {$extension}")
    };
}
