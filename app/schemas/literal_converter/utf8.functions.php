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

/**
 * Decode encoded string
 * Example: $result = fn_simple_decode_str('uftu'); // returns "test"
 *
 * @param type $str
 * @return type
 */
function fn_simple_decode_str($str)
{
    $decoded_str = '';
    for ($i = 0; $i < fn_strlen($str); $i++) {
        $chr = ord($str[$i]);
        $decoded_str .= chr(--$chr);
    }

    return $decoded_str;
}

/**
 * Encode plain text string
 * Example: $result = fn_simple_encode_str('test'); // returns "uftu"
 *
 * @param type $str
 * @return type
 */
function fn_simple_encode_str($str)
{
    $encoded_str = '';
    for ($i = 0; $i < fn_strlen($str); $i++) {
        $chr = ord($str[$i]);
        $encoded_str .= chr(++$chr);
    }

    return $encoded_str;
}
