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
use Tygh\Languages\Languages;
use Tygh\Languages\Values as LanguageValues;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    fn_trusted_vars("lang_data", "new_lang_data");
    $suffix = 'manage';

    //
    // Update language variables
    //
    if ($mode == 'm_update_variables') {
        if (is_array($_REQUEST['lang_data'])) {
            fn_update_lang_var($_REQUEST['lang_data']);
        }

        $suffix = 'translations';
    }

    //
    // Delete language variables
    //
    if ($mode == 'm_delete_variables') {
        if (!empty($_REQUEST['names'])) {
            LanguageValues::deleteVariables($_REQUEST['names']);
        }

        $suffix = 'translations';
    }

    //
    // Add new language variable
    //
    if ($mode == 'update_variables') {
        if (!empty($_REQUEST['new_lang_data'])) {
            $params = array('clear' => false);
            foreach (fn_get_translation_languages() as $lc => $_v) {
                fn_update_lang_var($_REQUEST['new_lang_data'], $lc, $params);
            }
        }

        $suffix = 'translations';
    }

    if ($mode == 'update_translation') {
        $uploaded_data = fn_filter_uploaded_data('language_data', array('po', 'zip'));

        if (!empty($uploaded_data['po_file']['path'])) {
            $ext = fn_get_file_ext($uploaded_data['po_file']['name']);

            $params = array(
                'reinstall' => true,
                'validate_lang_code' => $_REQUEST['language_data']['lang_code'],
            );
            if ($ext == 'po') {
                $result = Languages::installLanguagePack($uploaded_data['po_file']['path'], $params);
            } else {
                $result = Languages::installZipPack($uploaded_data['po_file']['path'], $params);
            }

            if (!$result) {
                fn_delete_notification('changes_saved');
            }
        }
    }

     //
    // Delete languages
    //
    if ($mode == 'm_delete') {

        if (!empty($_REQUEST['lang_ids'])) {
            fn_delete_languages($_REQUEST['lang_ids']);
        }
    }

    //
    // Update languages
    //
    if ($mode == 'm_update') {

        if (!Registry::get('runtime.company_id')) {
            if (!empty($_REQUEST['update_language'])) {
                foreach ($_REQUEST['update_language'] as $lang_id => $data) {
                    Languages::update($data, $lang_id);
                }
            }

            fn_save_languages_integrity();
        }
    }

    //
    // Create/update language
    //
    if ($mode == 'update') {

        $lc = false;
        $errors = false;

        if (!Registry::get('runtime.company_id')) {
            $lang_data = $_REQUEST['language_data'];

            if (fn_allowed_for('ULTIMATE:FREE')) {
                if ($lang_data['lang_code'] == DEFAULT_LANGUAGE && $lang_data['status'] != 'A') {
                    fn_set_notification('E', __('error'), __('default_language_status'));
                    $errors = true;

                } else {
                    if (isset($lang_data['status']) && $lang_data['status'] == 'A') {
                        Languages::changeDefaultLanguage($lang_data['lang_code']);
                    }
                }
            }

            if (!$errors) {
                $lc = Languages::update($lang_data, $_REQUEST['lang_id']);
            }

            if ($lc !== false) {
                fn_save_languages_integrity();
            }
        }

        if ($lc == false) {
            fn_delete_notification('changes_saved');
        }
    }

    if ($mode == 'install_from_po') {
        $uploaded_data = fn_filter_uploaded_data('language_data', array('po', 'zip'));

        if (!empty($uploaded_data['po_file']['path'])) {
            $ext = fn_get_file_ext($uploaded_data['po_file']['name']);

            if ($ext == 'po') {
                $result = Languages::installLanguagePack($uploaded_data['po_file']['path']);
            } else {
                $result = Languages::installZipPack($uploaded_data['po_file']['path']);
            }

            if (!$result) {
                fn_delete_notification('changes_saved');
            }
        }
    }

    $q = (empty($_REQUEST['q'])) ? '' : $_REQUEST['q'];

    return array(CONTROLLER_STATUS_OK, "languages.$suffix?q=$q");
}

