<?php

namespace Differ\Differ;

use function Differ\Files\readFile;
use function Differ\Files\getExtension;
use function Differ\Parsers\parse;
use function Differ\Formatters\genOutputFromDiff;
use function Functional\retry;
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
        $nodeKey = setNodeKey($key);

        if (
            isset($arrayBefore[$key]) && is_array($arrayBefore[$key])
            && isset($arrayAfter[$key]) && is_array($arrayAfter[$key])
        ) {
            $children = setChildren(createDiff($arrayBefore[$key], $arrayAfter[$key]));
            $nodeType = setNodeType('parent');
            $node = array_merge($nodeKey, $children, $nodeType);
            return $node;
        }

        $valueBefore = array_key_exists($key, $arrayBefore) ?
            setValueBefore($arrayBefore[$key]) : [];
        $valueAfter = array_key_exists($key, $arrayAfter) ?
            setValueAfter($arrayAfter[$key]) : [];

        $nodeType = setNodeType(match (true) {
            $valueBefore !== [] && $valueAfter === [] => 'deleted',
            $valueBefore === [] && $valueAfter !== [] => 'added',
            $valueBefore !== [] && $valueAfter !== []
            && $arrayBefore[$key] !== $arrayAfter[$key] => 'changed',
            $valueBefore !== [] && $valueAfter !== []
            && $arrayBefore[$key] === $arrayAfter[$key] => 'unchanged',
            default => throw new \LogicException("Unable to define node type")
        });

        $node = array_merge($nodeKey, $valueBefore, $valueAfter, $nodeType);
        return $node;
    }, mergeKeys($arrayBefore, $arrayAfter));
}

function mergeKeys(mixed $arr1, mixed $arr2)
{
    $keys = array_unique(array_merge(readKeys($arr1), readKeys($arr2)));
    return sort($keys, fn($left, $right) => $left <=> $right);
}

function readKeys(mixed $coll)
{
    return is_object($coll) ? get_object_vars($coll) : array_keys($coll);
}

/**
 * Interface functions
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

function setNodeKey(string $key)
{
    return ['key' => $key];
}

function setChildren(array $children)
{
    return ['children' => $children];
}

function setValueBefore(mixed $value)
{
    return ['valueBefore' => $value];
}

function setValueAfter(mixed $value)
{
    return ['valueAfter' => $value];
}

function setNodeType(string $type): array
{
    return ['nodeType' => $type];
}
