<?php

namespace Differ\Formatters\Json;

function json($diff)
{
    return json_encode($diff, JSON_PRETTY_PRINT) . PHP_EOL;
}
