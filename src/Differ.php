<?php

namespace Differ\Differ;

use function Functional\sort;
use function Differ\Parsers\readFile;
use function Differ\Formatters\genOutputFromDiff;

function getKeys(mixed $arr)
{
    return is_object($arr) ? get_object_vars($arr) : array_keys($arr);
}

function mergeKeys(mixed $arr1, mixed $arr2)
{
    $keys = array_unique(array_merge(getKeys($arr1), getKeys($arr2)));
    return sort($keys, fn($left, $right) => $left <=> $right);
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
        $keyProperty = setKey($key);

        if (bothHaveArraysByKey($arrayBefore, $arrayAfter, $key)) {
            $children = setChildren(createDiff($arrayBefore[$key], $arrayAfter[$key]));
            return array_merge($keyProperty, $children);
        }

        $valueBefore = array_key_exists($key, $arrayBefore) ?
            setValueBefore($arrayBefore[$key]) : [];
        $valueAfter = array_key_exists($key, $arrayAfter) ?
            setValueAfter($arrayAfter[$key]) : [];
        return array_merge($keyProperty, $valueBefore, $valueAfter);
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

function setKey(string $key)
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
