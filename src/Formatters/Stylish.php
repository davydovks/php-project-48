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

function toString(mixed $value)
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
    if (is_array($value)) {
        $innerLines = getArrayLines($value, $depth + 1);
        $addedLines = addStructure($innerLines, $key, $depth, $mark);
        return [...$acc, ...$addedLines];
    } else {
        $indent = getIndent($depth);
        $strigifiedValue = toString($value);
        $newLine = markLine("{$indent}{$key}: {$strigifiedValue}", $mark, $indent);
        return [...$acc, $newLine];
    }
}

function addItem(array $item, array $acc, int $depth, string $mark = ' ')
{
    $value = $mark === '+' ? getValueAfter($item) : getValueBefore($item);
    return addLines(getKey($item), $value, $acc, $depth, $mark);
}

function addStructure(array $innerLines, string $key, int $depth, string $mark = ' ')
{
    $indent = getIndent($depth);

    $firstLine = markLine("{$indent}{$key}: {", $mark, $indent);
    $lastLine = "{$indent}}";

    return [$firstLine, ...$innerLines, $lastLine];
}

function formatDiff(array $diff): string
{
    $iter = function (array $coll, int $depth) use (&$iter) {
        return array_reduce($coll, function ($acc, $item) use ($depth, &$iter) {
            $key = getKey($item);

            if (hasChildren($item)) {
                $innerLines = $iter(getChildren($item), $depth + 1);
                $addedLines = addStructure($innerLines, $key, $depth);
                return array_merge($acc, $addedLines);
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
