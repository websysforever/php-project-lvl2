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
        $file1 = 'tests/fixtures/simple_nested_file1.json';
        $file2 = 'tests/fixtures/simple_nested_file2.json';

        $expected = <<<DOC
        {
            setting1: Value 1
          - setting1.1: true
          + setting1.1: null
            setting2: {
                key5: value5
            }
            setting3: {
                subkey1: {
                    key1: valuekey1
                }
            }
          - setting4: a
          + setting4: b
          - setting5: null
          + setting5: 
        }
        DOC;

        $result = genDiff($file1, $file2, 'stylish');

        $this->assertEquals($expected, $result);
    }

    /**
     * @throws \Exception
     */
    public function testGenDiffNestedPlain(): void
    {
        $file1 = 'tests/fixtures/simple_nested_file1.json';
        $file2 = 'tests/fixtures/simple_nested_file2.json';

        $expected = <<<DOC
        Property 'setting1-1' was updated. From true to null
        Property 'setting3-2.subkey3-2-1.key3-2-1-0' was updated. From 'valuekey1' to 'valuekey2'
        Property 'setting4' was updated. From 'a' to 'b'
        Property 'setting5' was updated. From null to ''
        DOC;

        $result = genDiff($file1, $file2, 'plain');

        $this->assertEquals($expected, $result);
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
