<?php

namespace Differ\Formatters;

use function Differ\Formatters\Json\json;
use function Differ\Formatters\Plain\plain;
use function Differ\Formatters\Stylish\stylish;

/**
 * @param array<int, array<string, mixed>> $arr
 * @param string $format
 * @return string
 */
function render(array $arr, string $format): string|false
{
    $formats = [
        'stylish' => fn($ast) => stylish($ast),
        'plain' => fn($ast) => plain($ast),
        'json' => fn($ast) => json($ast)
    ];
    if (!array_key_exists($format, $formats)) {
        throw new \InvalidArgumentException("Unknown format: $format");
    }
    return $formats[$format]($arr);
}
