<?php

namespace Differ\Differ;

use function Differ\Parser\parse;
use function Differ\Render\render;

function genDiff(string $firstFilePath, string $secondFilePath, $format = "stylish")
{
    $firstArray = parse($firstFilePath);
    $secondArray = parse($secondFilePath);
    $diff = makeDiff($firstArray, $secondArray);
    return render($diff, $format);
}

function makeDiff(array $before, array $after)
{
    $unionKeys = array_unique(array_merge(array_keys($before), array_keys($after)));
    sort($unionKeys);
    return array_map(function ($key) use ($before, $after) {
        if (array_key_exists($key, $before) && array_key_exists($key, $after)) {
            if (is_array($before[$key]) && is_array($after[$key])) {
                $node =  buildNode('nested', $key, null, null, makeDiff($before[$key], $after[$key]));
            } elseif ($before[$key] === $after[$key]) {
                $node = buildNode("unchanged", $key, $before[$key], $after[$key]);
            } else {
                $node = buildNode("changed", $key, $before[$key], $after[$key]);
            }
        }
        if (array_key_exists($key, $before) && !array_key_exists($key, $after)) {
            $node = buildNode("removed", $key, $before[$key], null);
        }
        if (!array_key_exists($key, $before) && array_key_exists($key, $after)) {
            $node = buildNode("added", $key, null, $after[$key]);
        }
        return $node;
    }, $unionKeys);
}

function buildNode($typeNode, $key, $oldValue, $newValue, $children = null)
{
    $node = [
        'typeNode' => $typeNode,
        'key' => $key,
        'oldValue' => $oldValue,
        'newValue' => $newValue,
        'children' => $children
    ];
    return $node;
}
