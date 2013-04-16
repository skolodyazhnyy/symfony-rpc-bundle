<?php
/*
 * This file is part of the Symfony bundle Seven/Rpc.
 *
 * (c) Sergey Kolodyazhnyy <sergey.kolodyazhnyy@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Seven\RpcBundle\Rpc;
use Seven\RpcBundle\Exception\UnknownMethodResponse;
use Seven\RpcBundle\Rpc\Method\MethodCall;
use Seven\RpcBundle\Rpc\Method\MethodFault;
use Seven\RpcBundle\Rpc\Method\MethodResponse;
use Seven\RpcBundle\Rpc\Method\MethodReturn;
use Seven\RpcBundle\Rpc\Transport\TransportCurl;
use Seven\RpcBundle\Rpc\Transport\TransportInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Client implements ClientInterface
{

    protected $impl = null;
    protected $webServiceUrl = null;
    protected $transport;

    /**
     * @param $webServiceUrl
     * @param Implementation     $impl
     * @param TransportInterface $transport
     */

    public function __construct($webServiceUrl, Implementation $impl, TransportInterface $transport = null)
    {
        $this->impl = $impl;
        $this->webServiceUrl = $webServiceUrl;
        $this->transport = $transport ?: new TransportCurl();
    }

    /**
     * @param $methodName
     * @param  array             $parameters
     * @return mixed|null|string
     */

    public function call($methodName, $parameters = array())
    {
        return $this->_call(new MethodCall($methodName, $parameters));
    }

    /**
     * @param  MethodCall                                       $call
     * @throws \Exception
     * @throws \Seven\RpcBundle\Exception\UnknownMethodResponse
     * @param  MethodCall                                       $call
     * @return null|string
     */

    protected function _call(MethodCall $call)
    {
        $methodResponse = $this->_handle($call);

        if($methodResponse instanceof MethodFault)
            throw $methodResponse->getException();
        if($methodResponse instanceof MethodReturn)

            return $methodResponse->getReturnValue();

        throw new UnknownMethodResponse('Unable to determine method response type');
    }

    /**
     * @param  MethodCall     $call
     * @return MethodResponse
     */

    protected function _handle(MethodCall $call)
    {
        $request = $this->impl->createHttpRequest($call);
        $request = Request::create($this->webServiceUrl, 'GET', array(), array(), array(), array(), $request->getContent());
        $response = $this->transport->makeRequest($request);

        return $this->impl->createMethodResponse($response);
    }

}
