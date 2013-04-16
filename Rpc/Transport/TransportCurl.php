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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TransportCurl implements TransportInterface {

    /**
     * @param Request $request
     * @return Response
     */

    public function makeRequest(Request $request) {
        $options = array(
            CURLOPT_POST                => 1,
            CURLOPT_HEADER              => 0,
            CURLOPT_URL                 => $request->getBaseUrl() . '/' . $request->getRequestUri(),
            CURLOPT_FRESH_CONNECT       => 1,
            CURLOPT_RETURNTRANSFER      => 1,
            CURLOPT_FORBID_REUSE        => 1,
            CURLOPT_TIMEOUT             => 4,
            CURLOPT_POSTFIELDS          => $request->getContent()
        );

        $curl = curl_init();
        curl_setopt_array($curl, $options);
        if(!$responseBody = curl_exec($curl)) {
            $code = curl_errno($curl);
            return new Response("", 500);
        }
        curl_close($curl);
        return new Response($responseBody);
    }
}