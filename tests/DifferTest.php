<?php

namespace Differ\Phpunit\Differ;

use PHPUnit\Framework\TestCase;

use function Differ\Differ\genDiff;

class DifferTest extends TestCase
{
    public function testDiff(): void
    {
        $expected = file_get_contents(__DIR__ . "/fixtures/expected.json");
        $before = __DIR__ . "/fixtures/before.json";
        $after = __DIR__ . "/fixtures/after.json";
        $result = genDiff($before, $after);
        $this->assertEquals($expected, $result);
    }
}