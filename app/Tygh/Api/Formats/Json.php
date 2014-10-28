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
 * JSON format encoder/decoder
 */
class Json implements IFormat
{
    protected $mime_types = array(
        'application/json',
        'application/javascript'
    );

    public function getMimeTypes()
    {
        return $this->mime_types;
    }

    public function encode($data)
    {
        return json_encode($data);
    }

    public function decode($data)
    {
        $result = json_decode($data, true);
        $error = $this->get_json_error(json_last_error());

        return array($result, $error);
    }

    protected function get_json_error($error_code)
    {
        $errors = array(
            JSON_ERROR_NONE           => '',
            JSON_ERROR_DEPTH          => __('json_error_depth'),
            JSON_ERROR_STATE_MISMATCH => __('json_error_state_mismatch'),
            JSON_ERROR_CTRL_CHAR      => __('json_error_ctrl_char'),
            JSON_ERROR_SYNTAX         => __('json_error_syntax'),
            JSON_ERROR_UTF8           => __('json_error_utf8')
        );

        if (isset($errors[$error_code])) {
            $error = $errors[$error_code];
        } else {
            $error = __('json_error_unknown');
        }

        return $error;

    }
}
