<?php

namespace Differ\Differ;

function compareJson(array $json1, array $json2, string $key, int $level = 1)
{
    $indent = str_repeat(" ", $level * 2);
    $exists1 = array_key_exists($key, $json1);
    $exists2 = array_key_exists($key, $json2);
    if ($exists1) {
        $value1 = json_encode($json1[$key]);
    }
    if ($exists2) {
        $value2 = json_encode($json2[$key]);
    }
    if ($exists1 && $exists2) {
        if ($json1[$key] === $json2[$key]) {
            return $indent . "  {$key}: {$value1}\n";
        } else {
            $result = $indent . "- {$key}: {$value1}\n";
            $result .= $indent . "+ {$key}: {$value2}\n";
            return $result;
        }
    } elseif ($exists1) {
        return $indent . "- {$key}: {$value1}\n";
    } else {
        return $indent . "+ {$key}: {$value2}\n";
    }
}

function genDiff(string $pathToFile1, string $pathToFile2)
{
    $json1 = json_decode(file_get_contents($pathToFile1), true);
    $json2 = json_decode(file_get_contents($pathToFile2), true);

    $keys = array_keys(array_merge($json1, $json2));
    sort($keys);

    $result = '';
    foreach ($keys as $key) {
        $result .= compareJson($json1, $json2, $key);
    }

    return "{\n" . $result . "}\n";
}