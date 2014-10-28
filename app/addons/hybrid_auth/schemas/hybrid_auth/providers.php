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

$schema = array(
    'openid' => array(
        'provider' => 'OpenID'
    ),
    'aol' => array(
        'provider' => 'AOL',
    ),
    'google' => array(
        'provider' => 'Google',
        'keys' => array(
            'id' => array(
                'db_field' => 'app_id',
                'label' => 'id',
                'required' => true
            ),
            'secret' => array(
                'db_field' => 'app_secret_key',
                'label' => 'secret_key',
                'required' => true
            ),
        ),
        'params' => array(
            'google_callback' => array(
                'type' => 'template',
                'template' => 'addons/hybrid_auth/components/callback_url.tpl',
            )
        )
    ),
    'facebook' => array(
        'provider' => 'Facebook',
        'keys' => array(
            'id' => array(
                'db_field' => 'app_id',
                'label' => 'id',
                'required' => true
            ),
            'secret' => array(
                'db_field' => 'app_secret_key',
                'label' => 'secret_key',
                'required' => true
            )
        )
    ),
    'paypal' => array(
        'provider' => 'PayPal',
        'keys' => array(
            'id' => array(
                'db_field' => 'app_id',
                'label' => 'id',
                'required' => true
            ),
            'secret' => array(
                'db_field' => 'app_secret_key',
                'label' => 'secret_key',
                'required' => true
            ),
        ),
        'params' => array(
            'paypal_seamless' => array(
                'type' => 'checkbox',
                'label' => 'paypal_seamless',
                'default' => 'Y'
            ),
            'paypal_sandbox' => array(
                'type' => 'checkbox',
                'label' => 'paypal_sandbox',
            ),
            'paypal_callback' => array(
                'type' => 'template',
                'template' => 'addons/hybrid_auth/components/callback_url.tpl',
            )
        )
    ),
    'twitter' => array(
        'provider' => 'Twitter',
        'keys' => array(
            'key' => array(
                'db_field' => 'app_id',
                'label' => 'id',
                'required' => true
            ),
            'secret' => array(
                'db_field' => 'app_secret_key',
                'label' => 'secret_key',
                'required' => true
            ),
        ),
        'params' => array(
            'twitter_callback' => array(
                'type' => 'template',
                'template' => 'addons/hybrid_auth/components/callback_url.tpl',
            )
        )
    ),
    'yahoo' => array(
        'provider' => 'Yahoo',
        'keys' => array(
            'key' => array(
                'db_field' => 'app_id',
                'label' => 'id',
                'required' => true
            ),
            'secret' => array(
                'db_field' => 'app_secret_key',
                'label' => 'secret_key',
                'required' => true
            ),
        )
    ),
    'live' => array(
        'provider' => 'Live',
        'keys' => array(
            'id' => array(
                'db_field' => 'app_id',
                'label' => 'id',
                'required' => true
            ),
            'secret' => array(
                'db_field' => 'app_secret_key',
                'label' => 'secret_key',
                'required' => true
            )
        )
    ),
    'linkedin' => array(
        'provider' => 'LinkedIn',
        'keys' => array(
            'key' => array(
                'db_field' => 'app_id',
                'label' => 'id',
                'required' => true
            ),
            'secret' => array(
                'db_field' => 'app_secret_key',
                'label' => 'secret_key',
                'required' => true
            )
        )
    ),
    'foursquare' => array(
        'provider' => 'Foursquare',
        'keys' => array(
            'id' => array(
                'db_field' => 'app_id',
                'label' => 'id',
                'required' => true
            ),
            'secret' => array(
                'db_field' => 'app_secret_key',
                'label' => 'secret_key',
                'required' => true
            )
        )
    ),
);

return $schema;
