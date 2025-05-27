<?php

namespace Differ\Phpunit\Differ;

use PHPUnit\Framework\TestCase;

use function Differ\Differ\genDiff;

class DifferTest extends TestCase
{
    public function testDiffJson(): void
    {
        $expected = file_get_contents(__DIR__ . "/fixtures/expected.txt");
        $before = __DIR__ . "/fixtures/file1.json";
        $after = __DIR__ . "/fixtures/file2.json";
        $result = genDiff($before, $after);
        $this->assertEquals($expected, $result);
    }

    public function testDiffYaml(): void
    {
        $expected = file_get_contents(__DIR__ . "/fixtures/expected.txt");
        $before = __DIR__ . "/fixtures/file1.yml";
        $after = __DIR__ . "/fixtures/file2.yml";
        $result = genDiff($before, $after);
        $this->assertEquals($expected, $result);
    }

    public function testRecursiveJson(): void
    {
        $expected = file_get_contents(__DIR__ . "/fixtures/expectedRecursive.txt");
        $before = __DIR__ . "/fixtures/file1recursive.json";
        $after = __DIR__ . "/fixtures/file2recursive.json";
        $result = genDiff($before, $after);
        $this->assertEquals($expected, $result);
    }
}