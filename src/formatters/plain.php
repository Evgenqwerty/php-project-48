<?php

namespace Differ\Formatters\Plain;

const ADDED = "Property '%s' was added with value: %s";
const REMOVED = "Property '%s' was removed";
const CHANGED = "Property '%s' was updated. From %s to %s";
const VALUE_IS_ARRAY = "[complex value]";

function plain($ast)
{
    $arr = array_map(function ($item) {
        return getPlain($item, '');
    }, $ast);
    $arr = array_filter(array_flatten($arr));
    return implode("\n", $arr);
}

function getPlain($item, $path)
{
    [
        'typeNode' => $type,
        'key' =>  $key,
        'oldValue' => $before,
        'newValue' => $after,
        'children' => $children
    ] = $item;

    $before = getValue($before);
    $after = getValue($after);
    $name = "{$path}{$key}";
    $nameForChildren = "{$path}{$key}.";
    switch ($type) {
        case 'nested':
            return array_map(function ($item) use ($nameForChildren) {
                return getPlain($item, $nameForChildren);
            }, $children);

        case 'changed':
            return sprintf(CHANGED, $name, $before, $after);

        case 'removed':
            return sprintf(REMOVED, $name);

        case 'added':
            return sprintf(ADDED, $name, $after);
    }
}
function getValue($value)
{
    switch (gettype($value)) {
        case 'boolean':
            return $value ? 'true' : 'false';
        case 'array':
            return VALUE_IS_ARRAY;
        case 'NULL':
            return 'null';
        case 'string':
            return "'" . $value . "'";
        default:
            return $value;
    }
}

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
