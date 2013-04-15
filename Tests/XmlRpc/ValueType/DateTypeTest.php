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
use Seven\RpcBundle\XmlRpc\ValueType\DateType;

class DateTypeTest extends PHPUnit_Framework_TestCase
{
    public function testPacking()
    {
        $typeInstance = new DateType($this->getMock("Seven\\RpcBundle\\XmlRpc\\Implementation"));
        $domElement = $typeInstance->pack(new \DOMDocument(), new \DateTime("12/31/2010 12:34:50"));

        $this->assertEquals(
            array('value' => array('dateTime.iso8601' => '20101231T12:34:50')),
            array($domElement->tagName => XmlAssertHelper::xml2array($domElement))
        );
    }

    public function testExtracting()
    {
        $typeInstance = new DateType($this->getMock("Seven\\RpcBundle\\XmlRpc\\Implementation"));
        $document = new \DOMDocument();
        $document->appendChild($valueEl = $document->createElement('dateTime.iso8601', '20101231T12:34:50'));

        $value = $typeInstance->extract($valueEl);

        $this->assertEquals(new \DateTime("12/31/2010 12:34:50"), $value);
    }

}
