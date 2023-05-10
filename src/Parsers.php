<?php

namespace Differ\Parsers;

use Symfony\Component\Yaml\Yaml;

function readFile(string $filename)
{
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if ($ext == 'json') {
        return json_decode(file_get_contents($filename), true);
    } elseif ($ext == 'yml' || $ext == 'yaml') {
        return Yaml::parseFile($filename/*, Yaml::PARSE_OBJECT_FOR_MAP*/);
    } else {
        throw new \Exception("Unknown file format: {$ext}");
    }
}
