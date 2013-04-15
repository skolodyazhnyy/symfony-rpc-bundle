<?php

/*
 * This file is part of the Symfony bundle XmlRpc/Server.
 *
 * (c) Sergey Kolodyazhnyy <sergey.kolodyazhnyy@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Seven\RpcBundle\Tests\XmlRpc\ValueType;
use PHPUnit_Framework_TestCase;
use Seven\RpcBundle\XmlRpc\ValueType\ArrayType;

class ArrayTypeTest extends PHPUnit_Framework_TestCase
{
    public function testPacking()
    {
        $valueFactoryMock = $this->getMock("Seven\\RpcBundle\\XmlRpc\\Implementation");
        $valueFactoryMock->expects($this->any())
            ->method('pack')
            ->will($this->returnCallback(function($document) {
                /** @var $document \DOMDocument */

                return $document->createElement('test', 'test');
            }));

        $typeInstance = new ArrayType($valueFactoryMock);
        $domElement = $typeInstance->pack(new \DOMDocument(), array('value'));

        $this->assertEquals(
            array('value' => array('array' => array('data' => array(
                'test' => 'test'
            )))),
            array($domElement->tagName => XmlAssertHelper::xml2array($domElement))
        );
    }

}
