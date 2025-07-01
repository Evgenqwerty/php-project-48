<?php

namespace Differ\Formatters\Plain;

const ADDED = "Property '%s' was added with value: %s";
const REMOVED = "Property '%s' was removed";
const CHANGED = "Property '%s' was updated. From %s to %s";
const VALUE_IS_ARRAY = "[complex value]";

/**
 * @param array<mixed> $array
 * @return array<mixed>
 */
function array_flatten(array $array): array
{
    return array_reduce(
        $array,
        fn($acc, $item) => is_array($item)
            ? [...$acc, ...array_flatten($item)]
            : [...$acc, $item],
        []
    );
}

/**
 * @param array<int, array{
 *     typeNode: string,
 *     key: string,
 *     oldValue: mixed,
 *     newValue: mixed,
 *     children: array<int, array<mixed>>
 * }> $ast
 * @return string
 */
function plain(array $ast): string
{
    $lines = array_map(
        fn(array $item): array => getPlain($item, ''),
        $ast
    );

    $flattened = array_flatten($lines);
    $filtered = array_filter($flattened, fn($line) => $line !== '');

    return implode("\n", $filtered);
}

/**
 * @param array{
 *     typeNode: string,
 *     key: string,
 *     oldValue: mixed,
 *     newValue: mixed,
 *     children: array<int, array<mixed>>
 * } $item
 * @param string $path
 * @return array<string>
 */
function getPlain(array $item, string $path): array
{
    [
        'typeNode' => $type,
        'key' => $key,
        'oldValue' => $before,
        'newValue' => $after,
        'children' => $children
    ] = $item;

    $beforeStr = getValue($before);
    $afterStr = getValue($after);
    $name = "{$path}{$key}";
    $nameForChildren = "{$path}{$key}.";

    return match ($type) {
        'nested' => array_merge(
            [],
            ...array_map(
                fn(array $child): array => getPlain($child, $nameForChildren),
                $children
            )
        ),
        'changed' => [sprintf(CHANGED, $name, $beforeStr, $afterStr)],
        'removed' => [sprintf(REMOVED, $name)],
        'added' => [sprintf(ADDED, $name, $afterStr)],
        default => ['']
    };
}

/**
 * @param mixed $value
 * @return string
 */
function getValue(mixed $value): string
{
    return match (true) {
        is_bool($value) => $value ? 'true' : 'false',
        $value === null => 'null',
        is_array($value) || is_object($value) => VALUE_IS_ARRAY,
        is_string($value) => "'$value'",
        is_int($value) || is_float($value) => (string)$value,
        default => ''
    };
}
