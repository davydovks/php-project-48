<?php

namespace Differ\Parsers;

use Symfony\Component\Yaml\Yaml;

function readFile(string $filename)
{
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if ($ext == 'json') {
        return json_decode(strval(file_get_contents($filename)), true);
    } elseif ($ext == 'yml' || $ext == 'yaml') {
        return Yaml::parseFile($filename);
    } else {
        throw new \Exception("Unknown file format: {$ext}");
    }
}
