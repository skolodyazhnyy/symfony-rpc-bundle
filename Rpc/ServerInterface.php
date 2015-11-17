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

namespace Seven\RpcBundle\Rpc;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface ServerInterface
{
    /**
     * Handle request.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function handle(Request $request);

    /**
     * Call the $method with $parameters.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     *
     * @throws MethodNotExists If method is not defined.
     */
    public function call($methodName, array $parameters);
}
