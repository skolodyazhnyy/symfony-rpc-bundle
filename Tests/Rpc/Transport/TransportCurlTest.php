<?php

/**
 * @author Łukasz Pior <pior.lukasz@gmail.com>
 */

namespace Seven\RpcBundle\Tests\Transport;

class TransportCurlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Seven\RpcBundle\Rpc\Exception\CurlTransportException
     *
     * @dataProvider providerMakeRequestWithCurlError
     */
    public function testMakeRequestWithCurlError($errorCode, $errorMessage)
    {
        $transportMock = $this->getMock("Seven\\RpcBundle\\Rpc\\Transport\\TransportCurl", array(
            "getCurlRequest"
        ));

        $requestMock = $this->getMock("Symfony\\Component\\HttpFoundation\\Request");

        $curlRequestMock = $this->getMock("Seven\\RpcBundle\\Rpc\\Transport\\Curl\\CurlRequest", array(
            "execute",
            "getErrorNumber",
            "getErrorMessage"
        ));

        $curlRequestMock->expects($this->once())
            ->method('execute')
            ->will($this->returnValue(false));

        $curlRequestMock->expects($this->once())
            ->method('getErrorNumber')
            ->will($this->returnValue($errorCode));

        $curlRequestMock->expects($this->once())
            ->method('getErrorMessage')
            ->will($this->returnValue($errorMessage));

        $transportMock->expects($this->once())
            ->method('getCurlRequest')
            ->will($this->returnValue($curlRequestMock));

        $transportMock->makeRequest($requestMock);
    }

    public function providerMakeRequestWithCurlError()
    {
        return array(
            array(CURLE_COULDNT_CONNECT, "Failed to connect() to host or proxy."),
            array(CURLE_OPERATION_TIMEOUTED, "Operation timeout. The specified time-out period was reached according to the conditions."),
        );
    }
}