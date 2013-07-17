<?php
/*
 * This file is part of the Symfony bundle Seven/Rpc.
 *
 * (c) Sergey Kolodyazhnyy <sergey.kolodyazhnyy@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Seven\RpcBundle\Tests\JsonRpc;
use PHPUnit_Framework_TestCase;
use Seven\RpcBundle\Rpc\Method\MethodCall;
use Seven\RpcBundle\JsonRpc\Implementation;

class ImplementationMethodCallTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerRpcCallWithParameters
     */

    public function testRpcCallWithParameters($methodName, $methodParams, $methodId)
    {
        $requestJson = json_encode(array(
            'jsonrpc' => '2.0',
            'method'  => $methodName,
            'params'  => $methodParams,
            'id'      => $methodId
        ));

        $impl = new Implementation();
        $requestMock = $this->getMock("Symfony\\Component\\HttpFoundation\\Request");

        $requestMock->expects($this->once())
            ->method("getContent")
            ->will($this->returnValue($requestJson));

        $methodCall = $impl->createMethodCall($requestMock);

        $this->assertEquals($methodId,     $methodCall->getCallId());
        $this->assertEquals($methodName,   $methodCall->getMethodName());
        $this->assertEquals($methodParams, $methodCall->getParameters());
    }

    public function providerRpcCallWithParameters()
    {
        return array(
            array('methodname', array(1, 2, 3), 1),             // positional parameters
            array('methodname', array('a' => 1, 'b' => 2), 2),  // named parameters
            array('methodname', array(), 3),                    // no parameters
            array('methodname', array(), null)                  // notification
        );
    }

    public function testExtractingCallFromEmptyRequest()
    {
        $this->setExpectedException("Seven\\RpcBundle\\Exception\\InvalidJsonRpcContent");
        $impl = new Implementation();
        $requestMock = $this->getMock("Symfony\\Component\\HttpFoundation\\Request");
        $requestMock->expects($this->once())
            ->method("getContent")
            ->will($this->returnValue(""));

        $impl->createMethodCall($requestMock);
    }

    public function testExtractingCallWithoutMethodName()
    {
        $this->setExpectedException("Seven\\RpcBundle\\Exception\\InvalidJsonRpcContent");
        $requestJson = json_encode(array(
            'jsonrpc' => '2.0',
            'params'  => array(1, 2, 3),
            'id'      => 1
        ));

        $impl = new Implementation();
        $requestMock = $this->getMock("Symfony\\Component\\HttpFoundation\\Request");
        $requestMock->expects($this->once())
            ->method("getContent")
            ->will($this->returnValue($requestJson));

        $impl->createMethodCall($requestMock);
    }

    /**
     * @dataProvider providerPackingCall
     */

    public function testPackingCall($methodName, $methodParams, $methodCallId, $rawRequestData)
    {
        $impl = new Implementation();
        $rpcCall = new MethodCall($methodName, $methodParams, $methodCallId);
        $httpRequest = $impl->createHttpRequest($rpcCall);

        $this->assertEquals($rawRequestData, $httpRequest->getContent());
        $this->assertEquals("text/json", $httpRequest->headers->get('Content-Type'));
    }

    public function providerPackingCall()
    {
        return array(
            array("subtract", array(42, 23), 1, '{"jsonrpc":"2.0","method":"subtract","params":[42,23],"id":1}'),
        );
    }

}
