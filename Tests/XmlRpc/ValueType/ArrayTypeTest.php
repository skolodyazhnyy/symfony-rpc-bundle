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
use Seven\RpcBundle\XmlRpc\ValueType\ArrayType;

class ArrayTypeTest extends PHPUnit_Framework_TestCase
{

    public function testPacking()
    {
        $implementationMock = $this->getMock("Seven\\RpcBundle\\XmlRpc\\Implementation");
        $implementationMock->expects($this->any())
            ->method('pack')
            ->will($this->returnCallback(function ($document) {
                /** @var $document \DOMDocument */

                return $document->createElement('test', 'test');
            }));

        $typeInstance = new ArrayType($implementationMock);
        $domElement = $typeInstance->pack(new \DOMDocument(), array('value'));

        $this->assertEquals(
            array('value' => array('array' => array('data' => array(
                'test' => 'test'
            )))),
            array($domElement->tagName => XmlAssertHelper::xml2array($domElement))
        );
    }

    public function testExtracting()
    {
        $sample = array(1, 2, 3, 'abc');

        $implementationMock = $this->getMock("Seven\\RpcBundle\\XmlRpc\\Implementation");
        $typeInstance = new ArrayType($implementationMock);

        $implementationMock->expects($this->any())
            ->method('extract')
            ->will($this->returnCallback(function (\DOMElement $element) {
                if ($element->tagName == 'test') {
                    return $element->nodeValue;
                }

                return null;
            }));

        $document = new \DOMDocument();
        $document->appendChild($arrayEl = $document->createElement('array'));
        $arrayEl->appendChild($dataEl = $document->createElement('data'));
        foreach ($sample as $item) {
            $dataEl->appendChild($document->createElement('test', $item));
        }

        $value = $typeInstance->extract($arrayEl);

        $this->assertEquals($sample, $value);
    }

}
