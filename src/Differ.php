<?php

namespace Differ\Differ;

use function Differ\Formatters\genOutputFromDiff;
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

function bothHaveArraysByKey(array $arr1, array $arr2, string $key)
{
    $firstIsArray = isset($arr1[$key]) && is_array($arr1[$key]);
    $secondIsArray = isset($arr2[$key]) && is_array($arr2[$key]);
    return $firstIsArray && $secondIsArray;
}

function createDiff(array $arrayBefore, array $arrayAfter)
{
    return array_map(function ($key) use ($arrayBefore, $arrayAfter) {
        $result = ['key' => $key];
        if (bothHaveArraysByKey($arrayBefore, $arrayAfter, $key)) {
            $children = createDiff($arrayBefore[$key], $arrayAfter[$key]);
            //$result['children'] = createDiff($arrayBefore[$key], $arrayAfter[$key]);
            return setChildren($result, $children);
        }

        if (array_key_exists($key, $arrayBefore)) {
            //$newItem = ['valueBefore' => $arrayBefore[$key]];
            //$result['valueBefore'] = $arrayBefore[$key];
            $result = setValueBefore($result, $arrayBefore[$key]);
        }
        if (array_key_exists($key, $arrayAfter)) {
            //$result['valueAfter'] = $arrayAfter[$key];
            $result = setValueAfter($result, $arrayAfter[$key]);
        }

        return $result;
    }, mergeKeys($arrayBefore, $arrayAfter));
}

function genDiff(string $pathToFile1, string $pathToFile2, string $format = 'stylish')
{
    $arrayBefore = readFile($pathToFile1);
    $arrayAfter = readFile($pathToFile2);

    $diff = createDiff($arrayBefore, $arrayAfter);

    return genOutputFromDiff($diff, $format);
}

/**
 * Interface functions
 */

function getKey(array $item)
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

function setChildren(array $item, array $children)
{
    $property = ['children' => $children];
    return array_merge($item, $property);
}

function setValueBefore(array $item, mixed $value)
{
    $property = ['valueBefore' => $value];
    return array_merge($item, $property);
}

function setValueAfter(array $item, mixed $value)
{
    $property = ['valueAfter' => $value];
    return array_merge($item, $property);
}

function hasChildren(array $item)
{
    return array_key_exists('children', $item);
}

function isTheSame(array $item)
{
    return array_key_exists('valueBefore', $item)
        && array_key_exists('valueAfter', $item)
        && $item['valueBefore'] === $item['valueAfter'];
}

function isChanged(array $item)
{
    return array_key_exists('valueBefore', $item)
        && array_key_exists('valueAfter', $item)
        && $item['valueBefore'] !== $item['valueAfter'];
}

function isDeleted(array $item)
{
    return array_key_exists('valueBefore', $item)
        && !array_key_exists('valueAfter', $item);
}

function isAdded(array $item)
{
    return !array_key_exists('valueBefore', $item)
        && array_key_exists('valueAfter', $item);
}
