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
    'table' => 'gift_certificates',
    'object_name' => 'gift_certificate',
    'key' => array('gift_cert_id'),
    'fields' => array (
        'gift_cert_id' => array (
            'db_field' => 'gift_cert_id'
        ),
        'gift_cert_code' => array (
            'db_field' => 'gift_cert_code'
        ),
        'sender' => array (
            'db_field' => 'sender'
        ),
        'recipient' => array (
            'db_field' => 'recipient'
        ),
        'amount' => array (
            'db_field' => 'amount'
        ),
        'subtotal' => array (
            'db_field' => 'subtotal'
        ),
        'tax_value' => array (
            'db_field' => 'tax_value'
        ),
        'message' => array (
            'db_field' => 'message'
        ),
    )
);
return $schema;
