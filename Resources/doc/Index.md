Symfony RPC Bundle
==================

Intro
-------------

This is a lightweight implementation of Remote Procedure Call (RPC) library for Symfony.
It provide an easy way to create a XML-RPC web service within standard Symfony controller.

Basic Server usage
-------------

First, you have to create a regular Symfony controller and define an action which will be used to
handle RPC requests. You need to assign an URL to this action using standard Symfony routing
configuration. This URL will be used as your web service address.

Every RPC call will be processed by callback linked to certain methodName. You can make this
links using `addHandler` method of Server class. The `addHandler` method takes two parameters:
RPC method name and the callback. You can group your callbacks into the class and link this class
to some namespace within `addHandler` method by passing namespace as a first parameter and class
name as a second parameter. To call a method in the group you need to specify it name separated by
 dot, like when you call an method of the object in Java: `[namespace].[classMethodName]`.

The RPC Server class allow you to handle HTTP request and create valid HTTP response. You can use
different RPC implementations like XML-RPC, JSON-RPC or your own. There is an example below,
which show how you can handle XML-RPC calls.

<pre><code>
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
</code></pre>