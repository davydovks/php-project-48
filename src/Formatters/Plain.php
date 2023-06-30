<?php

namespace Differ\Formatters\Plain;

use function Differ\Differ\getNodeKey;
use function Differ\Differ\hasChildren;
use function Differ\Differ\getChildren;
use function Differ\Differ\isChanged;
use function Differ\Differ\isDeleted;
use function Differ\Differ\isAdded;
use function Differ\Differ\getValueBefore;
use function Differ\Differ\getValueAfter;

function formatDiff(array $diff): string
{
    $iter = function ($coll, array $parentElem) use (&$iter) {
        return array_reduce($coll, function (array $acc, $item) use (&$iter, $parentElem) {
            $key = getNodeKey($item);
            $currentElem = [...$parentElem, $key];

            return match (true) {
                hasChildren($item) => array_merge($acc, $iter(getChildren($item), $currentElem)),
                isChanged($item) => [...$acc, getLineChanged($item, $currentElem)],
                isAdded($item) => [...$acc, getLineAdded($item, $currentElem)],
                isDeleted($item) => [...$acc, getLineRemoved($currentElem)],
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
