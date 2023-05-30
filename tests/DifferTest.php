<?php

namespace Differ\Differ\Tests;

use PHPUnit\Framework\TestCase;

use function Differ\Differ\genDiff;

class DifferTest extends TestCase
{
    public function getFixtureFullPath($fixtureName)
    {
        $parts = [__DIR__, 'fixtures', $fixtureName];
        return realpath(implode('/', $parts));
    }

    public static function outputDataProvider()
    {
        return [
            ['file1.json', 'file2.json', 'expectedStylish.txt', 'stylish'],
            ['file1.yml', 'file2.yml', 'expectedStylish.txt', 'stylish'],
            ['file1.yml', 'file2.json', 'expectedStylish.txt', 'stylish'],
            ['file1.json', 'file2.json', 'expectedPlain.txt', 'plain'],
            ['file1.yml', 'file2.yml', 'expectedJson.json', 'json']
        ];
    }

    /**
     * @dataProvider outputDataProvider
     */
    public function testOutput($file1, $file2, $expectedFile, $format): void
    {
        $fixture1 = $this->getFixtureFullPath($file1);
        $fixture2 = $this->getFixtureFullPath($file2);
        $expected = $this->getFixtureFullPath($expectedFile);
        $actual = genDiff($fixture1, $fixture2, $format);

        $this->assertStringEqualsFile($expected, $actual);
    }

    public function testStylishAsDefault(): void
    {
        $fixture1 = $this->getFixtureFullPath('file1.yml');
        $fixture2 = $this->getFixtureFullPath('file2.yml');
        $expected = $this->getFixtureFullPath('expectedStylish.txt');
        $actual = genDiff($fixture1, $fixture2);

        $this->assertStringEqualsFile($expected, $actual);
    }
}
