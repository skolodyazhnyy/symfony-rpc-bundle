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
use Seven\RpcBundle\Rpc\Method\MethodFault;
use Seven\RpcBundle\Rpc\Method\MethodReturn;
use Seven\RpcBundle\XmlRpc\Implementation;
use Seven\RpcBundle\Tests\XmlRpc\Asserts\MethodUnknownResponse;

class ImplementationMethodResponseTest extends PHPUnit_Framework_TestCase
{
    public function testPackingValueResponse()
    {
        $impl = new Implementation();
        $response = new MethodReturn("test");
        $httpResponse = $impl->createHttpResponse($response);

        $this->assertEquals("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<methodResponse><params><param><value><string>test</string></value></param></params></methodResponse>\n", $httpResponse->getContent());
        $this->assertEquals("text/xml", $httpResponse->headers->get('Content-Type'));
    }

    public function testPackingFaultResponse()
    {
        $impl = new Implementation();
        $response = new MethodFault(new \Exception("Too many parameters.", 4));
        $httpResponse = $impl->createHttpResponse($response);

        $this->assertEquals("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<methodResponse><fault><value><struct><member><name>faultCode</name><value><int>4</int></value></member><member><name>faultString</name><value><string>Too many parameters.</string></value></member></struct></value></fault></methodResponse>\n",
            $httpResponse->getContent());

        $this->assertEquals("text/xml", $httpResponse->headers->get('Content-Type'));
    }

    public function testPackingUnknownResponse()
    {
        $this->setExpectedException("Seven\\RpcBundle\\Exception\\UnknownMethodResponse");

        $impl = new Implementation();
        $response = new MethodUnknownResponse();
        $httpResponse = $impl->createHttpResponse($response);
    }

    public function testExtractingValueResponse()
    {
        $impl = new Implementation();

        $httpResponse = $this->getMock("Symfony\\Component\\HttpFoundation\\Response");
        $httpResponse->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<methodResponse><params><param><value><string>test</string></value></param></params></methodResponse>\n"));

        $methodResponse = $impl->createMethodResponse($httpResponse);

        $this->assertInstanceOf("Seven\\RpcBundle\\Rpc\\Method\\MethodReturn", $methodResponse);
        $this->assertEquals("test", $methodResponse->getReturnValue());
    }

    public function testExtractingFaultResponse()
    {
        $impl = new Implementation();

        $httpResponse = $this->getMock("Symfony\\Component\\HttpFoundation\\Response");
        $httpResponse->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<methodResponse><fault><value><struct><member><name>faultCode</name><value><int>4</int></value></member><member><name>faultString</name><value><string>Too many parameters.</string></value></member></struct></value></fault></methodResponse>\n"));

        $methodResponse = $impl->createMethodResponse($httpResponse);

        $this->assertInstanceOf("Seven\\RpcBundle\\Rpc\\Method\\MethodFault", $methodResponse);
        $this->assertEquals(4, $methodResponse->getCode());
        $this->assertEquals("Too many parameters.", $methodResponse->getMessage());
    }

    public function testExtractingEmptyResponse()
    {
        $this->setExpectedException("Seven\\RpcBundle\\Exception\\InvalidXmlRpcContent");
        $impl = new Implementation();

        $httpResponse = $this->getMock("Symfony\\Component\\HttpFoundation\\Response");
        $httpResponse->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue(""));

        $impl->createMethodResponse($httpResponse);
    }

    public function testExtractingInvalidResponse()
    {
        $this->setExpectedException("Seven\\RpcBundle\\Exception\\InvalidXmlRpcContent");

        $impl = new Implementation();

        $httpResponse = $this->getMock("Symfony\\Component\\HttpFoundation\\Response");
        $httpResponse->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<methodCall><fault /></methodCall>\n"));

        $impl->createMethodResponse($httpResponse);
    }

    public function testExtractingStructResponse()
    {
        $responseXml = "<?xml version='1.0'?>
        <methodResponse>
            <params>
                <param>
                    <value>
                        <struct>
                            <member>
                                <name>fooName</name>
                                <value>
                                    <string>fooValue</string>
                                </value>
                            </member>
                            <member>
                                <name>barName</name>
                                <value>
                                    <int>42</int>
                                </value>
                            </member>
                        </struct>
                    </value>
                </param>
            </params>
        </methodResponse>
        ";

        $expectedResponseValues = array('fooName' => 'fooValue', 'barName' => 42);

        $impl = new Implementation();

        $httpResponse = $this->getMock("Symfony\\Component\\HttpFoundation\\Response");
        $httpResponse->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue($responseXml));

        $methodResponse = $impl->createMethodResponse($httpResponse);

        $this->assertInstanceOf("Seven\\RpcBundle\\Rpc\\Method\\MethodReturn", $methodResponse);
        $this->assertEquals($expectedResponseValues, $methodResponse->getReturnValue());
    }
}
