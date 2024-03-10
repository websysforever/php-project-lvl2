<?php

declare(strict_types=1);

namespace Test\Differ;

use PHPUnit\Framework\TestCase;

use function Differ\Differ\genDiff;

class FunctionsTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testGenDiffNestedJsonSimple(): void
    {
        $file1 = 'tests/fixtures/simple_nested_file01.json';
        $file2 = 'tests/fixtures/simple_nested_file02.json';

        $expected = [
            'setting1' => 'Value 1',
            'setting5' => [
                'key5' => 'value5'
            ],
        ];

        $result = genDiff($file1, $file2, 'json');
        $this->assertEquals(json_encode($expected, JSON_PRETTY_PRINT), $result);
    }

    /**
     * @throws \Exception
     */
    public function testGenDiffNestedStylish(): void
    {
        $file1 = 'tests/fixtures/simple_nested_file01.json';
        $file2 = 'tests/fixtures/simple_nested_file02.json';

        $expected = [
            'setting1' => 'Value 1',
            'setting5' => [
                'key5' => 'value5'
            ],
        ];

        $result = genDiff($file1, $file2, 'stylish');
        $this->assertEquals(json_encode($expected, JSON_PRETTY_PRINT), $result);
    }

    /**
     * @throws \Exception
     */
    public function testGenDiffWrongFile(): void
    {
        $file1 = 'tests/fixtures/file1.json';
        $file2 = 'tests/fixtures/wrong-file2.json';

        $this->expectException(\Exception::class);

        genDiff($file1, $file2);
    }
}