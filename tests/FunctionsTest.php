<?php

declare(strict_types=1);

namespace Test\Differ;

use PHPUnit\Framework\TestCase;

use function WebsysForever\Differ\genDiff;

class FunctionsTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testGenDiff(): void
    {
        $file1 = 'tests/fixtures/file1.json';
        $file2 = 'tests/fixtures/file2.json';

        $expected = [
            "host" => "hexlet.io",
            "-follow" => false,
            "-proxy" => "123.234.53.22",
            "-timeout" => 50,
            "+timeout" => 20,
            "+verbose" => true,
        ];

        $result = genDiff($file1, $file2);
        $this->assertEquals(json_encode($expected), $result);
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