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
        'option_id' => 2,
        'name' => 'card_number',
        'description' => __('card_number'),
        'value' => '',
        'option_type' =>  'I',
        'position' => 20,
        'required' => true,
    ),
    array (
        'option_id' => 3,
        'name' => 'cardholder_name',
        'description' => __('cardholder_name'),
        'value' => '',
        'option_type' =>  'I',
        'position' => 30,
        'required' => true,
    ),
    array (
        'option_id' => 5,
        'name' => 'expiry_date',
        'description' => __('expiry_date'),
        'value' => '',
        'option_type' =>  'D',
        'position' => 50,
        'required' => true,
    ),
    array (
        'option_id' => 6,
        'name' => 'cvv2',
        'description' => __('cvv2'),
        'value' => '',
        'option_type' =>  'I',
        'position' => 60,
        'required' => true,
    ),
);
return $schema;
