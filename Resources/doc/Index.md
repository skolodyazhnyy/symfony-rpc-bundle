Symfony RPC Bundle
==================

This is a lightweight implementation of Remote Procedure Call (RPC) library for Symfony.
It provide an easy way to create a XML-RPC web service within standard Symfony controller.

Basic Server usage
------------------

First, you have to create a regular Symfony controller and define an action which will be used to
handle RPC requests. You need to assign an URL to this action using standard Symfony routing
configuration. This URL will be used as your web service address.

Every RPC call will be processed by callback linked to certain method name. You can make this
links using `addHandler` method of Server class. The `addHandler` method takes two parameters:
RPC method name and callback. You can group your callbacks into a class and link it to some 
namespace within `addHandler` method by passing namespace as a first parameter and class name
as a second parameter. To call a method in the group you need to specify it namespace and name
separated by dot, like this: `[namespace].[classMethodName]`.

The RPC Server class allow you to handle HTTP request and create valid HTTP response. You can use
different RPC implementations like XML-RPC, JSON-RPC or your own. There is an example below,
which show how you can handle XML-RPC calls.

```php
namespace Sample\WebserviceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Seven\RpcBundle\XmlRpc\Server;

class CalcHandler {

    public function add($a, $b) { return $a + $b; }

    public function sub($a, $b) { return $a - $b; }

    public function div($a, $b) { return $a / $b; }

}

class WebServiceController extends Controller
{
    public function handleAction()
    {
        // Create XML-RPC Server
        $server = new Server();

        // Add handlers
        $server->addHandler('help', function() { return "Use methods calc.add, calc.sub and calc.div."; });
        $server->addHandler('calc', 'CalcHandler');

        // Handler request and return response
        return $server->handle($this->getRequest());
    }
}
```

Basic Client usage
------------------

```php
$client = new Seven\RpcBundle\XmlRpc\Client("http://xmlrpcservice/endpoint");

echo $client->call('calc.add', array(1, 2)); // echo 3
echo $client->call('calc.sub', array(2, 3)); // echo -1
```
