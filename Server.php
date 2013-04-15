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
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Server implements Rpc\ServerInterface
{
    protected $impl;
    protected $handlers;

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
     * @throws Exception
     * @return mixed
     */

    public function call($method, $parameters)
    {
        if (strpos($method, '.') !== false) {
            list($handlerName, $method) = explode('.', $method, 2);
            if($this->hasHandler($handlerName))

                return $this->_call(array($this->getHandler($handlerName), $method), $parameters);
        } elseif ($this->hasHandler($method) && is_callable($callback = $this->getHandler($method))) {
            return $this->_call($callback, $parameters);
        }

        throw new Exception("Method '{$method}' are not defined");
    }

    /**
     * @param $callback
     * @param $parameters
     * @return mixed
     */

    protected function _call($callback, $parameters)
    {
        return call_user_func_array($callback, $parameters);
    }

    /**
     * @param $name
     * @param $handler
     * @param  bool      $force
     * @throws Exception
     * @return Server
     */

    public function addHandler($name, $handler, $force = false)
    {
        if(isset($this->handlers[$name]) && !$force)
            throw new Exception("The '{$name}' handler already exists");
        $this->handlers[$name] = $handler;

        return $this;
    }

    /**
     * @param $name
     * @return mixed
     */

    public function hasHandler($name)
    {
        return $this->handlers[$name];
    }

    /**
     * @param $name
     * @return bool
     */

    public function getHandler($name)
    {
        if(!$this->hasHandler($name))

            return false;
        if(is_string($this->handlers[$name]))
            $this->handlers[$name] = new $this->handlers[$name];

        return $this->handlers[$name];
    }

    /**
     * @param $name
     * @return Server
     */

    public function removeHandler($name)
    {
        unset($this->handlers[$name]);

        return $this;
    }

}
