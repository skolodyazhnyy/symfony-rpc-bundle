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

namespace Seven\RpcBundle\XmlRpc\ValueType;

use Seven\RpcBundle\XmlRpc\Implementation;
use XmlRpc\ServerBundle\XmlRpc\ValueType;

abstract class AbstractType implements TypeInterface
{
    protected $impl;

    public function __construct(Implementation $impl)
    {
        $this->impl = $impl;
    }

    /**
     * @param \DOMElement $element
     * @param $wrapName
     * @return \DOMElement
     */

    public function wrap(\DOMElement $element, $wrapName)
    {
        $wrapper = $element->ownerDocument->createElement($wrapName);
        $wrapper->appendChild($element);

        return $wrapper;
    }

    /**
     * @param  \DOMElement $element
     * @param  null        $wrapName
     * @return \DOMElement
     * @throws \Exception
     */

    public function unwrap(\DOMElement $element, $wrapName = null)
    {
        if ($wrapName != null && $element->tagName != $wrapName) {
                throw new \Exception("Element must be wrapped into '$wrapName' instead of '{$element->tagName}'");
        }

        return $element->firstChild;
    }

    // @codeCoverageIgnoreStart
    abstract public function pack(\DOMDocument $document, $value);

    abstract public function extract(\DOMElement $element);
    // @codeCoverageIgnoreEnd
}
