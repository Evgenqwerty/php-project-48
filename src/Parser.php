<?php

namespace Differ\Parser;

use Symfony\Component\Yaml\Yaml;

function parse(string $path)
{
    if (!file_exists($path)) {
        throw new \Exception("Invalid file path: {$path}");
    }

    $content = file_get_contents($path);
    $extension = pathinfo($path, PATHINFO_EXTENSION);

    if ($content === false) {
        throw new \Exception("Can't read file: {$path}");
    }
    switch ($extension) {
        case "json":
            return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        case "yml":
            return Yaml::parse($content);
    }
}
