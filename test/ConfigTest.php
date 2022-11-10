<?php

namespace Test;

require_once(dirname(__DIR__) . '/src/Config.php');

use PHPUnit\Framework\TestCase;
use ConfigIni\Config;

class ConfigTest extends TestCase
{
    private Config $config;

    public function setUp() : void
    {
        $this->config = new Config([
            'one' => 1,
            'two' => '2',
            'three' => [ 'four' => 4 ],
            'five' => 5,
            'six' => ['seven' => [ 'eight' => 8 ]],
            'true' => 'true',
            'false' => false,
        ]);
    }

    public function testSimpleValue()
    {
        $this->assertEquals(5, $this->config->get('five'));
    }
    public function testGetArray()
    {
        $array = ['a' => 1, 'b' => 2];
        $config = new Config($array);
        $this->assertEquals($array, $config->getArray());
    }
    public function testMergeConfig()
    {
        $this->config->merge(new Config(['one' => 'one', 'seven' => 7]));
        $this->assertEquals('one', $this->config->get('one'));
        $this->assertEquals(7, $this->config->get('seven'));
    }
    public function testCorrectSimpleTypes()
    {
        $this->assertIsInt($this->config->get('one'));
        $this->assertIsInt($this->config->get('two'));
        $this->assertIsBool($this->config->get('true'));
        $this->assertIsBool($this->config->get('false'));
    }
    public function testNestedConfigInstance()
    {
        $this->assertInstanceOf(Config::class, $this->config->get('three'));
        $this->assertInstanceOf(Config::class, $this->config->get('six'));
    }
    public function testGetConfigPath()
    {
        $this->assertEquals(4, $this->config->get('three.four'));
        $this->assertEquals(4, $this->config->get('three/four'));
        $this->assertEquals(8, $this->config->get('six.seven.eight'));
        $this->assertEquals(8, $this->config->get('six/seven.eight'));
        $this->assertEquals(8, $this->config->get('six/seven/eight'));
        $this->assertEquals(8, $this->config->get('six.seven/eight'));
    }
    public function testDirectAccessorViaMagicGet()
    {
        $this->assertEquals(1, $this->config->one);
    }
    public function testArrayAccess()
    {
        $this->assertEquals(1, $this->config['one']);
        $this->assertEquals(4, $this->config['three']['four']);
        $this->assertTrue($this->config->offsetExists('one'));
        $this->assertTrue(isset($this->config['one']));
        $this->config->offsetSet('one', 'one');
        $this->assertEquals('one', $this->config->get('one'));
        $this->config['one'] = 1;
        $this->assertEquals(1, $this->config['one']);
        $this->config->offsetUnset('one');
        $this->assertFalse(isset($this->config['one']));
        $this->assertNull($this->config->get('one'));
        $this->assertEquals(2, $this->config['two']);
        unset($this->config['two']);
        $this->assertFalse(isset($this->config['two']));
        $this->assertNull($this->config->get('two'));
    }
}
