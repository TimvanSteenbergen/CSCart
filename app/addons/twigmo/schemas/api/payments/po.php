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
        'name' => 'po_number',
        'description' => __('po_number'),
        'value' => '',
        'option_type' =>  'I',
        'position' => 10,
        'required' => true,
    ),
    array (
        'option_id' => 2,
        'name' => 'company_name',
        'description' => __('company_name'),
        'value' => '',
        'option_type' =>  'I',
        'position' => 20,
        'required' => true,
    ),
    array (
        'option_id' => 3,
        'name' => 'buyer_name',
        'description' => __('buyer_name'),
        'value' => '',
        'option_type' =>  'I',
        'position' => 30,
        'required' => true,
    ),
    array (
        'option_id' => 4,
        'name' => 'position',
        'description' => __('position'),
        'value' => '',
        'option_type' =>  'I',
        'position' => 40,
        'required' => true,
    ),
);
return $schema;
