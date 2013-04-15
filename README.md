SevenRpcBundle
=====================

The `SevenRpcBundle` extends the default Symfony2 with a XML-RPC Server implementation.
This bundle provide you an easy way to create XML RPC webservice. 


Basic usage
---------------------

class XmlRpcController extends Controller {

	public function handleAction() {
		$server = new Server(new Seven\RpcBundle\XmlRpc\Implementation());
		$server->addHandler('webservice', 'MyBundle\Model\WebserviceHandler');
	
		return $server->handle($this->getRequest());
	}

}