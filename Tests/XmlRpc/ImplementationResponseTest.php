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
use Seven\RpcBundle\Rpc\MethodFault;
use Seven\RpcBundle\Rpc\MethodReturn;
use Seven\RpcBundle\XmlRpc\Implementation;
use Seven\RpcBundle\Tests\XmlRpc\Asserts\MethodUnknownResponse;

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

    public function testFaultResponse()
    {
        $impl = new Implementation();
        $response = new MethodFault(new \Exception("Too many parameters.", 4));
        $httpResponse = $impl->createHttpResponse($response);

        $this->assertEquals("<?xml version=\"\" encoding=\"UTF-8\"?>\n<methodResponse><fault><value><struct><member><name>faultCode</name><value><int>4</int></value></member><member><name>faultString</name><value><string>Too many parameters.</string></value></member></struct></value></fault></methodResponse>\n",
            $httpResponse->getContent());

        $this->assertEquals("text/xml", $httpResponse->headers->get('Content-Type'));
    }

    public function testUnknownResponse()
    {
        $this->setExpectedException("Seven\\RpcBundle\\Exception\\UnknownMethodResponse");

        $impl = new Implementation();
        $response = new MethodUnknownResponse();
        $httpResponse = $impl->createHttpResponse($response);
    }

}
