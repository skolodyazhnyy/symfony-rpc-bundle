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

use Exception;

class MethodFault extends MethodResponse
{
    /**
     * @var Exception
     */
    protected $exception;

    /**
     * Constructor.
     *
     * @param \Exception      $exception
     * @param int|string|null $callId
     */
    public function __construct(Exception $exception, $callId = null)
    {
        $this->exception = $exception;
        parent::__construct($callId);
    }

    /**
     * Get message.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->getException()->getMessage();
    }

    /**
     * Get code.
     *
     * @return string
     */
    public function getCode()
    {
        return $this->getException()->getCode();
    }

    /**
     * Get exception.
     *
     * @return Exception
     */
    public function getException()
    {
        return $this->exception;
    }
}
