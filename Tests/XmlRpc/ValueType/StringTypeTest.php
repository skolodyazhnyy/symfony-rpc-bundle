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
use Seven\RpcBundle\XmlRpc\ValueType\StringType;

class StringTypeTest extends PHPUnit_Framework_TestCase
{
    public function testPacking()
    {
        $typeInstance = new StringType($this->getMock("Seven\\RpcBundle\\XmlRpc\\Implementation"));
        $domElement = $typeInstance->pack(new \DOMDocument(), "s-t-r-i-n-g & string");

        $this->assertEquals(
            array('value' => array('string' => 's-t-r-i-n-g & string')),
            array($domElement->tagName => XmlAssertHelper::xml2array($domElement))
        );
    }

    public function testExtracting()
    {
        $typeInstance = new StringType($this->getMock("Seven\\RpcBundle\\XmlRpc\\Implementation"));
        $document = new \DOMDocument();
        $valueEl = $document->createElement('string');
        $valueElText = $document->createTextNode("s-t-r-i-n-g & string");

        $valueEl->appendChild($valueElText);

        $document->appendChild($valueEl);

        $value = $typeInstance->extract($valueEl);

        $this->assertEquals('s-t-r-i-n-g & string', $value);
    }

}