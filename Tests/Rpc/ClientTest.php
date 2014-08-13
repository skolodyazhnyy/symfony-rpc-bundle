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

use Seven\RpcBundle\Rpc\Client;
use Seven\RpcBundle\Rpc\Method\MethodCall;
use Symfony\Component\HttpFoundation\Request;

class ClientTest extends \PHPUnit_Framework_TestCase
{

    public function testSuccessCall()
    {
        $implMock = $this->getMock("Seven\\RpcBundle\\Rpc\\Implementation");
        $transportMock = $this->getMock("Seven\\RpcBundle\\Rpc\\Transport\\TransportInterface");
        $httpRequestMock = $this->getMock("Symfony\\Component\\HttpFoundation\\Request");
        $httpResponseMock = $this->getMock("Symfony\\Component\\HttpFoundation\\Response");
        $methodReturnMock = $this->getMock("Seven\\RpcBundle\\Rpc\\Method\\MethodReturn");

        $client = new Client("http://webservice.url/path", $implMock, $transportMock);

        $implMock->expects($this->once())
            ->method("createHttpRequest")
            ->with($this->callback(function (MethodCall $methodCall) {
                return $methodCall->getMethodName() == "method.name" &&
                    $methodCall->getParameters() == array('param_1', 'param_2');
            }))
            ->will($this->returnValue($httpRequestMock));

        $transportMock->expects($this->once())
            ->method("makeRequest")
            ->will($this->returnValue($httpResponseMock));

        $implMock->expects($this->once())
            ->method("createMethodResponse")
            ->with($this->equalTo($httpResponseMock))
            ->will($this->returnValue($methodReturnMock));

        $methodReturnMock->expects($this->once())
            ->method("getReturnValue")
            ->will($this->returnValue("return_value"));

        $this->assertEquals('return_value', $client->call("method.name", array('param_1', 'param_2')));
    }

}
