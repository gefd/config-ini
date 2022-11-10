<?php

namespace Test;

require_once(dirname(__DIR__) . '/src/Config.php');
require_once(dirname(__DIR__) . '/src/IniLoader.php');

use PHPUnit\Framework\TestCase;
use ConfigIni\IniLoader;
use ConfigIni\Config;

class IniLoaderTest extends TestCase
{
    public function testBasicArray()
    {
        $config = IniLoader::FromArray([
            'one' => 1,
            'two' => 'two',
            'true' => 'true'
        ]);

        $this->assertEquals(1, $config->get('one'));
        $this->assertEquals('two', $config->get('two'));
        $this->assertEquals(true, $config->get('true'), 'Boolean string is converted to a boolean');
    }

    public function testCreateKeyArrayFirstLevel()
    {
        $input = ['key.pair' => 'value'];
        $expected = ['key' => ['pair' => 'value']];
        $config = IniLoader::FromArray($input);
        $actual = $config->getArray();

        $this->assertEquals($expected, $actual, 'Incorrect result: ' . \var_export($actual, true));
    }

    public function testCreateKeyArrayMultipleFirstLevel()
    {
        $input = ['key.pair' => 'value', 'key.apple' => 'value'];
        $expected = ['key' => ['pair' => 'value', 'apple' => 'value']];
        $actual = IniLoader::FromArray($input)->getArray();

        $this->assertEquals($expected, $actual);
    }

    public function testCreateKeyArraySecondLevel()
    {
        $input = ['foo.bar' => ['key.pair' => 'value']];
        $expected = ['foo' => ['bar' => ['key' => ['pair' => 'value']]]];
        $config = IniLoader::FromArray($input);
        $actual = $config->getArray();
        $this->assertEquals($expected, $actual, 'Incorrect result: ' . \var_export($actual, true));
    }

    public function testCreateMultipleLevels()
    {
        $input = ['foo' => ['bar.baz' => 'value', 'bar.quux' => 'value']];
        $expected = ['foo' => ['bar' => ['baz' => 'value', 'quux' => 'value']]];
        $actual = IniLoader::FromArray($input)->getArray();

        $this->assertEquals($expected, $actual, 'Incorrect result: ' . \var_export($actual, true));
    }

    public function testConfigArrays()
    {
        $config = IniLoader::FromArray([
            'one' => [
                'two' => [
                    'three' => 3,
                    'four' => 4
                ]
            ]
        ]);
        $this->assertNotNull($config->get('one'));
        $this->assertInstanceOf(Config::class, $config->get('one'));
        $this->assertEquals(3, $config->get('one.two.three'));
        $this->assertEquals(4, $config->get('one.two.four'));
        $this->assertCount(2, $config->get('one.two'));
        $one = $config->get('one');
        $this->assertNotNull($one->get('two.three'));
        $this->assertEquals(3, $one->get('two.three'));
        $this->assertEquals(4, $one->get('two.four'));
        $two = $config->get('one.two');
        $this->assertEquals(3, $two->get('three'));
        $this->assertEquals(4, $two->get('four'));
    }

    public function testFromStringParsesDotFormat()
    {
        $config = IniLoader::FromString(\implode(\PHP_EOL, [
            '[one]',
            'two.three=3',
            'two.four=4',
            'two/five=5'
        ]));
        $expected = [
            'one' => [
                'two' => [
                    'three' => 3,
                    'four' => 4,
                    'five' => 5
                ]
            ]
        ];
        $this->assertEquals($expected, $config->getArray());
    }

    public function testFromStringParsesArrayFormat()
    {
        $config = IniLoader::FromString(\implode(\PHP_EOL, [
            '[one]',
            'two[]=2',
            'two[]="two"',
            'three.three[]=3',
            'three.three[]="three"'
        ]));

        $expected = [
            'one' => [
                'two' => [ 2 , 'two' ],
                'three' => [ 'three' => [ 3, 'three' ] ]
            ]
        ];

        $this->assertIsArray($config->get('one.two'), 'one.two returns a simple array');
        $this->assertIsArray($config->get('one.three.three'), 'one.three returns a simple array');
        $this->assertEquals([2, 'two'], $config->get('one.two'), 'one.two array contents match expected array');
        $this->assertEquals([3, 'three'], $config->get('one.three.three'), 'one.three array contents match expected array');

        $this->assertEquals($expected, $config->getArray(), 'Configuration array matches the expected array');
    }

    public function testFromStringWithGroupDot()
    {
        $config = IniLoader::FromString(\implode(\PHP_EOL, [
            '[one.two]',
            'three=3'
        ]));

        $expected = [ 'one' => [ 'two' => [ 'three' => 3 ] ] ];
        $this->assertEquals(3, $config->get('one.two.three'));
        $this->assertEquals($expected, $config->getArray());
    }

    public function testFromStringWithMultipleGroupDots()
    {
        $config = IniLoader::FromString(\implode(\PHP_EOL, [
            '[first.one]',
            'two=3',
            '[first.two]',
            'three=4'
        ]));

        $expected = [ 'first' => [ 'one' => [ 'two' => 3 ], 'two' => [ 'three' => 4 ] ] ];
        $this->assertEquals(3, $config->get('first.one.two'));
        $this->assertEquals(4, $config->get('first.two.three'));
        $this->assertEquals($expected, $config->getArray());
    }

    public function testFromStringWithAddedGroupDot()
    {
        $config = IniLoader::FromString(\implode(\PHP_EOL, [
            '[first]',
            'one=2',
            '[first.two]',
            'three=4'
        ]));

        $expected = [ 'first' => [ 'one' => 2, 'two' => [ 'three' => 4 ] ] ];
        $this->assertEquals(2, $config->get('first.one'));
        $this->assertEquals(4, $config->get('first.two.three'));
        $this->assertEquals($expected, $config->getArray());
    }

    public function testGroupDotOverlapsChildDot()
    {
        $config = IniLoader::FromString(\implode(\PHP_EOL, [
            '[first.one]',
            'one.one=1'
        ]));
        $this->assertEquals(1, $config->get('first.one.one.one'));
    }

    public function testChildDotOverlapsOtherChild()
    {
        $this->expectException(\ErrorException::class);
        $config = IniLoader::FromString(\implode(\PHP_EOL, [
            '[first]',
            'one=1',
            'one.two=1'
        ]));
        // ErrorException should have been thrown due to overlapping keys
        $this->fail();
    }

    public function testChildArray()
    {
        $config = IniLoader::FromString(\implode(\PHP_EOL, [
            '[first]',
            'one.two[]=1',
            'one.two[]=2',
            'one.two[]=3'
        ]));
        $this->assertEquals([1,2,3], $config->get('first.one.two'));
        $this->assertEquals(1, $config->get('first.one.two.0'));
        $this->assertInstanceOf(Config::class, $config->get('first.one'));
        $children = $config->get('first.one');
        $this->assertEquals([1,2,3], $children->get('two'));
        $this->assertEquals(1, $children->get('two.0'));
    }
}
