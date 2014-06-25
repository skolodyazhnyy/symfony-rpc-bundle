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

namespace Seven\RpcBundle\JsonRpc;

use Seven\RpcBundle\Exception\Fault;
use Seven\RpcBundle\Exception\InvalidJsonRpcContent;
use Seven\RpcBundle\Exception\InvalidJsonRpcVersion;
use Seven\RpcBundle\Exception\UnknownMethodResponse;
use Seven\RpcBundle\Rpc\Implementation as BaseImplementation;
use Seven\RpcBundle\Rpc\Method\MethodCall;
use Seven\RpcBundle\Rpc\Method\MethodResponse;
use Seven\RpcBundle\Rpc\Method\MethodFault;
use Seven\RpcBundle\Rpc\Method\MethodReturn;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Implementation extends BaseImplementation
{

    const ERROR_PARSING            = -32700;
    const ERROR_INVALID_REQUEST    = -32600;
    const ERROR_METHOD_NOT_FOUND   = -32601;
    const ERROR_INVALID_PARAMETERS = -32602;
    const ERROR_INTERNAL_ERROR     = -32603;
    const ERROR_SERVER_ERROR       = -32000;

    /**
     * @param  Request                                          $request
     * @return mixed|MethodCall
     * @throws \Seven\RpcBundle\Exception\InvalidJsonRpcVersion
     * @throws \Seven\RpcBundle\Exception\InvalidJsonRpcContent
     */

    public function createMethodCall(Request $request)
    {
        $content = $request->getContent();
        if (empty($content)) {
            throw new InvalidJsonRpcContent('The JSON-RPC request is empty', self::ERROR_INVALID_REQUEST);
        }

        $data = json_decode($content, true);

        if (is_null($data)) {
            throw new InvalidJsonRpcContent('The JSON-RPC call is not valid', self::ERROR_PARSING);
        }

        if (empty($data['jsonrpc']) || version_compare($data['jsonrpc'], '2.0', '<')) {
            throw new InvalidJsonRpcVersion('The JSON-RPC call version is not supported', self::ERROR_SERVER_ERROR);
        }

        if (empty($data['method']) || !isset($data['params'])) {
            throw new InvalidJsonRpcContent('The JSON-RPC call must have method and params properties', self::ERROR_INVALID_REQUEST);
        }

        return new MethodCall($data['method'], $data['params'], isset($data['id']) ? $data['id'] : null);
    }

    /**
     * @param  MethodResponse                                   $response
     * @return Response
     * @throws \Seven\RpcBundle\Exception\UnknownMethodResponse
     */

    public function createHttpResponse(MethodResponse $response)
    {
        $data = array(
            'jsonrpc' => '2.0',
        );

        if ($response instanceof MethodReturn) {
            $data['result'] = $response->getReturnValue();
        } elseif ($response instanceof MethodFault) {
            $data['error'] = array('code' => $response->getCode(), 'message' => $response->getMessage());
        } else {
            throw new UnknownMethodResponse("Unknown MethodResponse instance");
        }

        if ($response->getCallId()) {
            $data['id'] = $response->getCallId();
        }

        return new Response(json_encode($data), 200, array('content-type' => 'text/json'));
    }

    /**
     * @param  Response                                         $response
     * @return MethodFault|MethodResponse|MethodReturn
     * @throws \Seven\RpcBundle\Exception\InvalidJsonRpcVersion
     * @throws \Seven\RpcBundle\Exception\InvalidJsonRpcContent
     */

    public function createMethodResponse(Response $response)
    {
        $content = $response->getContent();
        if (empty($content)) {
            throw new InvalidJsonRpcContent('The JSON-RPC response is empty');
        }

        $data = json_decode($content, true);

        if (is_null($data)) {
            throw new InvalidJsonRpcContent('The JSON-RPC response is not valid', self::ERROR_PARSING);
        }

        if (empty($data['jsonrpc']) || version_compare($data['jsonrpc'], '2.0', '<')) {
            throw new InvalidJsonRpcVersion('The JSON-RPC response version is not supported');
        }

        if (isset($data['result'])) {
            return new MethodReturn($data['result']);
        } elseif (isset($data['error'])) {
            if (!isset($data['error']['message'])) {
                throw new InvalidJsonRpcContent('The JSON-RPC fault message is not passed');
            }
            if (!isset($data['error']['code'])) {
                throw new InvalidJsonRpcContent('The JSON-RPC fault code is not passed');
            }

            return new MethodFault(new Fault($data['error']['message'], $data['error']['code']));
        }

        throw new InvalidJsonRpcContent('The JSON-RPC response must have result or error properties');
    }

    /**
     * @param  MethodCall $call
     * @return Request
     */

    public function createHttpRequest(MethodCall $call)
    {
        $data = array(
            'jsonrpc' => '2.0',
            'method'  => $call->getMethodName(),
            'params'  => $call->getParameters()
        );

        if($call->getCallId())
            $data['id'] = $call->getCallId();

        $httpRequest = new Request(array(), array(), array(), array(), array(), array(), json_encode($data));
        $httpRequest->headers->add(array("content-type" => "text/json"));

        return $httpRequest;
    }

}
