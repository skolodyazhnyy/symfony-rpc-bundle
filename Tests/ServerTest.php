<?php
/*
 * This file is part of the Symfony bundle Seven/Rpc.
 *
 * (c) Sergey Kolodyazhnyy <sergey.kolodyazhnyy@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Seven\RpcBundle\Tests;

use PHPUnit_Framework_TestCase;
use Seven\RpcBundle\Server;

class ServerTest extends PHPUnit_Framework_TestCase
{
    public function testRequestHandle()
    {
        $httpRequestMock = $this->getMock("Symfony\\Component\\HttpFoundation\\Request");
        $httpResponseMock = $this->getMock("Symfony\\Component\\HttpFoundation\\Response");
        $implementationMock = $this->getMock("Seven\\RpcBundle\\Rpc\\Implementation");
        $methodCallMock = $this->getMock("Seven\\RpcBundle\\Rpc\\MethodCall", array(), array(), '', false);
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
            ->with($this->isInstanceOf("Seven\\RpcBundle\\Rpc\\MethodReturn"))
            ->will($this->returnValue($httpResponseMock));

        $server = new Server($implementationMock);
        $server->addHandler('method', $handlerMock);

        $httpResponse = $server->handle($httpRequestMock);

        $this->assertSame($httpResponseMock, $httpResponse);
    }

}
