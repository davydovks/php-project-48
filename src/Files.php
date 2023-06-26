<?php

namespace Differ\Files;

use Symfony\Component\Yaml\Yaml;

function getExtension(string $filename): string
{
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

function readJsonFile(string $filename)
{
    return json_decode(strval(file_get_contents($filename)), true);
}

function readYamlFile(string $filename)
{
    return Yaml::parseFile($filename);
}
