<?php

namespace Differ\Formatters\Plain;

use function Differ\Differ\getNodeKey;
use function Differ\Differ\getNodeType;
use function Differ\Differ\getChildren;
use function Differ\Differ\getValueBefore;
use function Differ\Differ\getValueAfter;

function formatDiff(array $diff): string
{
    $iter = function ($diff, array $parentPropertyStack) use (&$iter) {
        return array_reduce($diff, function (array $acc, $item) use (&$iter, $parentPropertyStack) {
            $propertyStack = [...$parentPropertyStack, getNodeKey($item)];
            $addedLines = match (getNodeType($item)) {
                'parent' => $iter(getChildren($item), $propertyStack),
                'changed' => genLineForChanged($item, $propertyStack),
                'added' => genLineForAdded($item, $propertyStack),
                'deleted' => genLineForRemoved($propertyStack),
                default => []
            };

            return array_merge($acc, $addedLines);
        }, []);
    };

    $lines = $iter($diff, []);

    return implode(PHP_EOL, $lines);
}

function genLineForChanged(array $item, array $stack)
{
    $property = getProperty($stack);
    $old = toString(getValueBefore($item));
    $new = toString(getValueAfter($item));
    return ["Property '{$property}' was updated. From {$old} to {$new}"];
}

function getProperty(array $stack)
{
    return implode('.', $stack);
}

function toString(mixed $value)
{
    if (is_array($value)) {
        return '[complex value]';
    } elseif (is_string($value)) {
        return var_export($value, true);
    }

    return json_encode($value);
}

function genLineForAdded(array $item, array $stack)
{
    $property = getProperty($stack);
    $value = toString(getValueAfter($item));
    return ["Property '{$property}' was added with value: {$value}"];
}

function genLineForRemoved(array $stack)
{
    $property = getProperty($stack);
    return ["Property '{$property}' was removed"];
}
