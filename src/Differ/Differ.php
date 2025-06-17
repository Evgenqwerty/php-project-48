<?php

namespace Differ\Differ;

use function Differ\Parser\parse;
use function Differ\Formatters\render;

/**
 * @param string $firstPath
 * @param string $secondPath
 * @param string $format
 */
function genDiff(string $firstPath, string $secondPath, string $format = "stylish"): string
{
    $firstArray = parse($firstPath);
    $secondArray = parse($secondPath);
    $diff = makeDiff($firstArray, $secondArray);
    return render($diff, $format);
}

/**
 * @param object $before
 * @param object $after
 * @return array<int, array<string, mixed>>
 */
function makeDiff(object $before, object $after): array
{
    $unionKeys = array_unique(array_merge(
        array_keys((array)$before),
        array_keys((array)$after)
    ));

    // Создаем копию и сортируем её
    $sortedKeys = array_values($unionKeys);
    usort($sortedKeys, fn($a, $b) => strcmp($a, $b));

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
    }, $sortedKeys);
}

/**
 * @param string $typeNode
 * @param string $key
 * @param mixed $oldValue
 * @param mixed $newValue
 * @param array<int, array<string, mixed>>|null $children
 * @return array<string, mixed>
 */
function buildNode(string $typeNode, string $key, mixed $oldValue, mixed $newValue, ?array $children = null): array
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
