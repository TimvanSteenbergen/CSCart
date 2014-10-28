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
    'object_name' => 'address',
    'key' => array('address_id'),
    'fields' => array (
        'address_id' => array (
            'name' => 'address_id'
        ),
        'type' => array (
            'name' => 'type'
        ),
        'title' => array (
            'name' => 'title'
        ),
        'firstname' => array (
            'name' => 'firstname'
        ),
        'lastname' => array (
            'name' => 'lastname'
        ),
        'address' => array (
            'name' => 'address'
        ),
        'address_2' => array (
            'name' => 'address_2'
        ),
        'city' => array (
            'name' => 'city'
        ),
        'county' => array (
            'name' => 'county'
        ),
        'state' => array (
            'name' => 'state'
        ),
        'state_description' => array (
            'name' => 'state_descr',
            'process_get' => array (
                'func' => 'fn_get_state_name',
                'params' => array (
                    'state' => array (
                        'name' => 'state'
                    ),
                    'country' => array (
                        'name' => 'country'
                    ),
                    'lang_code' => array (
                        'param' => 'lang_code'
                    )
                )
            )
        ),
        'country' => array (
            'name' => 'country'
        ),
        'country_description' => array (
            'name' => 'country_descr',
            'process_get' => array (
                'func' => 'fn_get_country_name',
                'params' => array (
                    'country' => array (
                        'name' => 'country'
                    ),
                    'lang_code' => array (
                        'param' => 'lang_code'
                    )
                )
            )
        ),
        'states_count' => array (
            'name' => 'states_count',
            'process_get' => array (
                'func' => 'Twigmo\\Core\\Api::getStatesCount',
                'params' => array (
                    'country' => array (
                        'name' => 'country'
                    ),
                )
            )
        ),
        'zipcode' => array (
            'name' => 'zipcode'
        ),
        'phone' => array (
            'name' => 'phone'
        ),
    )
);
return $schema;
