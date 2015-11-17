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

abstract class MethodResponse
{
    /**
     * @var int|string|null
     */
    protected $callId = null;

    /**
     * Constructor.
     *
     * @param int|string|null $callId
     */
    public function __construct($callId = null)
    {
        $this->callId = $callId;
    }

    /**
     * Get call id.
     *
     * @return int|string|null
     */
    public function getCallId()
    {
        return $this->callId;
    }
}
