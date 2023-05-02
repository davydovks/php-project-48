<?php

namespace Differ\ShowDoc;

require __DIR__.'/../vendor/docopt/docopt/src/docopt.php';

use Docopt;

function getDoc()
{
    $doc = <<<DOC
    Generate diff

    Usage:
      gendiff (-h|--help)
      gendiff (-v|--version)
      gendiff [--format <fmt>] <firstFile> <secondFile>
    
    Options:
      -h --help                     Show this screen
      -v --version                  Show version
      --format <fmt>                Report format [default: stylish]
    
    DOC;

    $result = Docopt::handle($doc, array('version'=>'1.0.0rc2'));

    return $result;
}