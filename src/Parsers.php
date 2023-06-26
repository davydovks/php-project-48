<?php

namespace Differ\Parsers;

use function Differ\Files\getExtension;
use function Differ\Files\readJsonFile;
use function Differ\Files\readYamlFile;

function parseFile(string $filename)
{
    $extension = getExtension($filename);
    return match ($extension) {
        'json' => readJsonFile($filename),
        'yml', 'yaml' => readYamlFile($filename),
        default => throw new \Exception("Unknown file format: {$extension}")
    };
}
