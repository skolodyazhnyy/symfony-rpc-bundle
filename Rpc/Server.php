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
use Seven\RpcBundle\Exception\MethodNotExists;
use Seven\RpcBundle\Rpc\Method\MethodReturn;
use Seven\RpcBundle\Rpc\Method\MethodFault;
use Seven\RpcBundle\Rpc\Method\MethodCall;
use Seven\RpcBundle\Rpc\Method\MethodResponse;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Server implements ServerInterface
{
    protected $impl;
    protected $handlers;

    /**
     * @param Implementation $impl
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
        try {
            $methodCall = $this->impl->createMethodCall($request);
            $methodResponse = $this->_handle($methodCall);
        } catch (\Exception $e) {
            $methodResponse = new MethodFault($e);
        }

        return $this->impl->createHttpResponse($methodResponse);
    }

    /**
     * @param  MethodCall     $methodCall
     * @return MethodResponse
     */

    protected function _handle(MethodCall $methodCall)
    {
        $response = $this->call($methodCall->getMethodName(), $methodCall->getParameters());

        if(!($response instanceof MethodResponse))
            $response = new MethodReturn($response);

        return $response;
    }

    /**
     * @param $method
     * @param $parameters
     * @throws MethodNotExists
     * @return mixed
     */

    public function call($method, $parameters)
    {
        if ($this->hasHandler($method) && is_callable($callback = $this->getHandler($method))) {
            return $this->_call($callback, $parameters);
        } elseif (strpos($method, '.') !== false) {
            list($handlerName, $methodName) = explode('.', $method, 2);
            if ($this->hasHandler($handlerName)) {
                if (is_callable($callback = array($this->getHandler($handlerName), $methodName))) {
                    return $this->_call($callback, $parameters);
                }
            }
        }
        throw new MethodNotExists("Method '{$method}' are not defined");
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
        return isset($this->handlers[$name]);
    }

    /**
     * @param $name
     * @return bool
     */

    public function getHandler($name)
    {
        if (!$this->hasHandler($name)) {
            return false;
        }
        if (is_string($this->handlers[$name])) {
            $this->handlers[$name] = new $this->handlers[$name];
        }

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
