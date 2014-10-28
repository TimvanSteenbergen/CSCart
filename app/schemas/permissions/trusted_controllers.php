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

return array(
    'auth' => array (
        'allow' => true
    ),
    'image' => array (
        'allow' => true,
        'areas' => array('A', 'C')
    ),
    'payment_notification' => array (
        'allow' => true
    ),
    'profiles' => array (
        'allow' => array (
            'password_reminder' => true,
        ),
    ),
    'helpdesk_connector' => array (
        'allow' => true
    ),
);
