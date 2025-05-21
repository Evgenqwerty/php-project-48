<?php

namespace Differ\Parser;

function parser(string $path)
{
    if (!file_exists($path)) {
        throw new \Exception("Invalid file path: {$path}");
    }
    $content = file_get_contents($path);

    if ($content === false) {
        throw new \Exception("Can't read file: {$path}");
    }
    return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
}