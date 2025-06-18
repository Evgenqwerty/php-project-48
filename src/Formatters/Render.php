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
function render(array $arr, string $format): string
{
    $formats = [
        'stylish' => fn($ast) => stylish($ast),
        'plain' => fn($ast) => plain($ast),
        'json' => fn($ast) => json($ast)
    ];
    return $formats[$format]($arr);
}
