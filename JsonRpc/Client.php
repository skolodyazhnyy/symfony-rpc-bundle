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

namespace Seven\RpcBundle\JsonRpc;
use Seven\RpcBundle\Rpc\Client as BaseClient;
use Seven\RpcBundle\Rpc\Transport\TransportInterface;

class Client extends BaseClient
{

    public function __construct($webServiceUrl, TransportInterface $transport = null)
    {
        parent::__construct($webServiceUrl, new Implementation(), $transport);
    }

}
