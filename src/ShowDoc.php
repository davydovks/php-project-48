<?php

namespace Differ\ShowDoc;

use Docopt;

function getDoc()
{
    $doc = <<<DOC
    Compares two configuration files and shows a difference.

    Usage:
      gendiff (-h|--help)
      gendiff (-v|--version)
      gendiff [--format <fmt>] <firstFile> <secondFile>
    
    Options:
      -h, --help                    Show this screen
      -v, --version                 Show version
      -f, --format <fmt>            Report format [default: stylish]
    
    DOC;

    $result = Docopt::handle($doc, array('version' => '1.0.0rc2'));

    return $result;
}
