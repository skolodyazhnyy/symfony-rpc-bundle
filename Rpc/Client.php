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

use Exception;
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
     * Constructor.
     *
     * @param string             $webServiceUrl
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
     * {@inheritdoc}
     */
    public function call($methodName, array $parameters = array())
    {
        return $this->callMethod(new MethodCall($methodName, $parameters));
    }

    /**
     * Call MethodCall.
     *
     * @param MethodCall $call
     *
     * @return mixed
     *
     * @throws Exception
     * @throws UnknownMethodResponse
     */
    protected function callMethod(MethodCall $call)
    {
        $methodResponse = $this->handleMethodCall($call);

        if ($methodResponse instanceof MethodFault) {
            throw $methodResponse->getException();
        }

        if ($methodResponse instanceof MethodReturn) {
            return $methodResponse->getReturnValue();
        }

        throw new UnknownMethodResponse('Unable to determine method response type');
    }

    /**
     * Handle MethodCall.
     *
     * @param MethodCall $call
     *
     * @return MethodResponse
     */
    protected function handleMethodCall(MethodCall $call)
    {
        $request = $this->impl->createHttpRequest($call);
        $request = Request::create($this->webServiceUrl, 'GET', array(), array(), array(), array(), $request->getContent());
        $response = $this->transport->makeRequest($request);

        return $this->impl->createMethodResponse($response);
    }
}
