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

if (!defined('AREA')) { die('Access denied'); }

use Tygh\Registry;
use Twigmo\Core\TwigmoSettings;
use Twigmo\Core\TwigmoConnector;

// addon version
fn_define('TWIGMO_VERSION', '3.6');

fn_define('TWIGMO_UPGRADE_DIR', Registry::get('config.dir.var') . 'twigmo/');
fn_define('TWIGMO_UA_RULES_FILE', TWIGMO_UPGRADE_DIR . 'ua_rules.txt');
fn_define('TWIGMO_UPGRADE_VERSION_FILE', 'version_info.txt');

fn_define('TWIGMO_IS_NATIVE_APP', !empty($_REQUEST['is_native_app']));

fn_define('TWG_UA_RULES_STAT', 'http://twigmo.com/svc2/ua_meta/stat.php');

fn_define('TWG_DEFAULT_DATA_FORMAT', 'json');
fn_define('TWG_DEFAULT_API_VERSION', '2.0');

fn_define('TWG_RESPONSE_ITEMS_LIMIT', 10);
fn_define('TWG_MAX_DESCRIPTION_LEN', 200);

if (Registry::get('addons.twigmo.status') == 'A' && TwigmoSettings::dbIsInited()) {
    $settings = array();

    $settings['unsupported_payment_methods'] = array(
        'FRIbetaling',
        'PayPal Advanced',
        'FuturePay'
    );

    $settings['unsupported_shipping_methods'] = array();

    $settings['block_types'] = array('products', 'categories', 'pages', 'html_block');
    if (Registry::get('addons.banners.status') == 'A') {
        $settings['block_types'][] = 'banners';
    }

    $settings['images'] = array(
        'cart' => array(
            'width' =>  96,
            'height' => 96
        ),
        'catalog' => array(
            'width' =>  200,
            'height' => 200
        ),
        'prewiew' => array(
            'width' =>  130,
            'height' => 120
        ),
        'big' => array(
            'width' =>  800,
            'height' => 800,
            'keep_proportions' => true
        )
    );
    fn_set_hook('twg_config', $settings);
    // Init twigmo settings
    TwigmoSettings::moveToRuntime($settings);
}

if (file_exists(Registry::get('config.dir.addons') .'twigmo/local_conf.php')) {
    include(Registry::get('config.dir.addons') . 'twigmo/local_conf.php');
}
