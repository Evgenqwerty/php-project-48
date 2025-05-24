<?php

namespace Differ\Render;

use function Differ\Render\Json\json;
use function Differ\Render\Plain\plain;
use function Differ\Render\Stylish\stylish;

function render($arr, $format)
{
    $formats = [
        'stylish' => function ($ast) {
            return stylish($ast);
        },
        'plain' => function ($ast) {
            return plain($ast);
        },
        'json' => function ($ast) {
            return json($ast);
        }
    ];
    return $formats[$format]($arr);
}
