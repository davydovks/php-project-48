<?php

namespace Differ\Formatters\Stylish;

use function Differ\Differ\getKey;
use function Differ\Differ\hasChildren;
use function Differ\Differ\getChildren;
use function Differ\Differ\isTheSame;
use function Differ\Differ\isChanged;
use function Differ\Differ\isDeleted;
use function Differ\Differ\isAdded;
use function Differ\Differ\getValueBefore;
use function Differ\Differ\getValueAfter;

function toStringStylish(mixed $value)
{
    if (is_string($value)) {
        return trim(var_export($value, true), "'");
    }

    return json_encode($value);
}

function markLine(string $str, string $mark, string $indent)
{
    $pos = strlen($indent) - 2;
    return substr_replace($str, $mark, $pos, strlen($mark));
}

function getIndent(int $depth)
{
    return str_repeat('    ', $depth);
}

function getArrayLines(array $array, int $depth)
{
    return array_reduce(
        array_keys($array),
        fn($acc, $key) => addLines($key, $array[$key], $acc, $depth, ' '),
        []
    );
}

function addLines(string $key, mixed $value, array $acc, int $depth, string $mark)
{
    $indent = getIndent($depth);

    if (is_array($value)) {
        $firstLine = markLine("{$indent}{$key}: {", $mark, $indent);
        $lines = getArrayLines($value, $depth + 1);
        $lastLine = "{$indent}}";
        return [...$acc, $firstLine, ...$lines, $lastLine];
    } else {
        $strigifiedValue = toStringStylish($value);
        $newLine = markLine("{$indent}{$key}: {$strigifiedValue}", $mark, $indent);
        return [...$acc, $newLine];
    }
}

function addItem(array $item, array $acc, int $depth, string $mark = ' ')
{
    $value = $mark === '+' ? getValueAfter($item) : getValueBefore($item);
    return addLines(getKey($item), $value, $acc, $depth, $mark);
}

function stylish(array $diff): string
{
    $iter = function (array $coll, int $depth) use (&$iter) {
        return array_reduce($coll, function ($acc, $item) use ($depth, &$iter) {
            $indent = getIndent($depth);
            $key = getKey($item);

            if (hasChildren($item)) {
                $acc[] = "{$indent}{$key}: {";
                $childrenLines = $iter(getChildren($item), $depth + 1);
                $acc = [...$acc, ...$childrenLines];
                $acc[] = "{$indent}}";
                return $acc;
            }

            if (isTheSame($item)) {
                return addItem($item, $acc, $depth);
            }

            if (isChanged($item)) {
                $newAcc = addItem($item, $acc, $depth, '-');
                return addItem($item, $newAcc, $depth, '+');
            }

            if (isAdded($item)) {
                return addItem($item, $acc, $depth, '+');
            }

            if (isDeleted($item)) {
                return addItem($item, $acc, $depth, '-');
            }

            throw new \LogicException('Error in element with key: ' . $key);
        }, []);
    };

    $lines = $iter($diff, 1);

    return implode(PHP_EOL, ['{', ...$lines, '}']);
}
