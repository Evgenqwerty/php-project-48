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

    // Сортировка без мутации - создаем новый отсортированный массив
    $sortedKeys = array_reduce(
        $unionKeys,
        function ($carry, $item) {
            $insertPos = 0;
            while ($insertPos < count($carry) && strcmp($carry[$insertPos], $item) < 0) {
                $insertPos++;
            }
            array_splice($carry, $insertPos, 0, $item);
            return $carry;
        },
        []
    );

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