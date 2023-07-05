<?php

namespace Differ\Files;

function readFile(string $filename)
{
    if (!file_exists($filename)) {
        throw new \Exception("File not found: {$filename}");
    }
    
    return (string) file_get_contents($filename);
}

function getExtension(string $filename): string
{
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}
