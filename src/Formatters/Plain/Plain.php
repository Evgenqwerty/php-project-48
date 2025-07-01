<?php

namespace Differ\Formatters\Plain;

const ADDED = "Property '%s' was added with value: %s";
const REMOVED = "Property '%s' was removed";
const CHANGED = "Property '%s' was updated. From %s to %s";
const VALUE_IS_ARRAY = "[complex value]";

/**
 * @param iterable<mixed> $array
 * @return array<mixed>
 */
function array_flatten(iterable $array): array
{
    return array_reduce(
        $array,
        function ($acc, $item) {
            if (is_array($item)) {
                return array_merge($acc, array_flatten($item));
            }
            return array_merge($acc, [$item]);
        },
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
    $processed = array_map(
        fn($item) => getPlain($item, ''),
        $ast
    );

    $flattened = array_flatten($processed);
    $filtered = array_filter($flattened, fn($line) => $line !== null && $line !== '');

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
                fn($child) => getPlain($child, $nameForChildren),
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
