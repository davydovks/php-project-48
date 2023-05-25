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

    return json_encode($value/*, JSON_PRETTY_PRINT*/);
}

function markLine($str, $mark, $indent)
{
    $pos = strlen($indent) - 2;
    $str[$pos] = $mark;
    return $str;
}

function getIndent(int $depth)
{
    return str_repeat('    ', $depth);
}

function getArrayLines(array $array, int $depth)
{
    $result = [];
    $indent = getIndent($depth);
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $result[] = "{$indent}{$key}: {";
            $result = array_merge($result, getArrayLines($value, $depth + 1));
            $result[] = "{$indent}}";
        } else {
            $result[] = "{$indent}{$key}: {$value}";
        }
    }
    return $result;
}

function addLine($item, &$acc, $depth, $mark = ' ')
{
    $rawValue = $mark === '+' ? getValueAfter($item) : getValueBefore($item);
    $indent = getIndent($depth);
    $key = getKey($item);
    
    if (is_array($rawValue)) {
        $acc[] = markLine("{$indent}{$key}: {", $mark, $indent);
        $arrayLines = getArrayLines($rawValue, $depth + 1);
        $acc = [...$acc, ...$arrayLines];
        $acc[] = "{$indent}}";
    } else {
        $value = toString($rawValue);
        $line = markLine("{$indent}{$key}: {$value}", $mark, $indent);
        $acc[] = $line;
    }

    return $acc;
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
                return addLine($item, $acc, $depth);
            }

            if (isChanged($item)) {
                addLine($item, $acc, $depth, '-');
                addLine($item, $acc, $depth, '+');
                return $acc;
            }

            if (isAdded($item)) {
                return addLine($item, $acc, $depth, '+');
            }

            if (isDeleted($item)) {
                return addLine($item, $acc, $depth, '-');
            }

            throw new \LogicException('Error in element with key: ' . $key);
        }, []);
    };

    $lines = $iter($diff, 1);

    return implode(PHP_EOL, ['{', ...$lines, '}']);
}

function plain(array $diff)
{

}
