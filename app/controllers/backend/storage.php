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

use Tygh\Registry;
use Tygh\Settings;
use Tygh\Storage;
use Tygh\Cdn;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ($mode == 'update_storage') {
        if (!empty($_REQUEST['storage_data'])) {
            if (Registry::get('runtime.storage.storage') != $_REQUEST['storage_data']['storage']) {

                $test = Storage::instance('statics', $_REQUEST['storage_data'])->testSettings($_REQUEST['storage_data']);
                $themes = array();
                if ($test === true) {

                    $total = 0;
                    if (fn_allowed_for('ULTIMATE')) {
                        foreach (fn_get_all_companies_ids() as $company_id) {
                            $themes[$company_id] = fn_get_dir_contents(fn_get_theme_path('[themes]', 'C', $company_id));
                            $total += sizeof($themes[$company_id]);
                        }
                    } else {
                        $themes[0] = fn_get_dir_contents(fn_get_theme_path('[themes]', 'C'));
                        $total += sizeof($themes[0]);
                    }

                    $storage = Registry::get('config.storage');
                    unset($storage['statics']); // Do not transfer auto-generated data
                    $total += sizeof($storage);

                    fn_set_progress('parts', $total);

                    // transfer storages
                    foreach ($storage as $type => $options) {
                        $from = Storage::instance($type, Registry::get('runtime.storage'));
                        $to = Storage::instance($type, $_REQUEST['storage_data']);

                        $to->putList($from->getList(''), $from->getAbsolutePath(''), array(
                            'overwrite' => true
                        ));
                    }

                    Settings::instance()->updateValue('storage', serialize($_REQUEST['storage_data']));
                    fn_clear_cache();
                    fn_set_notification('N', __('notice'), __('text_storage_changed'));

                } else {
                    fn_save_post_data('storage_data');
                    fn_set_notification('E', __('error'), $test);
                }
            }
        }

        return array(CONTROLLER_STATUS_OK, "storage.manage");
    }

    if ($mode == 'update_cdn') {

        // update
        if (Cdn::instance()->getOption('host')) {
            $distribution_data = Cdn::instance()->updateDistribution(Registry::get('config.http_host'), $_REQUEST['cdn_data']);
        } else {
            $distribution_data = Cdn::instance()->createDistribution(Registry::get('config.http_host'), $_REQUEST['cdn_data']);
        }

        if ($distribution_data !== false) {
            Cdn::instance()->save(fn_array_merge($_REQUEST['cdn_data'], $distribution_data));
        } else {
            fn_save_post_data('cdn_data');
        }

        return array(CONTROLLER_STATUS_OK, 'storage.cdn');
    }

    return;
}

if ($mode == 'manage') {

    $storage_data = fn_restore_post_data('storage_data');
    if (empty($storage_data)) {
        $storage_data = Registry::get('runtime.storage');
    }

    Registry::get('view')->assign('current_storage', Registry::get('runtime.storage.storage'));
    Registry::get('view')->assign('storage_data', $storage_data);
    Registry::get('view')->assign('amazon_data', array(
        'regions' => fn_get_amazon_regions()
    ));

} elseif ($mode == 'clear_cache') {

    fn_clear_cache();
    fn_set_notification('N', __('notice'), __('cache_cleared'));

    if (empty($_REQUEST['redirect_url'])) {
        $_REQUEST['redirect_url'] = 'index.index';
    }

    return array(CONTROLLER_STATUS_REDIRECT);

} elseif ($mode == 'clear_thumbnails') {

    Storage::instance('images')->deleteDir('thumbnails');
    fn_set_notification('N', __('notice'), __('thumbnails_removed'));

    if (empty($_REQUEST['redirect_url'])) {
        $_REQUEST['redirect_url'] = 'index.index';
    }

    return array(CONTROLLER_STATUS_REDIRECT);

} elseif ($mode == 'cdn') {

    $stored_cdn_data = fn_restore_post_data('cdn_data');

    if (Cdn::instance()->getOption('is_active') === false && Cdn::instance()->isActive()) {

        Cdn::instance()->save(array(
            'is_active' => true
        ));

        fn_set_notification('N', __('notice'), __('text_cdn_setup'));
    }

    if (Cdn::instance()->getHost()) {
        Registry::get('view')->assign('cdn_test_url', 'http://' . Cdn::instance()->getHost() . '/js/tygh/core.js');
    }

    if (!empty($stored_cdn_data)) {
        Registry::get('view')->assign('cdn_data', $stored_cdn_data);        
    } else {
        Registry::get('view')->assign('cdn_data', Cdn::instance()->getOptions());
    }

}

function fn_get_amazon_regions()
{
    return array(
        's3.amazonaws.com' => 'US Standard',
        's3-us-west-2.amazonaws.com' => 'Oregon',
        's3-us-west-1.amazonaws.com' => 'Northern California',
        's3-eu-west-1.amazonaws.com' => 'Ireland',
        's3-ap-southeast-1.amazonaws.com' => 'Singapore',
        's3-sa-east-1.amazonaws.com' => 'Sao Paulo',
        's3-ap-northeast-1.amazonaws.com' => 'Tokyo',
        's3-ap-southeast-2.amazonaws.com' =>'Sydney'
    );
}
