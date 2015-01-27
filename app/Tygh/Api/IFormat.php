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

namespace Tygh\Api;

/**
 * Data format interface
 */
interface IFormat
{
    /**
     * Must mime type(s) that can be encoded/decoded by this class
     *
     * @return array/string Mime type(s)
     */
    public function getMimeTypes();

    /**
     * Encodes $data in the format
     *
     * @param  array  $data resulting data that needs to be encoded in the given format
     * @return string Encoded string
     */
    public function encode($data);

    /**
    * Decodes $data from the format
    *
    * @param string $data data sent from client to the api in the given format
    * @return array Array of the parsed data
    */
    public function decode($data);
}
