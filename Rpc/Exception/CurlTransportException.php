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

namespace Seven\RpcBundle\Rpc\Exception;

use Exception;

/**
 * @author Łukasz Pior <pior.lukasz@gmail.com>
 */
class CurlTransportException extends Exception
{
    public function __construct($error, $code)
    {
        $message = sprintf("There was a transport error with code: '%d' and message: '%s'", $code, $error);

        parent::__construct($message, $code);
    }
}
