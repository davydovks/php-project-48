<?php

namespace Gendiff\ShowDoc;

require __DIR__.'/../vendor/docopt/docopt/src/docopt.php';

use Docopt;

function getDoc()
{
    $doc = <<<DOC
    Generate diff
    
    Usage:
      gendiff (-h|--help)
      gendiff (-v|--version)
    
    Options:
      -h --help                     Show this screen
      -v --version                  Show version
    
    DOC;

    $result = Docopt::handle($doc, array('version'=>'1.0.0rc2'));

    return $result;
}