//
// Get language variables values
//
if ($mode == 'manage') {

    if (fn_allowed_for('ULTIMATE:FREE') && !defined('AJAX_REQUEST')) {
        fn_set_notification('N', __('notice'), __('change_language_in_free_mode'), 'K');
    }

    $sections = array(
        'translations' => array(
            'title' => __('translations'),
            'href' => fn_url('languages.translations'),
        ),
        'manage_languages' => array(
            'title' => __('manage_languages'),
            'href' => fn_url('languages.manage'),
        ),
    );

    Registry::set('navigation.dynamic.sections', $sections);
    Registry::set('navigation.dynamic.active_section', 'manage_languages');

    Registry::set('navigation.tabs', array (
        'languages' => array (
            'title' => __('installed_languages'),
            'js' => true
        ),
    ));

    if (!Registry::get('runtime.company_id')) {
        Registry::set('navigation.tabs.available_languages', array (
            'title' => __('available_languages'),
            'ajax' => true,
            'href' => 'languages.install_list',
        ));
    }

    $view = Registry::get('view');

    $languages = fn_get_translation_languages(true);
    $view->assign('langs', $languages);
    $view->assign('countries', fn_get_simple_countries(false, DESCR_SL));

} elseif ($mode == 'install_list') {
    $view = Registry::get('view');
    $langs_meta = Languages::getLangPacksMeta();

    $languages = fn_get_translation_languages(true);

    $view->assign('langs_meta', $langs_meta);
    $view->assign('countries', fn_get_simple_countries(false, DESCR_SL));

    $view->assign('langs', $languages);

    $view->display('views/languages/components/install_languages.tpl');
    exit(0);

} elseif ($mode == 'install' && !empty($_REQUEST['pack'])) {
    $pack_path = Registry::get('config.dir.lang_packs') . fn_basename($_REQUEST['pack']);

    if (Languages::installCrowdinPack($pack_path, array())) {
        return array(CONTROLLER_STATUS_OK, 'languages.manage');
    } else {
        return array(CONTROLLER_STATUS_OK, 'languages.manage?selected_section=available_languages');
    }

} elseif ($mode == 'translations') {
    $sections = array(
        'translations' => array(
            'title' => __('translations'),
            'href' => fn_url('languages.translations'),
        ),
        'manage_languages' => array(
            'title' => __('manage_languages'),
            'href' => fn_url('languages.manage'),
        ),
    );
    Registry::set('navigation.dynamic.sections', $sections);
    Registry::set('navigation.dynamic.active_section', 'translations');

    list($lang_data, $search) = LanguageValues::getVariables($_REQUEST, Registry::get('settings.Appearance.admin_elements_per_page'));

    Registry::get('view')->assign('lang_data', $lang_data);
    Registry::get('view')->assign('search', $search);

} elseif ($mode == 'delete_variable') {

    LanguageValues::deleteVariables($_REQUEST['name']);

    return array(CONTROLLER_STATUS_REDIRECT);

} elseif ($mode == 'update') {
    $lang_data = Languages::get(array('lang_id' => $_REQUEST['lang_id']), 'lang_id');
    if (empty($lang_data[$_REQUEST['lang_id']])) {
        return array(CONTROLLER_STATUS_NO_PAGE);

    } else {
        $lang_data = $lang_data[$_REQUEST['lang_id']];
    }

    Registry::get('view')->assign('lang_data', $lang_data);
    Registry::get('view')->assign('countries', fn_get_simple_countries(false, DESCR_SL));

} elseif ($mode == 'update_status') {

    if (fn_allowed_for('ULTIMATE:FREE')) {
        if ($_REQUEST['status'] == 'H') {
            fn_set_notification('E', __('error'), __('language_hidden_status_free'));

            return array(CONTROLLER_STATUS_REDIRECT, 'languages.manage');
        }

        $lang_data = Languages::get(array('lang_id' => $_REQUEST['id']), 'lang_id');
        $lang_data = $lang_data[$_REQUEST['id']];

        if ($lang_data['lang_code'] == DEFAULT_LANGUAGE) {
            fn_set_notification('E', __('error'), __('default_language_status'));

        } else {
            if ($_REQUEST['status'] == 'A') {
                Languages::changeDefaultLanguage($lang_data['lang_code']);
            }

            fn_tools_update_status($_REQUEST);
            fn_save_languages_integrity();

            if (defined('AJAX_REQUEST')) {
                Registry::get('ajax')->assign('force_redirection', fn_url('languages.manage'));
            }
        }

    } else {
        fn_tools_update_status($_REQUEST);
        fn_save_languages_integrity();
    }

    return array(CONTROLLER_STATUS_REDIRECT, 'languages.manage');

} elseif ($mode == 'clone_language') {
    $lang_id = $_REQUEST['lang_id'];
    $lang_data = Languages::get(array('lang_id' => $lang_id), 'lang_id');

    if (!empty($lang_data) && !empty($_REQUEST['lang_code'])) {
        $language = $lang_data[$lang_id];

        $new_language = array(
            'lang_code' => $_REQUEST['lang_code'],
            'name' => $language['name'] . '_clone',
            'country_code' => $language['country_code'],
            'from_lang_code' => $language['lang_code'],
            'status' => 'D', // Disable cloned language
        );

        $lc = Languages::update($new_language, 0);

        if ($lc !== false) {
            fn_save_languages_integrity();
        }
    }

    return array(CONTROLLER_STATUS_REDIRECT, "languages.manage");

} elseif ($mode == 'export_language') {
    $lang_id = $_REQUEST['lang_id'];
    $lang_data = Languages::get(array('lang_id' => $lang_id), 'lang_id');

    if (!empty($lang_data)) {
        Languages::createPoFile($lang_data[$lang_id]['lang_code']);
    }

    return array(CONTROLLER_STATUS_REDIRECT, "languages.manage");

} elseif ($mode == 'delete_language') {

    if (!empty($_REQUEST['lang_id'])) {
        fn_delete_languages($_REQUEST['lang_id']);
    }

    return array(CONTROLLER_STATUS_REDIRECT, "languages.manage?selected_section=languages");

} elseif ($mode == 'update_translation') {
    $lang_data = Languages::get(array('lang_id' => $_REQUEST['lang_id']), 'lang_id');
    if (empty($lang_data[$_REQUEST['lang_id']])) {
        return array(CONTROLLER_STATUS_NO_PAGE);

    } else {
        $lang_data = $lang_data[$_REQUEST['lang_id']];
    }

    Registry::get('view')->assign('lang_data', $lang_data);
}

/**
 * @deprecated
 *
 * Updates language
 *
 * @param array $language_data Language data
 * @param string $lang_id language id
 * @return string language id
 */
function fn_update_language($language_data, $lang_id)
{
    return Languages::update($language_data, $lang_id);
}

/**
 * @deprecated
 *
 * Deletes language variablle
 *
 * @param array $names List of language variables go be deleted
 * @return boolean Always true
 */
function fn_delete_language_variables($names)
{
    return LanguageValues::deleteVariables($names);
}

/**
 * @deprecated
 */
function fn_get_language_variables($params, $items_per_page = 0, $lang_code = DESCR_SL)
{
    return LanguageValues::getVariables($params, $items_per_page, $lang_code);
}
