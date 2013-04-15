<?php

/*
 * This file is part of the Symfony bundle Seven/Rpc.
 *
 * (c) Sergey Kolodyazhnyy <sergey.kolodyazhnyy@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Seven\RpcBundle\Tests\XmlRpc\ValueType;

class XmlAssertHelper
{
    public static function xml2array(\DOMElement $element)
    {
        $value = array();
        for ($index = 0; $index < $element->childNodes->length; $index++) {
            /** @var $node \DOMElement */
            $node = $element->childNodes->item($index);
            if($node instanceof \DOMText)
                $value = $node->nodeValue;
            if($node instanceof \DOMElement)
                $value[$node->tagName] = static::xml2array($node);
        }

        return $value;
    }

}
