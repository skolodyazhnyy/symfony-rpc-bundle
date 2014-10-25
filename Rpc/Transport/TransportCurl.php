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

namespace Seven\RpcBundle\Rpc\Transport;

use Seven\RpcBundle\Rpc\Exception\CurlTransportException;
use Seven\RpcBundle\Rpc\Transport\Curl\CurlRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TransportCurl implements TransportInterface
{
    /**
     * Custom options for curl.
     * @var array
     */
    protected $options;

    /**
     * Constructor.
     * @param array $options Custom options for curl.
     */
    public function __construct($options = array())
    {
        $this->options = $options;
    }

    /**
     * @param  Request $request
     * @throws CurlTransportException
     * @return Response
     */

    public function makeRequest(Request $request)
    {
        $curlRequest = $this->getCurlRequest($request);

        $responseBody = $curlRequest->execute();
        if (!$responseBody) {
            $code = $curlRequest->getErrorNumber();
            $error = $curlRequest->getErrorMessage();

            throw new CurlTransportException($error, $code);
        }

        return new Response($responseBody);
    }

    /**
     * @param Request $request
     * @return Curl\CurlRequest
     */
    public function getCurlRequest(Request $request)
    {
        $options = $this->prepareOptions($request);

        $curlRequest = new CurlRequest();
        $curlRequest->setOptions($options);

        return $curlRequest;
    }

    /**
     * @param Request $request
     * @return array
     */
    private function prepareOptions(Request $request)
    {
        $options = array(
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_URL => $request->getSchemeAndHttpHost() . $request->getRequestUri(),
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_POSTFIELDS => $request->getContent(),
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false
        );

        foreach ($this->options as $key => $value) {
            $options[$key] = $value;
        }

        return $options;
    }
}
