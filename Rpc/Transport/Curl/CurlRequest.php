<?php

/**
 * @author Łukasz Pior <pior.lukasz@gmail.com>
 */

namespace Seven\RpcBundle\Rpc\Transport\Curl;

/**
 * Simple wrapper around curl functions that allows us to mocks curl requests in tests
 */
class CurlRequest
{
    private $handle = null;

    public function __construct()
    {
        $this->handle = curl_init();
    }

    public function setOptions(array $options)
    {
        curl_setopt_array($this->handle, $options);
    }

    public function setOption($name, $value)
    {
        curl_setopt($this->handle, $name, $value);
    }

    public function execute()
    {
        return curl_exec($this->handle);
    }

    public function getInfo($name)
    {
        return curl_getinfo($this->handle, $name);
    }

    public function close()
    {
        curl_close($this->handle);
    }

    public function getErrorNumber()
    {
        return curl_errno($this->handle);
    }

    public function getErrorMessage()
    {
        return curl_error($this->handle);
    }
}