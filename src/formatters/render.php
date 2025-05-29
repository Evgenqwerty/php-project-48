<?php

namespace Differ\Formatters;

use function Differ\Formatters\Json\json;
use function Differ\Formatters\Plain\plain;
use function Differ\Formatters\Stylish\stylish;

function render($arr, $format)
{
    $formats = [
        'stylish' => fn($ast) => stylish($ast),
        'plain' => fn($ast) => plain($ast),
        'json' => fn($ast) => json($ast)
    ];
    return $formats[$format]($arr);
}
