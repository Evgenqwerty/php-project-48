<?php

namespace Differ\Formatters\Stylish;

use function Differ\Differ\makeDiff;

const TAB_SPACE = '    ';
const ADDED = '  + ';
const DELETED = '  - ';
const UNMODIFIED = '    ';

/**
 * @param array<int, array<string, mixed>> $array
 * @return string
 */
function stylish(array $array): string
{
    $initialString = '{' . "\n";
    $bodyDiff =  getBody($array);
    $endString = "\n" . '}';
    return "{$initialString}{$bodyDiff}{$endString}";
}

/**
 * @param array<int, array<string, mixed>> $array
 * @param int $depth
 * @return string
 */
function getBody(array $array, int $depth = 0): string
{
    $bodyDiff = array_reduce($array, function (array $acc, array $data) use ($depth) {
        switch ($data['typeNode']) {
            case 'changed':
                $acc[] = renderNodesRemoved($data, $depth);
                $acc[] = renderNodesAdded($data, $depth);
                break;
            case 'unchanged':
                $acc[] = renderNodesUnchanged($data, $depth);
                break;
            case 'removed':
                $acc[] = renderNodesRemoved($data, $depth);
                break;
            case 'added':
                $acc[] = renderNodesAdded($data, $depth);
                break;
            case 'nested':
                $acc[] = renderNodesNested($data, $depth);
        }
        return $acc;
    }, []);
    return implode("\n", $bodyDiff);
}

/**
 * @param array<string, mixed> $array
 * @param int $depth
 * @return string
 */
function renderArray(array $array, int $depth): string
{
    $keys = array_keys($array);
    $viewArray = array_map(function ($key) use ($array, $depth) {
        $prefix = getIndent($depth) . UNMODIFIED;
        $value = getValue($array[$key], $depth);
        $keyStr = toStringSafe($key);
        return "{$prefix}{$keyStr}: " . $value;
    }, $keys);
    $initialString = "{\n";
    $endString = "\n" . getIndent($depth) . "}";
    $body = implode("\n", $viewArray);
    return "{$initialString}{$body}{$endString}";
}

/**
 * @param int $depth
 * @return string
 */
function getIndent(int $depth): string
{
    $lengthIndent = strlen(TAB_SPACE) * $depth;
    $indent = str_pad('', $lengthIndent, TAB_SPACE);
    return $indent;
}

/**
 * @param array<string, mixed> $data
 * @param int $depth
 * @return string
 */
function renderNodesRemoved(array $data, int $depth): string
{
    $prefix = getIndent($depth) . DELETED;
    $value = getValue($data['oldValue'], $depth);
    $key = isset($data['key']) ? toStringSafe($data['key']) : '';
    $view = "{$prefix}{$key}:" . ($value === '' ? ' ' : " $value");
    return $view;
}

/**
 * @param array<string, mixed> $data
 * @param int $depth
 * @return string
 */
function renderNodesAdded(array $data, int $depth): string
{
    $prefix = getIndent($depth) . ADDED;
    $value = getValue($data['newValue'], $depth);
    $key = isset($data['key']) ? toStringSafe($data['key']) : '';
    $view = "{$prefix}{$key}:" . ($value === '' ? ' ' : " $value");
    return $view;
}

/**
 * @param array<string, mixed> $data
 * @param int $depth
 * @return string
 */
function renderNodesUnchanged(array $data, int $depth): string
{
    $prefix = getIndent($depth) . UNMODIFIED;
    $value = getValue($data['newValue'], $depth);
    $key = isset($data['key']) ? toStringSafe($data['key']) : '';
    $view = "{$prefix}{$key}:" . ($value === '' ? ' ' : " $value");
    return $view;
}

/**
 * @param array<string, mixed> $data
 * @param int $depth
 * @return string
 */
function renderNodesNested(array $data, int $depth): string
{
    $prefix = getIndent($depth) . UNMODIFIED;
    $children = isset($data['children'])
    && is_array($data['children'])
    && array_is_list($data['children']) ? $data['children'] : [];
    $body = getBody($children, $depth + 1);
    $key = isset($data['key']) ? toStringSafe($data['key']) : '';
    $lines = [
        "{$prefix}{$key}: {",
        $body,
        getIndent($depth + 1) . "}"
    ];
    return implode("\n", $lines);
}

/**
 * Безопасное приведение mixed к string для phpstan
 * @param mixed $value
 * @return string
 */
function toStringSafe(mixed $value): string
{
    if (is_string($value) ||
        is_int($value) ||
        is_float($value) ||
        is_bool($value) ||
        is_null($value)) {
        return strval($value);
    }
    return '';
}

/**
 * @param mixed $value
 * @param int $depth
 * @return string
 */
function getValue(mixed $value, int $depth): string
{
    if (is_null($value)) {
        return 'null';
    }
    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    }
    if (is_array($value)) {
        /** @var array<string, mixed> $value */
        return renderArray($value, $depth + 1);
    }
    if (is_object($value)) {
        /** @var array<string, mixed> $casted */
        $casted = (array)$value;
        return renderArray($casted, $depth + 1);
    }
    if ($value === 0) {
        return '0';
    }
    return toStringSafe($value);
}
