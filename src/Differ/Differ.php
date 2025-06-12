<?php

namespace Differ\Differ;

use function Differ\Parser\parse;
use function Differ\Formatters\render;

function genDiff(string $firstPath, string $secondPath, $format = "stylish")
{
    $firstArray = parse($firstPath);
    $secondArray = parse($secondPath);
    $diff = makeDiff($firstArray, $secondArray);
    return render($diff, $format);
}

function makeDiff($before, $after)
{
    $unionKeys = array_unique(array_merge(array_keys((array)$before), array_keys((array)$after)));
    sort($unionKeys);
    return array_map(function ($key) use ($before, $after) {
        if (!property_exists($before, $key)) {
            return buildNode("added", $key, null, $after->$key);
        }
        
        if (!property_exists($after, $key)) {
            return buildNode("removed", $key, $before->$key, null);
        }
        
        if ($before->$key === $after->$key) {
            return buildNode("unchanged", $key, $before->$key, $after->$key);
        }
        
        if (is_object($before->$key) && is_object($after->$key)) {
            return buildNode('nested', $key, null, null, makeDiff($before->$key, $after->$key));
        }
        
        return buildNode("changed", $key, $before->$key, $after->$key);
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
