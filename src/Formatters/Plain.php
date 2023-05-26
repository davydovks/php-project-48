<?php

namespace Differ\Formatters\Plain;

use function Differ\Differ\getKey;
use function Differ\Differ\hasChildren;
use function Differ\Differ\getChildren;
use function Differ\Differ\isChanged;
use function Differ\Differ\isDeleted;
use function Differ\Differ\isAdded;
use function Differ\Differ\getValueBefore;
use function Differ\Differ\getValueAfter;

function toStringPlain(mixed $value)
{
    if (is_array($value)) {
        return '[complex value]';
    }

    if (is_string($value)) {
        return var_export($value, true);
    }

    return json_encode($value);
}

function getProperty(array $stack)
{
    return implode('.', $stack);
}

function getLineChanged(array $item, array $stack)
{
    $property = getProperty($stack);
    $old = toStringPlain(getValueBefore($item));
    $new = toStringPlain(getValueAfter($item));
    return "Property '{$property}' was updated. From {$old} to {$new}";
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

function plain(array $diff)
{
    $iter = function ($coll, array $parentElem) use (&$iter) {
        return array_reduce($coll, function (array $acc, $item) use (&$iter, $parentElem) {
            $key = getKey($item);
            $currentElem = [...$parentElem, $key];

            if (hasChildren($item)) {
                return array_merge($acc, $iter(getChildren($item), $currentElem));
            }

            if (isChanged($item)) {
                $line = getLineChanged($item, $currentElem);
                return array_merge($acc, [$line]);
            }

            if (isAdded($item)) {
                $line = getLineAdded($item, $currentElem);
                return array_merge($acc, [$line]);
            }

            if (isDeleted($item)) {
                $line = getLineRemoved($currentElem);
                return array_merge($acc, [$line]);
            }

            return $acc;
        }, []);
    };

    $lines = $iter($diff, []);

    return implode(PHP_EOL, $lines);
}
