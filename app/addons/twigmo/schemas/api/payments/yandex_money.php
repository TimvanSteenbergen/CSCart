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

$schema = array (
    array (
        'option_id' => 1,
        'name' => 'yandex_payment_type',
        'description' => __('select_yandex_payment'),
        'value' => '',
        'option_type' =>  'S',
        'position' => 10,
        'option_variants' => fn_twg_api_get_yandex_payments(),
        'required' => true,
    ),
);

return $schema;

function fn_twg_api_get_yandex_payments()
{
    $values = array(
        array('param_id' => 1,
                'param' => 'pc',
                'descr' => __('yandex_payment_yandex'),
                'position' => 1),
        array('param_id' => 2,
                'param' => 'ac',
                'descr' => __('yandex_payment_card'),
                'position' => 2),
        array('param_id' => 3,
                'param' => 'gp',
                'descr' => __('yandex_payment_terminal'),
                'position' => 3),
        array('param_id' => 4,
                'param' => 'mc',
                'descr' => __('yandex_payment_phone'),
                'position' => 4),
        array('param_id' => 5,
                'param' => 'nv',
                'descr' => __('yandex_payment_webmoney'),
                'position' => 5),

    );
    $variants = array();

    foreach ($values as $k => $v) {
        $variants[] = array (
            'variant_id' => $v['param_id'],
            'variant_name' => $v['param'],
            'description' => $v['descr'],
            'position' => $v['position'],
        );
    }

    return $variants;
}
