<?php

/*
 * This file is part of the Symfony bundle Seven/Rpc.
 *
 * (c) Sergey Kolodyazhnyy <sergey.kolodyazhnyy@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Seven\RpcBundle\Tests\XmlRpc\ValueType;
use PHPUnit_Framework_TestCase;
use Seven\RpcBundle\XmlRpc\ValueType\ObjectType;

class ObjectTypeTest extends PHPUnit_Framework_TestCase
{
    public function testPacking()
    {
        $valueFactoryMock = $this->getValueFactoryMock();
        $typeInstance = new ObjectType($valueFactoryMock);
        $domElement = $typeInstance->pack(new \DOMDocument(), array('aProp' => 'aValue'));

        $this->assertEquals(
            array('value' => array('struct' => array(
                'member' => array('name' => 'aProp', 'value' => 'aValue')
            ))),
            array($domElement->tagName => XmlAssertHelper::xml2array($domElement))
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */

    public function getValueFactoryMock()
    {
        $valueFactoryMock = $this->getMock("Seven\\RpcBundle\\XmlRpc\\Implementation");
        $valueFactoryMock->expects($this->any())
            ->method('pack')
            ->will($this->returnCallback(function ($document, $value) {
                return $document->createElement('value', $value);
            }));

        return $valueFactoryMock;
    }

    public function testPackingWhenSomeIndexesAreNumeric()
    {
        $valueFactoryMock = $this->getValueFactoryMock();
        $typeInstance = new ObjectType($valueFactoryMock);
        $domElement = $typeInstance->pack(new \DOMDocument(), array('numericValue'));

        $this->assertEquals(
            array('value' => array('struct' => array(
                'member' => array('name' => 'i0', 'value' => 'numericValue')
            ))),
            array($domElement->tagName => XmlAssertHelper::xml2array($domElement))
        );
    }

}
