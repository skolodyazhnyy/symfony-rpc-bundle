<?php

/*
 * This file is part of the Symfony bundle XmlRpc/Server.
 *
 * (c) Sergey Kolodyazhnyy <sergey.kolodyazhnyy@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Seven\RpcBundle\Tests\XmlRpc;
use PHPUnit_Framework_TestCase;
use Seven\RpcBundle\Rpc\MethodReturn;
use Seven\RpcBundle\XmlRpc\Implementation;

class ImplementationResponseTest extends PHPUnit_Framework_TestCase
{
    public function testValueReturnResponse()
    {
        $impl = new Implementation();
        $response = new MethodReturn("test");
        $httpResponse = $impl->createHttpResponse($response);

        $this->assertEquals("<?xml version=\"\" encoding=\"UTF-8\"?>\n<methodResponse><params><param><value><string>test</string></value></param></params></methodResponse>\n", $httpResponse->getContent());
        $this->assertEquals("text/xml", $httpResponse->headers->get('Content-Type'));
    }

}
