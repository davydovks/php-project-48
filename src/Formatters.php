<?php

namespace Differ\Formatters;

use function Differ\Differ\getKey;
use function Differ\Differ\hasChildren;
use function Differ\Differ\getChildren;
use function Differ\Differ\isTheSame;
use function Differ\Differ\isChanged;
use function Differ\Differ\isDeleted;
use function Differ\Differ\isAdded;
use function Differ\Differ\getValueBefore;
use function Differ\Differ\getValueAfter;

function genOutput(array $diff, string $format)
{
    switch ($format) {
        case 'stylish':
            return stylish($diff);

        case 'plain':
            return plain($diff);

        default:
            throw new \Exception('Unknown format: ' . $format);
    }
}

function toString($value)
{
    if (is_string($value)) {
        return trim(var_export($value, true), "'");
    }

    return json_encode($value, JSON_PRETTY_PRINT);
}

function markLine($str, $mark, $indent)
{
    $pos = strlen($indent) - 2;
    $str[$pos] = $mark;
    return $str;
}

function stylish(array $diff): string
{
    $iter = function (array $coll, int $depth) use (&$iter) {
        return array_reduce($coll, function ($acc, $item) use ($depth, &$iter) {
            $indent = str_repeat('    ', $depth);
            $key = getKey($item);

            if (hasChildren($item)) {
                $acc[] = "{$indent}{$key}: {";
                $childrenLines = $iter(getChildren($item), $depth + 1);
                $acc = [...$acc, ...$childrenLines];
                $acc[] = "{$indent}}";
                return $acc;
            }

            if (isTheSame($item)) {
                $value = toString(getValueBefore($item));
                $acc[] = "{$indent}{$key}: {$value}";
                return $acc;
            }

            if (isChanged($item)) {
                $value1 = toString(getValueBefore($item));
                $line1 = markLine("{$indent}{$key}: {$value1}", '-', $indent);
                $value2 = toString(getValueAfter($item));
                $line2 = markLine("{$indent}{$key}: {$value2}", '+', $indent);
                return [...$acc, $line1, $line2];
            }

            if (isAdded($item)) {
                $value = toString(getValueAfter($item));
                $line = markLine("{$indent}{$key}: {$value}", '+', $indent);
                return [...$acc, $line];
            }

            if (isDeleted($item)) {
                $value = toString(getValueBefore($item));
                $line = markLine("{$indent}{$key}: {$value}", '-', $indent);
                return [...$acc, $line];
            }

            throw new \LogicException('Error in element with key: ' . getKey($item));
        }, []);
    };

    $lines = $iter($diff, 1);

    return implode(PHP_EOL, ['{', ...$lines, '}', '']);
}

function plain(array $diff)
{

}
