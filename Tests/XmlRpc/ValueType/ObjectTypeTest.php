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

    public function testExtracting()
    {
        $sample = array('prop_a' => 'value_a', 'prop_b' => 'value_b');

        $implementationMock = $this->getMock("Seven\\RpcBundle\\XmlRpc\\Implementation");
        $typeInstance = new ObjectType($implementationMock);

        $implementationMock->expects($this->any())
            ->method('extract')
            ->will($this->returnCallback(function (\DOMElement $element) {
                if($element->tagName == 'test')

                    return $element->nodeValue;
                return null;
            }));

        $document = new \DOMDocument();
        $document->appendChild($structEl = $document->createElement('struct'));
        foreach ($sample as $key => $item) {
            $structEl->appendChild($memberEl = $document->createElement('member'));
            $memberEl->appendChild($document->createElement('name', $key));
            $memberEl->appendChild($valueEl = $document->createElement('value'));
            $valueEl->appendChild($document->createElement('test', $item));
        }

        $value = $typeInstance->extract($structEl);

        $this->assertEquals($sample, $value);
    }

}
