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

use Twigmo\Api\TwgApiBase;

/*
 * Twigmo Api v 2.0
 * includes separate section for meta data
 * and errors
 */
class TwgApiv2 extends TwgApiBase
{
    const STATUS_OK = 'OK';
    const STATUS_ERROR = 'ERROR';
    const VERSION = '2.0';

    public function __construct()
    {
        $this->meta = array (
            'version' => self::VERSION
        );
    }

    public function setMeta($value, $name)
    {
        $this->meta[$name] = $value;
    }

    public function getMeta($name = '')
    {
        if (empty($name)) {
            return $this->meta;
        }

        return !empty($this->meta[$name]) ? $this->meta[$name] : '';
    }

    public function getResponseData()
    {
        $result = array (
            'meta' => $this->meta
        );

        if (!empty($this->errors)) {
            $result['meta']['status'] = self::STATUS_ERROR;
            $result['meta']['errors'] = $this->errors;
        } else {
            $result['meta']['status'] = self::STATUS_OK;
        }

        if (!empty($this->data)) {
            $result['data'] = $this->data;
        }

        return $result;
    }

    public function parseResponse($doc, $format = TWG_DEFAULT_DATA_FORMAT)
    {
        $data = ApiData::parseDocument($doc, $format);

        if (empty($data)) {
            return false;
        }

        if (empty($data['meta'])) {
            return false;
        }

        $this->meta = $data['meta'];

        if (!empty($data['meta']['errors'])) {
            $this->errors = ApiData::getObjects($data['meta']['errors']);
        }

        if (!empty($data['data'])) {
            $this->data = $data['data'];
        }

        return true;
    }

    public function setResponseList($list)
    {
        if (!empty($list)) {
            $this->setData(current($list));
        }
    }
}
