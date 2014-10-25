<?php

/**
 * @author Łukasz Pior <pior.lukasz@gmail.com>
 */

namespace Seven\RpcBundle\Rpc\Exception;

use Exception;

class CurlTransportException extends Exception
{
    public function __construct($error, $code)
    {
        $message = sprintf("There was a transport error with code: '%d' and message: '%s'", $code, $error);

        parent::__construct($message, $code);
    }
}