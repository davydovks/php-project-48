<?php

namespace Differ\Differ;

use function Differ\Parsers\readFile;

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

function bothHaveArraysByKey(array $arr1, array $arr2, $key)
{
    $firstIsArray = isset($arr1[$key]) && is_array($arr1[$key]);
    $secondIsArray = isset($arr2[$key]) && is_array($arr2[$key]);
    return $firstIsArray && $secondIsArray;
}

function createDiff(array $arrayFirst, array $arraySecond)
{
    $keys = mergeKeys($arrayFirst, $arraySecond);
    return array_map(function ($key) use ($arrayFirst, $arraySecond) {
        $result = ['key' => $key];
        if (bothHaveArraysByKey($arrayFirst, $arraySecond, $key)) {
            $result['children'] = createDiff($arrayFirst[$key], $arraySecond[$key]);
            return $result;
        }

        if (array_key_exists($key, $arrayFirst)) {
            $result['valueBefore'] = $arrayFirst[$key];
        }
        if (array_key_exists($key, $arraySecond)) {
            $result['valueAfter'] = $arraySecond[$key];
        }

        return $result;
    }, $keys);
}

function genDiff(string $pathToFile1, string $pathToFile2)
{
    $arrayBefore = readFile($pathToFile1);
    $arrayAfter = readFile($pathToFile2);

    return createDiff($arrayBefore, $arrayAfter);
}

/**
 * Interface
 */
function getKey($item)
{
    return $item['key'];
}

function hasChildren($item)
{
    return array_key_exists('children', $item);
}

function getChildren($item)
{
    return $item['children'];
}

function isTheSame($item)
{
    return array_key_exists('valueBefore', $item)
        && array_key_exists('valueAfter', $item)
        && $item['valueBefore'] === $item['valueAfter'];
}

function isChanged($item)
{
    return array_key_exists('valueBefore', $item)
        && array_key_exists('valueAfter', $item)
        && $item['valueBefore'] !== $item['valueAfter'];
}

function isDeleted($item)
{
    return array_key_exists('valueBefore', $item)
        && !array_key_exists('valueAfter', $item);
}

function isAdded($item)
{
    return !array_key_exists('valueBefore', $item)
        && array_key_exists('valueAfter', $item);
}

function getValueBefore($item)
{
    return $item['valueBefore'];
}

function getValueAfter($item)
{
    return $item['valueAfter'];
}
