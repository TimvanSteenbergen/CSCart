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

$d = SEO_DELIMITER;

$umlauts = array(
    // Replace umlauts with their basic latin representation
    "\xc3\xa5" => 'aa',
    "\xc3\xa6" => 'ae',
    "\xc3\xb6" => 'oe',
    "\xc3\x85" => 'aa',
    "\xc3\x86" => 'ae',
    "\xc3\x96" => 'oe',

    "\xc5\xA5" => 't',
    "\xc4\xBE" => 'l',
    "\xc5\xA4" => 'T',
    "\xc4\xBD" => 'L',
    "\xc4\x84" => 'A',
    "\xc4\x85" => 'a',
    "\xc3\xa1" => 'a',
    "\xc3\x81" => 'A',
    "\xc3\xa0" => 'a',
    "\xc3\x80" => 'A',
    "\xc3\xa2" => 'a',
    "\xc3\x82" => 'A',
    "\xc3\xa3" => 'a',
    "\xc3\x83" => 'A',
    "\xc2\xaa" => 'a',
    "\xc4\x8c" => 'C',
    "\xc4\x8d" => 'c',
    "\xc3\xa7" => 'c',
    "\xc3\x87" => 'C',
    "\xc3\xa9" => 'e',
    "\xc3\x89" => 'E',
    "\xc3\xa8" => 'e',
    "\xc3\x88" => 'E',
    "\xc3\xaa" => 'e',
    "\xc3\x8a" => 'E',
    "\xc3\xab" => 'e',
    "\xc3\x8b" => 'E',
    "\xc4\x98" => 'E',
    "\xc4\x99" => 'e',
    "\xc4\x9a" => 'E',
    "\xc4\x9b" => 'e',
    "\xc4\x8f" => 'd',
    "\xc3\xad" => 'i',
    "\xc3\x8d" => 'I',
    "\xc3\xac" => 'i',
    "\xc3\x8c" => 'I',
    "\xc3\xae" => 'i',
    "\xc3\x8e" => 'I',
    "\xc3\xaf" => 'i',
    "\xc3\x8f" => 'I',
    "\xc4\xb9" => 'L',
    "\xc4\xba" => 'l',
    "\xc4\xbe" => 'l',
    "\xc5\x87" => 'N',
    "\xc5\x88" => 'n',
    "\xc3\xb1" => 'n',
    "\xc3\x91" => 'N',
    "\xc3\xb3" => 'o',
    "\xc3\x93" => 'O',
    "\xc3\xb2" => 'o',
    "\xc3\x92" => 'O',
    "\xc3\xb4" => 'o',
    "\xc3\x94" => 'O',
    "\xc3\xb5" => 'o',
    "\xc3\x95" => 'O',
    "\xd4\xa5" => 'o',
    "\xc3\x98" => 'O',
    "\xc2\xba" => 'o',
    "\xc3\xb0" => 'o',
    "\xc5\x94" => 'R',
    "\xc5\x95" => 'r',
    "\xc5\x98" => 'R',
    "\xc5\x99" => 'r',
    "\xc5\xa0" => 'S',
    "\xc5\xa1" => 's',
    "\xc5\xa5" => 't',
    "\xc3\xba" => 'u',
    "\xc3\x9a" => 'U',
    "\xc3\xb9" => 'u',
    "\xc3\x99" => 'U',
    "\xc3\xbb" => 'u',
    "\xc3\x9b" => 'U',
    "\xc3\xbc" => 'u',
    "\xc3\x9c" => 'U',
    "\xc5\xae" => 'U',
    "\xc5\xaf" => 'u',
    "\xc3\xbd" => 'y',
    "\xc3\x9d" => 'Y',
    "\xc3\xbf" => 'y',
    "\xc3\xa4" => 'a',
    "\xc3\x84" => 'A',
    "\xc3\x9f" => 's',
    "\xc5\xbd" => 'Z',
    "\xc5\xbe" => 'z',
);

return array_merge($schema, $umlauts);
