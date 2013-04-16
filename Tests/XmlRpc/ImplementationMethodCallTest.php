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
use Seven\RpcBundle\Rpc\Method\MethodCall;
use Seven\RpcBundle\XmlRpc\Implementation;

class ImplementationMethodCallTest extends PHPUnit_Framework_TestCase
{

    public function testExtractingCallWithParameters()
    {
        $requestXml = "<?xml version=\"1.0\"?>\n<methodCall><methodName>examples.getStateName</methodName><params><param><value><i4>41</i4></value></param></params></methodCall>";

        $impl = new Implementation();
        $requestMock = $this->getMock("Symfony\\Component\\HttpFoundation\\Request");
        $requestMock->expects($this->once())
            ->method("getContent")
            ->will($this->returnValue($requestXml));

        $methodCall = $impl->createMethodCall($requestMock);

        $this->assertEquals("examples.getStateName", $methodCall->getMethodName());
        $this->assertEquals(array(41), $methodCall->getParameters());
    }

    public function testExtractingCallWithoutParameters()
    {
        $requestXml = "<?xml version=\"1.0\"?>\n<methodCall><methodName>examples.getStateName</methodName><params /></methodCall>";

        $impl = new Implementation();
        $requestMock = $this->getMock("Symfony\\Component\\HttpFoundation\\Request");
        $requestMock->expects($this->once())
            ->method("getContent")
            ->will($this->returnValue($requestXml));

        $methodCall = $impl->createMethodCall($requestMock);

        $this->assertEquals("examples.getStateName", $methodCall->getMethodName());
        $this->assertEquals(array(), $methodCall->getParameters());
    }

    public function testExtractingCallFromEmptyRequest()
    {
        $this->setExpectedException("Seven\\RpcBundle\\Exception\\InvalidXmlRpcContent");
        $impl = new Implementation();
        $requestMock = $this->getMock("Symfony\\Component\\HttpFoundation\\Request");
        $requestMock->expects($this->once())
            ->method("getContent")
            ->will($this->returnValue(""));

        $impl->createMethodCall($requestMock);
    }


    public function testExtractingCallWithoutMethodName()
    {
        $this->setExpectedException("Seven\\RpcBundle\\Exception\\InvalidXmlRpcContent");

        $requestXml = "<?xml version=\"1.0\"?>\n<methodCall><params><param><value><i4>41</i4></value></param></params></methodCall>";

        $impl = new Implementation();
        $requestMock = $this->getMock("Symfony\\Component\\HttpFoundation\\Request");
        $requestMock->expects($this->once())
            ->method("getContent")
            ->will($this->returnValue($requestXml));

        $impl->createMethodCall($requestMock);
    }

    public function testExtractingCallWithExtraTags()
    {
        $this->setExpectedException("Seven\\RpcBundle\\Exception\\InvalidXmlRpcContent");

        $requestXml = "<?xml version=\"1.0\"?>\n<methodCall><params><param><value><i4>41</i4></value></param></params><extra /></methodCall>";

        $impl = new Implementation();
        $requestMock = $this->getMock("Symfony\\Component\\HttpFoundation\\Request");
        $requestMock->expects($this->once())
            ->method("getContent")
            ->will($this->returnValue($requestXml));

        $impl->createMethodCall($requestMock);
    }

    public function testExtractingCallFromResponseRequest()
    {
        $this->setExpectedException("Seven\\RpcBundle\\Exception\\InvalidXmlRpcContent");

        $requestXml = "<?xml version=\"1.0\"?>\n<methodResponse><params><param><value><i4>41</i4></value></param></params><extra /></methodResponse>";

        $impl = new Implementation();
        $requestMock = $this->getMock("Symfony\\Component\\HttpFoundation\\Request");
        $requestMock->expects($this->once())
            ->method("getContent")
            ->will($this->returnValue($requestXml));

        $impl->createMethodCall($requestMock);
    }

    public function testPackingCall() {
        $impl = new Implementation();
        $rpcCall = new MethodCall("examples.getStateName", array(41));
        $httpRequest = $impl->createHttpRequest($rpcCall);

        $this->assertEquals("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<methodCall><methodName>examples.getStateName</methodName><params><param><value><int>41</int></value></param></params></methodCall>\n", $httpRequest->getContent());
        $this->assertEquals("text/xml", $httpRequest->headers->get('Content-Type'));
    }

}
