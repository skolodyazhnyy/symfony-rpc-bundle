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

use Symfony\Component\Config\FileLocator;
use Seven\RpcBundle\Rpc\Implementation as BaseImplementation;
use Seven\RpcBundle\Rpc\MethodCall;
use Seven\RpcBundle\Rpc\MethodResponse;
use Seven\RpcBundle\Rpc\MethodFault;
use Seven\RpcBundle\Rpc\MethodReturn;
use Seven\RpcBundle\XmlRpc\ValueType;
use Seven\RpcBundle\XmlRpc\ValueType\AbstractType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Implementation extends BaseImplementation
{
    protected $types;

    /**
     * @param  Request    $request
     * @return MethodCall
     * @throws \Exception
     */

    public function createMethodCall(Request $request)
    {
        $document = new \DOMDocument();
        $document->loadXML($request->getContent());

        $fileLocator = new FileLocator(dirname(__DIR__) . "/Resources/schema");

        // validate document by xsd schema
        if(!($schema = $fileLocator->locate("xmlrpc.xsd")))
            throw new \Exception('The XML-RPC methodCall schema not found');
        if(!$document->schemaValidate($schema) && $document->firstChild->nodeName == 'methodCall')
            throw new \Exception('The XML document is not valid XML-RPC methodCall');

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
     * @param  MethodResponse $response
     * @throws \Exception
     * @return Response
     */

    public function createHttpResponse(MethodResponse $response)
    {
        if ($response instanceof MethodReturn) {
            $value = $response->getReturnValue();
            $type = $response->getReturnType();
        } elseif ($response instanceof MethodFault) {
            $value = array('message' => $response->getMessage(), 'code' => $response->getCode());
            $type = null;
        } else {
            throw new \Exception("Unknown MethodResponse instance");
        }

        $document = new \DOMDocument(null, "UTF-8");
        $responseEl = $document->createElement("methodResponse");
        $paramsEl = $document->createElement("params");
        $paramEl = $document->createElement("param");

        $document->appendChild($responseEl);
        $responseEl->appendChild($paramsEl);
        $paramsEl->appendChild($paramEl);
        $paramEl->appendChild($this->pack($document, $value, $type));

        return new Response($document->saveXML(), 200, array('content-type' => 'text/xml'));
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

        return $this->types[$type] ?: $this->typeInstance(Value::String);
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
        if($value === null)

            return ValueType::Null;
        if(is_float($value))

            return ValueType::Double;
        if(is_numeric($value))

            return ValueType::Integer;
        if(is_bool($value))

            return ValueType::Boolean;
        if($value instanceof \DateTime)

            return ValueType::Date;
        if(is_object($value))

            return ValueType::Object;
        if(is_array($value))

            return $this->isAssociative($value) ? ValueType::Object : ValueType::Set;

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
