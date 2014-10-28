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

namespace Twigmo\Api;

/*
 * Twigmo api base object
 */
class TwgApiBase
{
    protected $errors = array(); // request errors list
    protected $data = array(); // request returned data: object list or object details
    protected $meta = array();

    public function getErrors()
    {
        $errors = array();

        if (empty($this->errors)) {
            return array();
        }

        foreach ($this->errors as $k => $v) {
            $errors[] = $v['message'];
        }

        return $errors;
    }

    public function getData()
    {
        return $this->data;
    }

    public function addError($code, $message)
    {
        $error = array (
            'code' => $code,
            'message' => $message
        );

        if (!empty($additional_data)) {
            $error = array_merge($error, $additional_data);
        }

        $this->errors[] = $error;

        return true;
    }

    public function setData($data, $name = '')
    {
        if (!empty($name)) {
            $this->data[$name] = $data;
        } else {
            $this->data = array_merge($this->data, $data);
        }
    }
}
