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

namespace Tygh\Api\Entities\v10;

class Payments extends \Tygh\Api\Entities\Payments
{
    public function index($id = 0, $params = array())
    {
        if (!$id && !isset($params['items_per_page'])) {
            $params['items_per_page'] = 0;
        }

        $result = parent::index($id, $params);

        if (!$id) {
            $result['data'] = $result['data']['payments'];
        }

        return $result;
    }
}
