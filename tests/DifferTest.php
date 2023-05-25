<?php

namespace Differ\Differ\Tests;

use PHPUnit\Framework\TestCase;
use function Differ\Differ\genDiff;
use function Differ\Formatters\genOutput;

class DifferTest extends TestCase
{
    public function getFixtureFullPath($fixtureName)
    {
        $parts = [__DIR__, 'fixtures', $fixtureName];
        return realpath(implode('/', $parts));
    }

    public function testJsonDiff(): void
    {
        $JSONFile1 = $this->getFixtureFullPath('file1.json');
        $JSONFile2 = $this->getFixtureFullPath('file2.json');
        $expected = $this->getFixtureFullPath('expectedStylish.txt');
        $diff = genDiff($JSONFile1, $JSONFile2);
        $actual = genOutput($diff, 'stylish');

        $this->assertStringEqualsFile($expected, $actual);
    }

    public function testYamlDiff(): void
    {
        $file1 = $this->getFixtureFullPath('file1.yml');
        $file2 = $this->getFixtureFullPath('file2.yml');
        $expected = $this->getFixtureFullPath('expectedStylish.txt');
        $diff = genDiff($file1, $file2);
        $actual = genOutput($diff, 'stylish');

        $this->assertStringEqualsFile($expected, $actual);
    }
}