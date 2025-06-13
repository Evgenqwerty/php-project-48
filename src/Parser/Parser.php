<?php

namespace Differ\Parser;

use Symfony\Component\Yaml\Yaml;

function parse(string $path): \stdClass
{
    $content = readFileContent($path);
    $extension = pathinfo($path, PATHINFO_EXTENSION);

    return parseToObject($content, $extension);
}

function readFileContent(string $path): string
{
    if (!file_exists($path)) {
        throw new RuntimeException("Invalid file path: {$path}");
    }

    $content = file_get_contents($path);

    if ($content === false) {
        throw new RuntimeException("Can't read file: {$path}");
    }

    return $content;
}

function parseToObject(string $rawData, string $format): \stdClass
{
    return match (strtolower($format)) {
        'json' => json_decode($rawData, false, 512, JSON_THROW_ON_ERROR),
        'yml', 'yaml' => (object)Yaml::parse($rawData, Yaml::PARSE_OBJECT_FOR_MAP),
        default => throw new InvalidArgumentException("Unsupported format: $format"),
    };
}
