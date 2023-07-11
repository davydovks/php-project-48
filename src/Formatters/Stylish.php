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
            $indent = str_repeat('    ', $depth);
            switch (getNodeType($item)) {
                case 'parent':
                    $firstLine = "{$indent}{$key}: {";
                    $innerLines = $iter(getChildren($item), $depth + 1);
                    $lastLine = "{$indent}}";
                    return [...$acc, $firstLine, ...$innerLines, $lastLine];
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
    $indent = str_repeat('    ', $depth);

    if (is_array($value)) {
        $firstLine = markLine("{$indent}{$key}: {", $mark, $indent);
        $innerLines = array_reduce(
            array_keys($value),
            fn($acc, $key) => generateLines($key, $value[$key], $acc, $depth + 1),
            []
        );
        $lastLine = "{$indent}}";
        return [...$acc, $firstLine, ...$innerLines, $lastLine];
    } else {
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

    $stringifiedNullAndBoolean = json_encode($value);
    return $stringifiedNullAndBoolean;
}
