<?php

namespace Differ\Formatters\Plain;

use function Differ\Differ\getNodeKey;
use function Differ\Differ\getNodeType;
use function Differ\Differ\getChildren;
use function Differ\Differ\getValueBefore;
use function Differ\Differ\getValueAfter;

function formatDiff(array $diff): string
{
    $iter = function ($diff, array $parentElem) use (&$iter) {
        return array_reduce($diff, function (array $acc, $item) use (&$iter, $parentElem) {
            $key = getNodeKey($item);
            $currentElem = [...$parentElem, $key];

            return match (getNodeType($item)) {
                'parent' => array_merge($acc, $iter(getChildren($item), $currentElem)),
                'changed' => [...$acc, getLineChanged($item, $currentElem)],
                'added' => [...$acc, getLineAdded($item, $currentElem)],
                'deleted' => [...$acc, getLineRemoved($currentElem)],
                default => $acc
            };
        }, []);
    };

    $lines = $iter($diff, []);

    return implode(PHP_EOL, $lines);
}

function getLineChanged(array $item, array $stack)
{
    $property = getProperty($stack);
    $old = toStringPlain(getValueBefore($item));
    $new = toStringPlain(getValueAfter($item));
    return "Property '{$property}' was updated. From {$old} to {$new}";
}

function getProperty(array $stack)
{
    return implode('.', $stack);
}

function toStringPlain(mixed $value)
{
    if (is_array($value)) {
        return '[complex value]';
    } elseif (is_string($value)) {
        return var_export($value, true);
    }

    return json_encode($value);
}

function getLineAdded(array $item, array $stack)
{
    $property = getProperty($stack);
    $value = toStringPlain(getValueAfter($item));
    return "Property '{$property}' was added with value: {$value}";
}

function getLineRemoved(array $stack)
{
    $property = getProperty($stack);
    return "Property '{$property}' was removed";
}
