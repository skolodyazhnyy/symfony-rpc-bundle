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

use Seven\RpcBundle\Exception\Fault;
use Seven\RpcBundle\Exception\InvalidXmlRpcContent;
use Seven\RpcBundle\Exception\UnknownMethodResponse;
use Seven\RpcBundle\Exception\XmlRpcSchemaNotFound;
use Symfony\Component\Config\FileLocator;
use Seven\RpcBundle\Rpc\Implementation as BaseImplementation;
use Seven\RpcBundle\Rpc\Method\MethodCall;
use Seven\RpcBundle\Rpc\Method\MethodResponse;
use Seven\RpcBundle\Rpc\Method\MethodFault;
use Seven\RpcBundle\Rpc\Method\MethodReturn;
use Seven\RpcBundle\XmlRpc\ValueType;
use Seven\RpcBundle\XmlRpc\ValueType\AbstractType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Implementation extends BaseImplementation
{
    protected $types;

    /**
     * @param  Request                                         $request
     * @throws \Seven\RpcBundle\Exception\InvalidXmlRpcContent
     * @throws \Seven\RpcBundle\Exception\XmlRpcSchemaNotFound
     * @return MethodCall
     */

    public function createMethodCall(Request $request)
    {
        $document = new \DOMDocument();

        $fileLocator = new FileLocator(dirname(__DIR__) . "/Resources/schema");

        // lookup for schema
        if(!($schema = $fileLocator->locate("xmlrpc.xsd")))
            throw new XmlRpcSchemaNotFound('The XML-RPC methodCall schema not found');

        // validate schema
        $useInternal = libxml_use_internal_errors(true);
        if($content = $request->getContent())
            $document->loadXML($content);
        $valid = $document->schemaValidate($schema);
        libxml_use_internal_errors($useInternal);

        if(!$valid || $document->firstChild->nodeName != 'methodCall')
            throw new InvalidXmlRpcContent('The XML document is not valid XML-RPC methodCall');

        $xpath = new \DOMXPath($document);

        // extract name
        $methodName = (string) $xpath->query("//methodCall/methodName")->item(0)->nodeValue;
        // extract parameters
        $parameters = array();
        $rawParameters = $xpath->query("//methodCall/params/param");
        for ($index = 0; $index < $rawParameters->length; $index++) {
            $item = $rawParameters->item($index);
            $parameters[] = $this->extract($item->firstChild);
        }

        return new MethodCall($methodName, $parameters);
    }

    /**
     * @param  MethodResponse                                   $response
     * @throws \Seven\RpcBundle\Exception\UnknownMethodResponse
     * @return Response
     */

    public function createHttpResponse(MethodResponse $response)
    {
        $document = new \DOMDocument("1.0", "UTF-8");
        $document->appendChild($responseEl = $document->createElement("methodResponse"));

        if ($response instanceof MethodReturn) {
            $paramsEl = $document->createElement("params");
            $paramEl = $document->createElement("param");

            $responseEl->appendChild($paramsEl);
            $paramsEl->appendChild($paramEl);
            $paramEl->appendChild($this->pack($document, $response->getReturnValue(), $response->getReturnType()));
        } elseif ($response instanceof MethodFault) {
            $responseEl->appendChild($faultEl = $document->createElement("fault"));
            $faultEl->appendChild($this->pack($document, array('faultCode' => $response->getCode(), 'faultString' => $response->getMessage()), ValueType::Object));
        } else {
            throw new UnknownMethodResponse("Unknown MethodResponse instance");
        }

        return new Response($document->saveXML(), 200, array('content-type' => 'text/xml'));
    }

    /**
     * @param  Response                                        $response
     * @throws \Seven\RpcBundle\Exception\Fault
     * @throws \Seven\RpcBundle\Exception\XmlRpcSchemaNotFound
     * @throws \Seven\RpcBundle\Exception\InvalidXmlRpcContent
     * @return MethodResponse
     */

    public function createMethodResponse(Response $response)
    {
        $document = new \DOMDocument();

        $fileLocator = new FileLocator(dirname(__DIR__) . "/Resources/schema");

        // lookup for schema
        if(!($schema = $fileLocator->locate("xmlrpc.xsd")))
            throw new XmlRpcSchemaNotFound('The XML-RPC methodResponse schema not found');

        // validate schema
        $useInternal = libxml_use_internal_errors(true);
        if($content =$response->getContent())
            $document->loadXML($content);
        $valid = $document->schemaValidate($schema);
        libxml_use_internal_errors($useInternal);

        if(!$valid || $document->firstChild->nodeName != 'methodResponse')
            throw new InvalidXmlRpcContent('The XML document is not valid XML-RPC methodCall');

        $xpath = new \DOMXPath($document);

        // it's fault
        if ($faultEl = $xpath->query("//methodResponse/fault")->item(0)) {
            $struct = $this->extract($faultEl->firstChild);

            return new MethodFault(new Fault($struct['faultString'], $struct['faultCode']));
        }

        // extract parameters
        $parameters = array();
        $rawParameters = $xpath->query("//methodResponse/params/param");
        for ($index = 0; $index < $rawParameters->length; $index++) {
            $item = $rawParameters->item($index);
            $parameters[] = $this->extract($item->firstChild);
        }

        return new MethodReturn(reset($parameters));
    }

    /**
     * @param  MethodCall                                $call
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * @return Request
     */
    public function createHttpRequest(MethodCall $call, Request $request = null)
    {
        $document = new \DOMDocument("1.0", "UTF-8");
        $document->appendChild($callEl = $document->createElement("methodCall"));

        $callEl->appendChild($methodName = $document->createElement("methodName", $call->getMethodName()));
        $callEl->appendChild($paramsEl = $document->createElement("params"));
        foreach ($call->getParameters() as $parameter) {
            $paramsEl->appendChild($paramEl = $document->createElement("param"));
            $paramEl->appendChild($this->pack($document, $parameter));
        }

        $httpRequest = $request ? new Request($request->query, $request->request, $request->attributes, $request->cookies, $request->files, $request->server, $document->saveXML())
                            : new Request(array(), array(), array(), array(), array(), array(), $document->saveXML());
        $httpRequest->headers->add(array("content-type" => "text/xml"));

        return $httpRequest;
    }

    /**
     * @param  \DOMNode $element
     * @return string
     */

    public function extract(\DOMNode $element)
    {
        if($element->tagName == 'value')
            $element = $element->firstChild;

        switch ($element->tagName) {
            case "array":
                return $this->typeInstance(ValueType::Set)->extract($element);
            case "base64":
                return $this->typeInstance(ValueType::Blob)->extract($element);
            case "boolean":
                return $this->typeInstance(ValueType::Boolean)->extract($element);
            case "dateTime.iso8601":
                return $this->typeInstance(ValueType::Date)->extract($element);
            case "double":
                return $this->typeInstance(ValueType::Double)->extract($element);
            case "string":
                return $this->typeInstance(ValueType::String)->extract($element);
            case "i4":
            case "int":
                return static::typeInstance(ValueType::Integer)->extract($element);
            case "struct":
                return static::typeInstance(ValueType::Object)->extract($element);
        }

        return null;
    }

    /**
     * @param \DOMDocument $document
     * @param $value
     * @param $type
     * @return \DOMElement
     */

    public function pack(\DOMDocument $document, $value, $type = null)
    {
        return $this->typeInstance($type ?: $this->detectType($value))
            ->pack($document, $value);
    }

    /**
     * @param  null                   $type
     * @return ValueType\AbstractType
     */

    protected function typeInstance($type)
    {
        if(empty($this->types[$type]))
            $this->types[$type] = $this->createType($type);

        return $this->types[$type] ?: $this->typeInstance(ValueType::String);
    }

    /**
     * @param $type
     * @return AbstractType
     */

    protected function createType($type)
    {
        switch ($type) {
            case ValueType::Null:     return new ValueType\NullType($this);
            case ValueType::String:   return new ValueType\StringType($this);
            case ValueType::Integer:  return new ValueType\IntegerType($this);
            case ValueType::Boolean:  return new ValueType\BooleanType($this);
            case ValueType::Double:   return new ValueType\DoubleType($this);
            case ValueType::Date:     return new ValueType\DateType($this);
            case ValueType::Blob:     return new ValueType\BlobType($this);
            case ValueType::Set:      return new ValueType\ArrayType($this);
            case ValueType::Object:   return new ValueType\ObjectType($this);
        }

        return null;
    }

    /**
     * @param $value
     * @return mixed
     */

    public function detectType($value)
    {
        if ($value === null) {
            return ValueType::Null;
        } elseif (is_float($value)) {
            return ValueType::Double;
        } elseif (is_numeric($value)) {
            return ValueType::Integer;
        } elseif (is_bool($value)) {
            return ValueType::Boolean;
        } elseif ($value instanceof \DateTime) {
            return ValueType::Date;
        } elseif (is_object($value)) {
            return ValueType::Object;
        } elseif (is_array($value)) {
            return $this->isAssociative($value) ? ValueType::Object : ValueType::Set;
        }

        return ValueType::String;
    }

    /**
     * @param $value
     * @return bool
     */

    protected function isAssociative($value)
    {
        foreach((array) $value as $key => $value)
            if(!is_numeric($key))

                return true;
        return false;
    }

}
