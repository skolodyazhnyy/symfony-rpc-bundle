<?php

/*
 * This file is part of the Symfony bundle Seven/Rpc.
 *
 * (c) Sergey Kolodyazhnyy <sergey.kolodyazhnyy@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Seven\RpcBundle\Tests\XmlRpc;

use PHPUnit_Framework_TestCase;
use Seven\RpcBundle\XmlRpc\ValueType;
use Seven\RpcBundle\XmlRpc\Implementation;

class ImplementationTypeDetectTest extends PHPUnit_Framework_TestCase
{
    /** @var Implementation */
    public $impl;

    public function setUp()
    {
        $this->impl = new Implementation();
    }

    public function testTypeDetectString()
    {
        $this->assertEquals(ValueType::String, $this->impl->detectType("string"),
            "Detect string when string given");
    }

    public function testTypeDetectInteger()
    {
        $this->assertEquals(ValueType::Integer, $this->impl->detectType(10),
            "Detect integer when integer given");
    }

    public function testTypeDetectBoolean()
    {
        $this->assertEquals(ValueType::Boolean, $this->impl->detectType(true),
            "Detect boolean when boolean given");
    }

    public function testTypeDetectDate()
    {
        $this->assertEquals(ValueType::Date, $this->impl->detectType(new \DateTime("now")),
            "Detect date when instance of DateTime given");
    }

    public function testTypeDetectDouble()
    {
        $this->assertEquals(ValueType::Double, $this->impl->detectType(11.1),
            "Detect decimal when decimal given");
    }

    public function testTypeDetectArray()
    {
        $this->assertEquals(ValueType::Set, $this->impl->detectType(array(1, 2, 3)),
            "Detect array when array given");
    }

    public function testTypeDetectObject()
    {
        $object = new \stdClass();
        $object->a = 10;
        $object->b = "test";

        $this->assertEquals(ValueType::Object, $this->impl->detectType($object),
            "Detect object when instance of stdClass given");

        $this->assertEquals(ValueType::Object, $this->impl->detectType(array('a' => 'aValue', 'b' => 'bValue')),
            "Detect object when associative array given");

        $this->assertEquals(ValueType::Object, $this->impl->detectType(array(0, 'key' => 'value', 1, 2)),
            "Detect object when array where only one pair is associative given");
    }

    public function testTypeDetectNull()
    {
        $this->assertEquals(ValueType::Null, $this->impl->detectType(NULL),
            "Detect NULL when NULL given");
    }

}
