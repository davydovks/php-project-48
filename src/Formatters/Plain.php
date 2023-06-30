<?php

namespace Differ\Formatters\Plain;

use function Differ\Differ\getNodeKey;
use function Differ\Differ\getNodeType;
use function Differ\Differ\getChildren;
use function Differ\Differ\getValueBefore;
use function Differ\Differ\getValueAfter;

function formatDiff(array $diff): string
{
    $iter = function ($diff, array $propertyStack) use (&$iter) {
        return array_reduce($diff, function (array $acc, $item) use (&$iter, $propertyStack) {
            array_push($propertyStack, getNodeKey($item));
            return match (getNodeType($item)) {
                'parent' => array_merge($acc, $iter(getChildren($item), $propertyStack)),
                'changed' => [...$acc, genLineForChanged($item, $propertyStack)],
                'added' => [...$acc, genLineForAdded($item, $propertyStack)],
                'deleted' => [...$acc, genLineForRemoved($propertyStack)],
                default => $acc
            };
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
    return "Property '{$property}' was updated. From {$old} to {$new}";
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
    return "Property '{$property}' was added with value: {$value}";
}

function genLineForRemoved(array $stack)
{
    $property = getProperty($stack);
    return "Property '{$property}' was removed";
}
