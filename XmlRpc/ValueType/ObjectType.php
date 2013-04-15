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

class ObjectType extends AbstractType
{
    /**
     * @param \DOMDocument $document
     * @param $value
     * @return \DOMElement
     */

    public function pack(\DOMDocument $document, $value)
    {
        $members = $document->createElement("struct");
        foreach ((array) $value as $memberKey => $memberValue) {
            $memberElement = $document->createElement("member");
            $memberElement->appendChild(new \DOMElement("name", $this->normalizeKey($memberKey)));
            $memberElement->appendChild($this->impl->pack($document, $memberValue));
            $members->appendChild($memberElement);
        }

        return $this->wrap($members, 'value');
    }

    /**
     * @param $key
     * @return mixed|string
     */

    protected function normalizeKey($key)
    {
        $key = (string) $key;
        if($key == "") return "empty";
        if (is_numeric($key{0})) {
            $key = "i{$key}";
        }

        return preg_replace('/[^\.A-Za-z_0-9]/', '_', $key);
    }

    /**
     * @param  \DOMElement $element
     * @return array
     */

    public function extract(\DOMElement $element)
    {
        $data = array();
        $items = $element->childNodes;
        for ($index = 0; $index < $items->length; $index++) {
            $item = $items->item($index);
            if ($item instanceof \DOMElement && $item->tagName == 'member') {
                if($item->childNodes->length   != 2      ||
                   $item->firstChild->nodeName != 'name' ||
                   $item->lastChild->nodeName  != 'value')
                        continue;

                $data[$item->firstChild->nodeValue] = $this->impl->extract($item->lastChild->firstChild);
            }
        }

        return $data;
    }
}
