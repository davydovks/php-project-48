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
                    $addedLines = [$firstLine, ...$innerLines, $lastLine];
                    break;
                case 'changed':
                    $lineBefore = generateLines($key, getValueBefore($item), $depth, '-');
                    $lineAfter = generateLines($key, getValueAfter($item), $depth, '+');
                    $addedLines = [...$lineBefore, ...$lineAfter];
                    break;
                case 'unchanged':
                    $addedLines = generateLines($key, getValueBefore($item), $depth);
                    break;
                case 'added':
                    $addedLines = generateLines($key, getValueAfter($item), $depth, '+');
                    break;
                case 'deleted':
                    $addedLines = generateLines($key, getValueBefore($item), $depth, '-');
                    break;
                default:
                    throw new \LogicException('Error in element with key: ' . $key);
            }

            return array_merge($acc, $addedLines);
        }, []);
    };

    $lines = $iter($diff, 1);

    return implode(PHP_EOL, ['{', ...$lines, '}']);
}

function generateLines(string $key, mixed $value, int $depth, string $mark = ' ')
{
    $indent = str_repeat('    ', $depth);

    if (is_array($value)) {
        $firstLine = markLine("{$indent}{$key}: {", $mark, $indent);
        $innerLines = array_reduce(
            array_keys($value),
            fn($acc, $key) => [...$acc, ...generateLines($key, $value[$key], $depth + 1)],
            []
        );
        $lastLine = "{$indent}}";
        return [$firstLine, ...$innerLines, $lastLine];
    } else {
        $strigifiedValue = toString($value);
        $newLine = markLine("{$indent}{$key}: {$strigifiedValue}", $mark, $indent);
        return [$newLine];
    }
}

function markLine(string $str, string $mark, string $indent)
{
    $pos = strlen($indent) - 2;
    return substr_replace($str, $mark, $pos, strlen($mark));
}

function toString(mixed $value)
{
    if (is_string($value)) {
        return trim(var_export($value, true), "'");
    }

    $stringifiedNullAndBoolean = json_encode($value);
    return $stringifiedNullAndBoolean;
}
