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

namespace Seven\RpcBundle\Rpc\Method;

class MethodCall
{
    /**
     * @var string
     */
    protected $methodName;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @var int|string|null
     */
    protected $callId;

    /**
     * Constructor.
     *
     * @param string          $methodName
     * @param array           $parameters
     * @param int|string|null $callId
     */
    public function __construct($methodName, $parameters = array(), $callId = null)
    {
        $this->methodName = $methodName;
        $this->parameters = $parameters;
        $this->callId     = $callId;
    }

    /**
     * Get method name.
     *
     * @return string
     */
    public function getMethodName()
    {
        return $this->methodName;
    }

    /**
     * Get array of parameters.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Get call id.
     *
     * @return string|int|null
     */
    public function getCallId()
    {
        return $this->callId;
    }
}
