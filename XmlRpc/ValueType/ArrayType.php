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

class ArrayType extends AbstractType
{
    /**
     * @param \DOMDocument $document
     * @param $value
     * @return \DOMElement
     */

    public function pack(\DOMDocument $document, $value)
    {
        $data = $document->createElement("data");
        foreach ((array) $value as $item) {
            $data->appendChild($this->impl->pack($document, $item));
        }

        return $this->wrap($this->wrap($data, "array"), "value");
    }

    /**
     * @param  \DOMElement $element
     * @return array
     */

    public function extract(\DOMElement $element)
    {
        $data = array();
        $items = $this->unwrap($element, 'array')->childNodes;
        if (!$items) {
            return array();
        }

        for ($index = 0; $index < $items->length; $index++) {
            if (($item = $items->item($index)) instanceof \DOMElement) {
                $data[] = $this->impl->extract($item);
            }
        }

        return $data;
    }

}
