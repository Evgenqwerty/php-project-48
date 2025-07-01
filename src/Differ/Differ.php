<?php

namespace Differ\Differ;

use function Differ\Parser\parse;
use function Differ\Formatters\render;

function genDiff(string $firstPath, string $secondPath, string $format = "stylish"): string|false
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
    $beforeKeys = array_keys((array)$before);
    $afterKeys = array_keys((array)$after);

    $unionKeys = array_unique([...$beforeKeys, ...$afterKeys]);

    // Функциональная сортировка без мутаций и циклов
    $sortedKeys = array_reduce(
        $unionKeys,
        function ($acc, $key) {
            return array_merge(
                array_filter($acc, fn($k) => strcmp($k, $key) < 0),
                [$key],
                array_filter($acc, fn($k) => strcmp($k, $key) >= 0)
            );
        },
        []
    );

    return array_map(
        fn($key) => match (true) {
            !property_exists($before, $key) => buildNode("added", $key, null, $after->$key),
            !property_exists($after, $key) => buildNode("removed", $key, $before->$key, null),
            $before->$key === $after->$key => buildNode("unchanged", $key, $before->$key, $after->$key),
            is_object($before->$key) && is_object($after->$key) =>
            buildNode('nested', $key, null, null, makeDiff($before->$key, $after->$key)),
            default => buildNode("changed", $key, $before->$key, $after->$key)
        },
        $sortedKeys
    );
}

function buildNode(string $typeNode, string $key, mixed $oldValue, mixed $newValue, ?array $children = null): array
{
    return [
        'typeNode' => $typeNode,
        'key' => $key,
        'oldValue' => $oldValue,
        'newValue' => $newValue,
        'children' => $children
    ];
}
