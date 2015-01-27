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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'update' && $_REQUEST['addon'] == 'watermarks') {
    
        $wt_settings = !empty($_REQUEST['wt_settings']) ? $_REQUEST['wt_settings'] : array();
        $is_unset_opts = false; // Unset some applied settings
        $unset_types = array(); // Types of watermarks without image
        $wt_changed = array(); // array for changed watermark types

        if (fn_allowed_for('ULTIMATE') && !Registry::get('runtime.company_id')) {
            $companies = fn_get_short_companies();
            if (!empty($wt_settings['update_all_vendors'])) {

                $attached_image_id = fn_update_watermark_image_settings($wt_settings);
                if (!isset($attached_image_id) && $image_data = fn_get_image_pairs(WATERMARK_IMAGE_ID, 'watermark', 'M')) {
                    $attached_image_id = WATERMARK_IMAGE_ID;
                }

                foreach ($companies as $company_id => $name) {
                    fn_update_watermark_image_settings($wt_settings, $company_id, $attached_image_id);
                }
                
                // clear all companies directories
                $wt_changed['icons'] = true;
                $wt_changed['detailed'] = true;
            }
            
            // check if company options are valid
            $option_types = fn_get_apply_watermark_options();

            foreach ($companies as $company_id => $c_name) {
                $settings = fn_get_watermark_settings($company_id);
                if ($settings['type'] == 'G') {
                    // only graphic watermarks images can be empty
                    foreach ($option_types as $type => $options) {
                        foreach ($options as $name => $option_id) {
                            $image_name = ($type == 'icons') ? 'icon' : 'detailed';
                            if (empty($settings['image_pair'][$image_name]['absolute_path'])) {
                                if (!empty($_REQUEST['addon_data']['options'][$option_id]) && $_REQUEST['addon_data']['options'][$option_id] == 'Y') {
                                    $_REQUEST['addon_data']['options'][$option_id] = 'N';
                                    $is_unset_opts = true;
                                }

                                if (Settings::instance()->getValue($name, 'watermarks', $company_id) == 'Y') {
                                    Settings::instance()->updateValueById($option_id, 'N', $company_id)  ;
                                    $unset_types[] = $type;
                                }
                            }
                        }
                    }
                }
                
                if ($unset_types) {
                    foreach ($unset_types as $type) {
                        fn_set_notification('E', __('error'), $c_name . ': ' . __('wt_fail_apply_graphic_watermark', array(
                            '[image_type]' => __('wt_' . $type)
                        )));
                    }
                }
            }
        } else {

            $old_wt_setting = fn_get_watermark_settings();

            // check if settings were changed
            if (empty($old_wt_setting)) {
                $wt_changed['icons'] = true;
                $wt_changed['detailed'] = true;
            } else {
                foreach ($old_wt_setting as $k => $v) {
                    if (empty($wt_settings[$k]) || $v != $wt_settings[$k]) {
                        if ($k == 'font_size_icon') {
                            $wt_changed['icons'] = true;
                        } elseif ($k == 'font_size_detailed') {
                            $wt_changed['detailed'] = true;
                        } elseif ($k != 'image_pair') {
                            $wt_changed['icons'] = true;
                            $wt_changed['detailed'] = true;
                            break;
                        }
                    }
                }
            }

            if (fn_update_watermark_image_settings($wt_settings)) {
                $wt_changed['icons'] = true;
                $wt_changed['detailed'] = true;
            }

            $watermark_pairs = fn_get_image_pairs(WATERMARK_IMAGE_ID, 'watermark', 'M');

            if (!empty($_REQUEST['addon_data'])) {
                // unset wt option if watermark is not loaded
                $option_types = fn_get_apply_watermark_options();

                foreach ($option_types as $type => $options) {
                    foreach ($options as $name => $option_id) {
                        if (empty($_REQUEST['addon_data']['options'][$option_id])) {
                            continue;
                        }

                        if ($wt_settings['type'] == 'G' && $_REQUEST['addon_data']['options'][$option_id] == 'Y') {
                            $image_name = ($type == 'icons') ? 'icon' : 'detailed';
                            if (empty($watermark_pairs[$image_name]['absolute_path'])) {
                                $_REQUEST['addon_data']['options'][$option_id] = 'N';
                                $is_unset_opts = true;
                                $unset_types[] = $type;
                            }
                        }

                        if (Settings::instance()->getValue($name, '') != $_REQUEST['addon_data']['options'][$option_id]) {
                            $wt_changed[$type] = true;
                        }
                    }
                }
            }

            if ($unset_types) {
                foreach ($unset_types as $type) {
                    fn_set_notification('E', __('error'), __('wt_fail_apply_graphic_watermark', array(
                        '[image_type]' => __('wt_' . $type)
                    )));
                }
            }

        }

        if (!empty($wt_changed)) {
            fn_delete_watermarks($wt_changed);
        }

        if ($is_unset_opts) {
            fn_update_addon($_REQUEST['addon_data']);

            return array(CONTROLLER_STATUS_REDIRECT, "addons.manage");
        }
    }
}

if ($mode == 'update') {

    if ($_REQUEST['addon'] == 'watermarks') {
        $wt_fonts = array(
            'arial'   => 'Arial',
            'arialbd' => 'Arial Bold',
            'georgia' => 'Georgia',
            'symbol'  => 'Symbol',
            'times'   => 'Times New Roman',
            'verdana' => 'Verdana'
        );
        Registry::get('view')->assign('wt_fonts', $wt_fonts);

        $wt_font_sizes = range(5, 50, 5);
        Registry::get('view')->assign('wt_font_sizes', $wt_font_sizes);

        $wt_font_colors = array (
            'white' => 'White',
            'black' => 'Black',
            'gray'  => 'Gray',
            'clear_gray' => 'Clear gray',
        );
        Registry::get('view')->assign('wt_font_colors', $wt_font_colors);

        $wt_settings = fn_get_watermark_settings();

        Registry::get('view')->assign('wt_settings', $wt_settings);

    }

}

function fn_update_watermark_image_settings($wt_settings, $company_id = null, $attached_image_id = null)
{
    if (!$setting_id = Settings::instance()->getId('watermark', '')) {
        $setting_id = Settings::instance()->update(array(
            'name' =>           'watermark',
            'section_id' =>     0,
            'section_tab_id' => 0,
            'type' =>           'A', // any not existing type
            'position' =>       0,
            'is_global' =>      'N',
            'handler' =>        ''
        ));
    }
    Settings::instance()->updateValueById($setting_id, serialize($wt_settings), $company_id);

    if ($wt_settings['type'] == 'G') {
        $_REQUEST['wt_image_image_data'][0]['image_alt'] = '';
        $_REQUEST['wt_image_image_data'][0]['detailed_alt'] = '';

        $image_id = !empty($company_id) ? $company_id : WATERMARK_IMAGE_ID;

        if (!is_null($attached_image_id)) {
            fn_clone_image_pairs($image_id, $attached_image_id, 'watermark');
        } else {
            $pair_ids = fn_attach_image_pairs('wt_image', 'watermark', $image_id);

            if (!empty($pair_ids)) {
                $attached_image_id = $image_id;
            }
        }
    }

    return $attached_image_id;
}
