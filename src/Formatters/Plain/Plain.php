<?php

namespace Differ\Formatters\Plain;

const ADDED = "Property '%s' was added with value: %s";
const REMOVED = "Property '%s' was removed";
const CHANGED = "Property '%s' was updated. From %s to %s";
const VALUE_IS_ARRAY = "[complex value]";

function array_flatten($array)
{
    $result = array();
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $result = array_merge($result, array_flatten($value));
        } else {
            $result = array_merge($result, array($key => $value));
        }
    }
    return $result;
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
    $arr = array_map(function ($item) {
        return getPlain($item, '');
    }, $ast);
    $arr = array_flatten($arr);
    $arr = array_filter($arr, fn($line) => $line !== null && $line !== '');
    return implode("\n", $arr);
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
 * @return string|array<string>
 */
function getPlain(array $item, string $path): string|array
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

    switch ($type) {
        case 'nested':
            /** @var array<string> $nestedResult */
            $nestedResult = [];
            foreach ($children as $child) {
                $childResult = getPlain($child, $nameForChildren);
                if (is_array($childResult)) {
                    $nestedResult = [...$nestedResult, ...$childResult];
                } else {
                    $nestedResult[] = $childResult;
                }
            }
            return $nestedResult;

        case 'changed':
            return sprintf(CHANGED, $name, $beforeStr, $afterStr);

        case 'removed':
            return sprintf(REMOVED, $name);

        case 'added':
            return sprintf(ADDED, $name, $afterStr);

        default:
            return '';
    }
}

/**
 * @param mixed $value
 * @return string
 */
function getValue(mixed $value): string
{
    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    }

    if ($value === null) {
        return 'null';
    }

    if (is_array($value) || is_object($value)) {
        return VALUE_IS_ARRAY;
    }

    if (is_string($value)) {
        return "'$value'";
    }

    if (is_int($value) || is_float($value)) {
        return (string)$value;
    }

    return '';
}