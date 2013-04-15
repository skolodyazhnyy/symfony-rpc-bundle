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
use Seven\RpcBundle\XmlRpc\ValueType\BlobType;

class BlobTypeTest extends PHPUnit_Framework_TestCase
{
    public function testPacking()
    {
        $typeInstance = new BlobType($this->getMock("Seven\\RpcBundle\\XmlRpc\\Implementation"));
        $domElement = $typeInstance->pack(new \DOMDocument(), "s-t-r-i-n-g");

        $this->assertEquals(
            array('value' => array('base64' => base64_encode('s-t-r-i-n-g'))),
            array($domElement->tagName => XmlAssertHelper::xml2array($domElement))
        );
    }

    public function testExtracting()
    {
        $typeInstance = new BlobType($this->getMock("Seven\\RpcBundle\\XmlRpc\\Implementation"));
        $document = new \DOMDocument();
        $document->appendChild($valueEl = $document->createElement('double', base64_encode('s-t-r-i-n-g')));

        $value = $typeInstance->extract($valueEl);

        $this->assertEquals('s-t-r-i-n-g', $value);
    }

}
