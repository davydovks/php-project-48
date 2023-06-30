<?php

namespace Differ\Formatters\Stylish;

use function Differ\Differ\getNodeKey;
use function Differ\Differ\getChildren;
use function Differ\Differ\getNodeType;
use function Differ\Differ\getValueBefore;
use function Differ\Differ\getValueAfter;

function formatDiff(array $diff): string
{
    $iter = function (array $coll, int $depth) use (&$iter) {
        return array_reduce($coll, function ($acc, $item) use ($depth, &$iter) {
            $key = getNodeKey($item);
            switch (getNodeType($item)) {
                case 'parent':
                    $innerLines = $iter(getChildren($item), $depth + 1);
                    $allLines = genOutputForStructure($innerLines, $key, $depth);
                    return array_merge($acc, $allLines);
                case 'changed':
                    $newAcc = genOutputForLeaf($item, $acc, $depth, '-');
                    return genOutputForLeaf($item, $newAcc, $depth, '+');
                case 'unchanged':
                    return genOutputForLeaf($item, $acc, $depth);
                case 'added':
                    return genOutputForLeaf($item, $acc, $depth, '+');
                case 'deleted':
                    return genOutputForLeaf($item, $acc, $depth, '-');
                default:
                    throw new \LogicException('Error in element with key: ' . $key);
            }
        }, []);
    };

    $lines = $iter($diff, 1);

    return implode(PHP_EOL, ['{', ...$lines, '}']);
}

function genOutputForStructure(array $innerLines, string $key, int $depth, string $mark = ' ')
{
    $indent = getIndent($depth);

    $firstLine = markLine("{$indent}{$key}: {", $mark, $indent);
    $lastLine = "{$indent}}";

    return [$firstLine, ...$innerLines, $lastLine];
}

function getIndent(int $depth)
{
    return str_repeat('    ', $depth);
}

function markLine(string $str, string $mark, string $indent)
{
    $pos = strlen($indent) - 2;
    return substr_replace($str, $mark, $pos, strlen($mark));
}

function genOutputForLeaf(array $item, array $acc, int $depth, string $mark = ' ')
{
    $value = $mark === '+' ? getValueAfter($item) : getValueBefore($item);
    return generateLines(getNodeKey($item), $value, $acc, $depth, $mark);
}

function generateLines(string $key, mixed $value, array $acc, int $depth, string $mark = ' ')
{
    if (is_array($value)) {
        $innerLines = array_reduce(
            array_keys($value),
            fn($acc, $key) => generateLines($key, $value[$key], $acc, $depth + 1),
            []
        );
        $addedLines = genOutputForStructure($innerLines, $key, $depth, $mark);
        return [...$acc, ...$addedLines];
    } else {
        $indent = getIndent($depth);
        $strigifiedValue = toString($value);
        $newLine = markLine("{$indent}{$key}: {$strigifiedValue}", $mark, $indent);
        return [...$acc, $newLine];
    }
}

function toString(mixed $value)
{
    if (is_string($value)) {
        return trim(var_export($value, true), "'");
    }

    return json_encode($value);
}
