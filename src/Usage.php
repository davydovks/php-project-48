<?php

namespace Differ\Usage;

use Docopt;

function getDoc()
{
    $doc = <<<DOC
    Compares two configuration files and shows a difference.

    Usage:
      gendiff [options] <firstFile> <secondFile>
    
    Options:
      -h, --help                    Show this screen
      -v, --version                 Show version
      -f, --format <fmt>            Report format [default: stylish]
    
    DOC;

    $result = Docopt::handle($doc, array('version' => '1.0.0rc2'));

    return $result;
}
