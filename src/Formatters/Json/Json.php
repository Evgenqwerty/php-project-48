<?php

namespace Differ\Formatters\Json;

/**
 * @param array<int, mixed> $ast
 */
function json(array $ast): string|false
{
    return json_encode($ast, JSON_PRETTY_PRINT);
}
