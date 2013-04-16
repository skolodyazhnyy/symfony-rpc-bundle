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

namespace Seven\RpcBundle\XmlRpc;
use Seven\RpcBundle\Rpc\Server as BaseServer;

class Server extends BaseServer
{

    public function __construct()
    {
        parent::__construct(new Implementation());
    }

}
