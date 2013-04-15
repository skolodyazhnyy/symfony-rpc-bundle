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

namespace Seven\RpcBundle;
use Seven\RpcBundle\Rpc\MethodReturn;
use Seven\RpcBundle\Rpc\MethodFault;
use Seven\RpcBundle\Rpc\MethodCall;
use Seven\RpcBundle\Rpc\MethodResponse;
use Seven\RpcBundle\Rpc\Implementation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Server implements Rpc\ServerInterface
{
    protected $impl;

    /**
     * @param Rpc\Implementation $impl
     */

    public function __construct(Implementation $impl)
    {
        $this->impl = $impl;
    }

    /**
     * @param  Request  $request
     * @return Response
     */

    public function handle(Request $request)
    {
        $methodCall = $this->impl->createMethodCall($request);
        $methodResponse = $this->_handle($methodCall);

        return $this->impl->createHttpResponse($methodResponse);
    }

    /**
     * @param  MethodCall     $methodCall
     * @return MethodResponse
     */

    protected function _handle(MethodCall $methodCall)
    {
        try {
            $response = $this->call($methodCall->getMethodName(), $methodCall->getParameters());

            if(!($response instanceof MethodResponse))
                $response = new MethodReturn($response);
        } catch (\Exception $e) {
            $response = new MethodFault($e);
        }

        return $response;
    }

    /**
     * @param $method
     * @param $parameters
     * @return mixed
     */

    public function call($method, $parameters)
    {
        return 'some value';
    }

}
