<?php

namespace Differ\Formatters;

function genOutput(array $diff, string $format)
{
    switch ($format) {
        case 'stylish':
            return stylish($diff);

        case 'plain':
            return plain($diff);
        
        default:
            throw new \Exception('Unknown format: ' . $format);
    }
}

function stylish(array $diff)
{

}

function plain(array $diff)
{
    
}