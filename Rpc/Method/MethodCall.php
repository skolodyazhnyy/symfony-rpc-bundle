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
    protected $methodName;
    protected $parameters;

    /**
     * @param $methodName
     * @param  array                                  $parameters
     * @return \Seven\RpcBundle\Rpc\Method\MethodCall
     */

    public function __construct($methodName, $parameters = array())
    {
        $this->methodName = $methodName;
        $this->parameters = $parameters;
    }

    /**
     * @return string
     */

    public function getMethodName()
    {
        return $this->methodName;
    }

    /**
     * @return array
     */

    public function getParameters()
    {
        return $this->parameters;
    }

}
