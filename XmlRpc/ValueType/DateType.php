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

class DateType extends AbstractType
{
    /**
     * @param \DOMDocument $document
     * @param $value
     * @return \DOMElement
     */

    public function pack(\DOMDocument $document, $value)
    {
        $date = ($value instanceof \DateTime) ? $value : new \DateTime((string) $value);

        return $this->wrap($document->createElement("dateTime.iso8601", $date->format("Ymd\\TH:i:s")), "value");
    }

    /**
     * @param  \DOMElement $element
     * @return \DateTime
     */

    public function extract(\DOMElement $element)
    {
        return new \DateTime((string) $element->nodeValue);
    }

}
