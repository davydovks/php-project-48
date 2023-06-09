<?php

namespace Differ\Differ;

use function Differ\Files\readFile;
use function Differ\Files\getExtension;
use function Differ\Parsers\parse;
use function Differ\Formatters\genOutputFromDiff;
use function Functional\sort;

function genDiff(string $pathToFile1, string $pathToFile2, string $format = 'stylish')
{
    $contentsOfFile1 = readFile($pathToFile1);
    $contentsOfFile2 = readFile($pathToFile2);

    $extension1 = getExtension($pathToFile1);
    $extension2 = getExtension($pathToFile2);

    $arrayBefore = parse($contentsOfFile1, $extension1);
    $arrayAfter = parse($contentsOfFile2, $extension2);

    $diff = createDiff($arrayBefore, $arrayAfter);

    return genOutputFromDiff($diff, $format);
}

function createDiff(array $arrayBefore, array $arrayAfter)
{
    return array_map(function ($key) use ($arrayBefore, $arrayAfter) {
        if (
            isset($arrayBefore[$key]) && is_array($arrayBefore[$key])
            && isset($arrayAfter[$key]) && is_array($arrayAfter[$key])
        ) {
            return [
                'key' => $key,
                'children' => createDiff($arrayBefore[$key], $arrayAfter[$key]),
                'nodeType' => 'parent'
            ];
        }

        $valueBeforeExists = array_key_exists($key, $arrayBefore);
        $valueAfterExists = array_key_exists($key, $arrayAfter);

        if ($valueBeforeExists && !$valueAfterExists) {
            return [
                'key' => $key,
                'valueBefore' => $arrayBefore[$key],
                'nodeType' => 'deleted'
            ];
        }

        if (!$valueBeforeExists && $valueAfterExists) {
            return [
                'key' => $key,
                'valueAfter' => $arrayAfter[$key],
                'nodeType' => 'added'
            ];
        }

        if ($arrayBefore[$key] !== $arrayAfter[$key]) {
            return [
                'key' => $key,
                'valueBefore' => $arrayBefore[$key],
                'valueAfter' => $arrayAfter[$key],
                'nodeType' => 'changed'
            ];
        }

        if ($arrayBefore[$key] === $arrayAfter[$key]) {
            return [
                'key' => $key,
                'valueBefore' => $arrayBefore[$key],
                'valueAfter' => $arrayAfter[$key],
                'nodeType' => 'unchanged'
            ];
        }
    }, mergeKeys($arrayBefore, $arrayAfter));
}

function mergeKeys(mixed $arr1, mixed $arr2)
{
    $keys = array_unique(array_merge(array_keys($arr1), array_keys($arr2)));
    return sort($keys, fn($left, $right) => $left <=> $right);
}

/**
 * Getters
 */

function getNodeKey(array $item)
{
    return $item['key'];
}

function getChildren(array $item)
{
    return $item['children'];
}

function getValueBefore(array $item)
{
    return $item['valueBefore'];
}

function getValueAfter(array $item)
{
    return $item['valueAfter'];
}

function getNodeType(array $item)
{
    return $item['nodeType'];
}
