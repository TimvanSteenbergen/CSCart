<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/

namespace Tygh;

/**
 * Extension for SimpleXMLElement
 */
class ExSimpleXmlElement extends \SimpleXMLElement
{
    /**
     * Returns count of child elements
     *
     * @return int
     */
    public function exCount()
    {
        return count($this->children());
    }

    /**
     * Add CDATA text in a node
     * @param string $cdata_text The CDATA value to add
     */
    private function addCData($cdata_text)
    {
        $node= dom_import_simplexml($this);
        $no = $node->ownerDocument;
        $node->appendChild($no->createCDATASection($cdata_text));
    }

    /**
     * Create a child with CDATA value
     * @param  string             $name       The name of the child element to add.
     * @param  string             $cdata_text The CDATA value of the child element.
     * @return ExSimpleXMLElement
     */
    public function addChildCData($name, $cdata_text)
    {
        $child = $this->addChild($name);
        $child->addCData($cdata_text);

        return $child;
    }

    /**
     * Add SimpleXMLElement code into a SimpleXMLElement
     * @param  ExSimpleXMLElement $append
     * @return ExSimpleXMLElement
     */
    public function appendXML($append)
    {
        if ($append) {
            if (strlen(trim((string) $append))==0) {
                $xml = $this->addChild($append->getName());
                foreach ($append->children() as $child) {
                    $xml->appendXML($child);
                }
            } else {
                $xml = $this->addChild($append->getName(), (string) $append);
            }

            foreach ($append->attributes() as $n => $v) {
                @$xml->addAttribute($n, $v);
            }

            return $xml;
        }
    }
}
