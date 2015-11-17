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

use Seven\RpcBundle\Rpc\Method\MethodCall;
use Seven\RpcBundle\Rpc\Method\MethodResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// @codeCoverageIgnoreStart
abstract class Implementation
{
    /**
     * Create method call from request.
     *
     * @param Request $request
     *
     * @return mixed
     */
    abstract public function createMethodCall(Request $request);

    /**
     * Create method response.
     *
     * @param Response $response
     *
     * @return MethodResponse
     */
    abstract public function createMethodResponse(Response $response);

    /**
     * Create HTTP Response.
     *
     * @param MethodResponse $response
     *
     * @return Response
     */
    abstract public function createHttpResponse(MethodResponse $response);

    /**
     * Create HTTP request.
     *
     * @param MethodCall $call
     *
     * @return Request
     */
    abstract public function createHttpRequest(MethodCall $call);
}
// @codeCoverageIgnoreEnd

