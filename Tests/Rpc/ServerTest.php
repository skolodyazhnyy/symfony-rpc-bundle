<?php
/*
 * This file is part of the Symfony bundle Seven/Rpc.
 *
 * (c) Sergey Kolodyazhnyy <sergey.kolodyazhnyy@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Seven\RpcBundle\Tests\Rpc;

use PHPUnit_Framework_TestCase;
use Seven\RpcBundle\Rpc\Server;

class ServerTest extends PHPUnit_Framework_TestCase
{
    public function testRequestHandle()
    {
        $httpRequestMock = $this->getMock("Symfony\\Component\\HttpFoundation\\Request");
        $httpResponseMock = $this->getMock("Symfony\\Component\\HttpFoundation\\Response");
        $implementationMock = $this->getMock("Seven\\RpcBundle\\Rpc\\Implementation");
        $methodCallMock = $this->getMock("Seven\\RpcBundle\\Rpc\\Method\\MethodCall", array(), array(), '', false);
        $handlerMock = $this->getMock("stdClass", array('op'));

        $handlerMock->expects($this->once())
            ->method('op')
            ->with($this->equalTo('parameter_1'), $this->equalTo('parameter_2'))
            ->will($this->returnValue('return_value'));

        $methodCallMock->expects($this->any())
            ->method('getMethodName')
            ->will($this->returnValue('method.op'));

        $methodCallMock->expects($this->any())
            ->method('getParameters')
            ->will($this->returnValue(array('parameter_1', 'parameter_2')));

        $implementationMock->expects($this->once())
            ->method('createMethodCall')
            ->with($this->equalTo($httpRequestMock))
            ->will($this->returnValue($methodCallMock));

        $implementationMock->expects($this->once())
            ->method('createHttpResponse')
            ->with($this->isInstanceOf("Seven\\RpcBundle\\Rpc\\Method\\MethodReturn"))
            ->will($this->returnValue($httpResponseMock));

        $server = new Server($implementationMock);
        $server->addHandler('method', $handlerMock);

        $httpResponse = $server->handle($httpRequestMock);

        $this->assertSame($httpResponseMock, $httpResponse);
    }

    public function testRequestHandleWithException()
    {
        $httpRequestMock = $this->getMock("Symfony\\Component\\HttpFoundation\\Request");
        $httpResponseMock = $this->getMock("Symfony\\Component\\HttpFoundation\\Response");
        $implementationMock = $this->getMock("Seven\\RpcBundle\\Rpc\\Implementation");
        $methodCallMock = $this->getMock("Seven\\RpcBundle\\Rpc\\Method\\MethodCall", array(), array(), '', false);
        $handlerMock = $this->getMock("stdClass", array('op'));

        $handlerMock->expects($this->once())
            ->method('op')
            ->with($this->equalTo('parameter_1'), $this->equalTo('parameter_2'))
            ->will($this->returnCallback(function () {
                throw new \Exception('Test', 10);
            }));

        $methodCallMock->expects($this->any())
            ->method('getMethodName')
            ->will($this->returnValue('method.op'));

        $methodCallMock->expects($this->any())
            ->method('getParameters')
            ->will($this->returnValue(array('parameter_1', 'parameter_2')));

        $implementationMock->expects($this->once())
            ->method('createMethodCall')
            ->with($this->equalTo($httpRequestMock))
            ->will($this->returnValue($methodCallMock));

        $implementationMock->expects($this->once())
            ->method('createHttpResponse')
            ->with($this->isInstanceOf("Seven\\RpcBundle\\Rpc\\Method\\MethodFault"))
            ->will($this->returnValue($httpResponseMock));

        $server = new Server($implementationMock);
        $server->addHandler('method', $handlerMock);

        $httpResponse = $server->handle($httpRequestMock);

        $this->assertSame($httpResponseMock, $httpResponse);
    }

    public function testServerCallWithCallback()
    {
        $implementationMock = $this->getMock("Seven\\RpcBundle\\Rpc\\Implementation");
        $callbackMock = $this->getMock('stdClass', array('callbackMethod'));

        $callbackMock->expects($this->once())
            ->method('callbackMethod')
            ->with($this->equalTo("parameter_1"), $this->equalTo("parameter_2"))
            ->will($this->returnValue("result_value"));

        $server = new Server($implementationMock);
        $server->addHandler('rpcMethodName', array($callbackMock, 'callbackMethod'));

        $this->assertEquals("result_value", $server->call('rpcMethodName', array("parameter_1", "parameter_2")));
    }

    public function testServerCallWithObject()
    {
        $implementationMock = $this->getMock("Seven\\RpcBundle\\Rpc\\Implementation");
        $server = new Server($implementationMock);
        $server->addHandler('rpcMethodGroup', new \Seven\RpcBundle\Tests\Rpc\Asserts\ServerHandler());

        $this->assertEquals("parameter_1:parameter_2", $server->call('rpcMethodGroup.concat', array("parameter_1", "parameter_2")));
    }

    public function testServerCallWithClassName()
    {
        $implementationMock = $this->getMock("Seven\\RpcBundle\\Rpc\\Implementation");
        $server = new Server($implementationMock);
        $server->addHandler('rpcMethodGroup', "Seven\\RpcBundle\\Tests\\Rpc\\Asserts\\ServerHandler");

        $this->assertEquals("parameter_1:parameter_2", $server->call('rpcMethodGroup.concat', array("parameter_1", "parameter_2")));
    }

    public function testServerCallExceptionInvalidGroupname()
    {
        $this->setExpectedException("Seven\\RpcBundle\\Exception\\MethodNotExists");

        $implementationMock = $this->getMock("Seven\\RpcBundle\\Rpc\\Implementation");
        $server = new Server($implementationMock);
        $server->addHandler('test', "Seven\\RpcBundle\\Tests\\Rpc\\Asserts\\ServerHandler");
        $this->assertEquals("parameter_1:parameter_2", $server->call('rpcMethodGroup.concat', array("parameter_1", "parameter_2")));
    }

    public function testServerCallExceptionInvalidMethodname()
    {
        $this->setExpectedException("Seven\\RpcBundle\\Exception\\MethodNotExists");

        $implementationMock = $this->getMock("Seven\\RpcBundle\\Rpc\\Implementation");
        $server = new Server($implementationMock);
        $server->addHandler('test', "Seven\\RpcBundle\\Tests\\Rpc\\Asserts\\ServerHandler");
        $this->assertEquals("parameter_1:parameter_2", $server->call('concat', array("parameter_1", "parameter_2")));
    }

    public function testServerCallExceptionInvalidMethodnameInGroup()
    {
        $this->setExpectedException("Seven\\RpcBundle\\Exception\\MethodNotExists");

        $implementationMock = $this->getMock("Seven\\RpcBundle\\Rpc\\Implementation");
        $server = new Server($implementationMock);
        $server->addHandler('rpcMethodGroup', "Seven\\RpcBundle\\Tests\\Rpc\\Asserts\\ServerHandler");
        $this->assertEquals("parameter_1:parameter_2", $server->call('rpcMethodGroup.nonexists', array("parameter_1", "parameter_2")));
    }

    public function testServerCallExceptionInvalidCallback()
    {
        $this->setExpectedException("Seven\\RpcBundle\\Exception\\MethodNotExists");

        $implementationMock = $this->getMock("Seven\\RpcBundle\\Rpc\\Implementation");
        $server = new Server($implementationMock);
        $server->addHandler('rpcMethodGroup', 111);
        $this->assertEquals("parameter_1:parameter_2", $server->call('rpcMethodGroup', array("parameter_1", "parameter_2")));
    }

}
