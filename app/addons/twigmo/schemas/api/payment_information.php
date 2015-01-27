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
    'object_name' => 'payment_information',
    'fields' => array (
        'card' => array (
            'name' => 'card'
        ),
        'card_number' => array (
            'name' => 'card_number'
        ),
        'cardholder_name' => array (
            'name' => 'cardholder_name'
        ),
        'expiry_date_month' => array (
            'name' => 'expiry_month'
        ),
        'expiry_date_year' => array (
            'name' => 'expiry_year'
        ),
        'start_date_month' => array (
            'name' => 'start_month'
        ),
        'start_date_year' => array (
            'name' => 'start_year'
        ),
        'cvv2' => array (
            'name' => 'cvv2'
        ),
        'issue_number' => array (
            'name' => 'issue_number'
        ),
        'description' => array (
            'name' => 'description'
        ),

        'customer_signature' => array (
            'name' => 'customer_signature'
        ),
        'checking_account_number' => array (
            'name' => 'checking_account_number'
        ),
        'bank_routing_number' => array (
            'name' => 'bank_routing_number'
        ),

        'date_of_birth' => array (
            'name' => 'date_of_birth'
        ),
        'last4ssn' => array (
            'name' => 'last4ssn'
        ),
        'phone' => array (
            'name' => 'phone'
        ),
        'passport_number' => array (
            'name' => 'passport_number'
        ),
        'drlicense_number' => array (
            'name' => 'drlicense_number'
        ),
        'routing_code' => array (
            'name' => 'routing_code'
        ),
        'account_number' => array (
            'name' => 'account_number'
        ),
        'check_number' => array (
            'name' => 'check_number'
        ),

        'po_number' => array (
            'name' => 'po_number'
        ),
        'company_name' => array (
            'name' => 'company_name'
        ),
        'buyer_name' => array (
            'name' => 'buyer_name'
        ),
    )
);
return $schema;
