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

class MethodReturn extends MethodResponse
{
    protected $returnValue;
    protected $returnType = null;

    /**
     * Constructor.
     *
     * @param string|null     $returnValue
     * @param string|null     $returnType
     * @param int|string|null $callId
     */
    public function __construct($returnValue = null, $returnType = null, $callId = null)
    {
        $this->returnType = $returnType;
        $this->returnValue = $returnValue;
        parent::__construct($callId);
    }

    /**
     * Get return value.
     *
     * @return string|null
     */
    public function getReturnValue()
    {
        return $this->returnValue;
    }

    /**
     * Get return type.
     *
     * @return string|null
     */
    public function getReturnType()
    {
        return $this->returnType;
    }
}
