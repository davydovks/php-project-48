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

    public function testGenDiff(): void
    {
        $JSONFile1 = $this->getFixtureFullPath('file1.json');
        $JSONFile2 = $this->getFixtureFullPath('file2.json');
        $expected = $this->getFixtureFullPath('output.txt');
        $actual = genDiff($JSONFile1, $JSONFile2);

        $this->assertStringEqualsFile($expected, $actual);
    }
}