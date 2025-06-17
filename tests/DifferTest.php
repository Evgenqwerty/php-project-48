<?php

namespace Differ\Phpunit\Differ;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function Differ\Differ\genDiff;

class DifferTest extends TestCase
{
    #[DataProvider('diffProvider')]
    public function testDiff($expected, $result): void
    {
        $this->assertEquals($expected, $result);
    }

    public static function getFile($fileName)
    {
        return __DIR__ . "/fixtures/$fileName";
    }

    public static function diffProvider(): array
    {
        return [
            'flat_json' => [file_get_contents(self::getFile('expected.txt')), genDiff(self::getFile('file1.json'), self::getFile('file2.json'))],
            'flat_yaml' => [file_get_contents(self::getFile('expected.txt')), genDiff(self::getFile('file1.yml'), self::getFile('file2.yml'))],
            'recursive_json' => [file_get_contents(self::getFile('expectedRecursive.txt')), genDiff(self::getFile('file1recursive.json'), self::getFile('file2recursive.json'))],
            'recursive_yaml' => [file_get_contents(self::getFile('expectedRecursive.txt')), genDiff(self::getFile('file1recursive.yaml'), self::getFile('file2recursive.yaml'))],
            'plain_format' => [file_get_contents(self::getFile('expected2plain.txt')), genDiff(self::getFile('file1recursive.json'), self::getFile('file2recursive.json'), 'plain')],
            'json_format' => [file_get_contents(self::getFile('expectedJson.txt')), genDiff(self::getFile('file1recursive.json'), self::getFile('file2recursive.json'), 'json')],

        ];
    }
}
