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

namespace Tygh\Api\Formats;

use Tygh\Api\IFormat;

/**
 * Text format encoder/decoder
 */
class Text implements IFormat
{
    protected $mime_types = array(
        'text/plain'
    );

    public function getMimeTypes()
    {
        return $this->mime_types;
    }

    public function encode($data)
    {
        return $data;
    }

    public function decode($data)
    {
        parse_str($data, $result);

        return array($result, '');
    }
}
