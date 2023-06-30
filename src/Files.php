<?php

namespace Differ\Files;

function readFile(string $filename)
{
    return strval(file_get_contents($filename));
}

function getExtension(string $filename): string
{
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}
