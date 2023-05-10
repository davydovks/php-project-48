<?php

namespace Differ\Differ;

use function Differ\Parsers\readFile;

function compareArrays(array $arr1, array $arr2, string $key, int $level = 1)
{
    $indent = str_repeat("  ", $level);
    $keyExistsInFile1 = array_key_exists($key, $arr1);
    $keyExistsInFile2 = array_key_exists($key, $arr2);
    if ($keyExistsInFile1) {
        $value1 = json_encode($arr1[$key]);
    }
    if ($keyExistsInFile2) {
        $value2 = json_encode($arr2[$key]);
    }
    if ($keyExistsInFile1 && $keyExistsInFile2) {
        if ($arr1[$key] === $arr2[$key]) {
            return $indent . "  {$key}: {$value1}\n";
        } else {
            $result = $indent . "- {$key}: {$value1}\n";
            $result .= $indent . "+ {$key}: {$value2}\n";
            return $result;
        }
    } elseif ($keyExistsInFile1) {
        return $indent . "- {$key}: {$value1}\n";
    } else {
        return $indent . "+ {$key}: {$value2}\n";
    }
}

function getKeys(mixed $arr)
{
    return is_object($arr) ? get_object_vars($arr) : array_keys($arr);
}

function mergeKeys(mixed $arr1, mixed $arr2)
{
    $keys = array_unique(array_merge(getKeys($arr1), getKeys($arr2)));
    sort($keys);
    return $keys;
}

function genDiff(string $pathToFile1, string $pathToFile2)
{
    $arrayBefore = readFile($pathToFile1);
    $arrayAfter = readFile($pathToFile2);

    $keys = mergeKeys($arrayBefore, $arrayAfter);

    $result = '';
    foreach ($keys as $key) {
        $result .= compareArrays($arrayBefore, $arrayAfter, $key);
    }

    return "{\n" . $result . "}\n";
}
