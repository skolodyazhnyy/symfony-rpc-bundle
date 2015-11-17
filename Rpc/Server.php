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
use Seven\RpcBundle\Exception\MethodNotExists;
use Seven\RpcBundle\Rpc\Method\MethodCall;
use Seven\RpcBundle\Rpc\Method\MethodFault;
use Seven\RpcBundle\Rpc\Method\MethodResponse;
use Seven\RpcBundle\Rpc\Method\MethodReturn;
use Symfony\Component\HttpFoundation\Request;

class Server implements ServerInterface
{
    /**
     * @var Implementation
     */
    protected $impl;

    /**
     * @var array
     */
    protected $handlers;

    /**
     * Constructor.
     *
     * @param Implementation $impl
     */
    public function __construct(Implementation $impl)
    {
        $this->impl = $impl;
        $this->handlers = array();
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Request $request)
    {
        try {
            $methodCall = $this->impl->createMethodCall($request);
            $methodResponse = $this->handleMethodCall($methodCall);
        } catch (Exception $e) {
            $methodResponse = new MethodFault($e);
        }

        return $this->impl->createHttpResponse($methodResponse);
    }

    /**
     * Handle MethodCall.
     *
     * @param MethodCall $methodCall
     *
     * @return MethodReturn
     */
    protected function handleMethodCall(MethodCall $methodCall)
    {
        $response = $this->call($methodCall->getMethodName(), $methodCall->getParameters());

        if (!($response instanceof MethodResponse)) {
            $response = new MethodReturn($response);
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function call($method, array $parameters)
    {
        if ($this->hasHandler($method) && is_callable($callback = $this->getHandler($method))) {
            return $this->callCallback($callback, $parameters);
        }

        if (strpos($method, '.') !== false) {
            list($handlerName, $methodName) = explode('.', $method, 2);
            if ($this->hasHandler($handlerName)) {
                if (is_callable($callback = array($this->getHandler($handlerName), $methodName))) {
                    return $this->callCallback($callback, $parameters);
                }
            }
        }

        throw new MethodNotExists("Method '{$method}' is not defined");
    }

    /**
     * Call the $callback with $parameters.
     *
     * @param callable $callback
     * @param array    $parameters
     *
     * @return mixed
     */
    protected function callCallback($callback, array $parameters)
    {
        return call_user_func_array($callback, $parameters);
    }

    /**
     * Add handler.
     *
     * @param string $name
     * @param mixed  $handler
     * @param bool   $force
     *
     * @return Server
     *
     * @throws Exception if the handler's name already exists and $force is set to false.
     */
    public function addHandler($name, $handler, $force = false)
    {
        if (isset($this->handlers[$name]) && !$force) {
            throw new Exception("The '{$name}' handler already exists");
        }
        $this->handlers[$name] = $handler;

        return $this;
    }

    /**
     * Returns true if handler exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasHandler($name)
    {
        return isset($this->handlers[$name]);
    }

    /**
     * Get handler.
     *
     * @param string $name
     *
     * @return bool
     */
    public function getHandler($name)
    {
        if (!$this->hasHandler($name)) {
            return false;
        }
        if (is_string($this->handlers[$name])) {
            $this->handlers[$name] = new $this->handlers[$name]();
        }

        return $this->handlers[$name];
    }

    /**
     * Remove handler.
     *
     * @param string $name
     *
     * @return Server
     */
    public function removeHandler($name)
    {
        unset($this->handlers[$name]);

        return $this;
    }
}
