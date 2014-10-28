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

use Tygh\Mailer;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_form_builder_demo_install()
{
    // Get admin account information and update "Email to" field in the "Contact us" page
    $admin_email = db_get_field('SELECT email FROM ?:users WHERE user_id = ?i', 1);

    db_query('UPDATE ?:form_options SET value = ?s WHERE element_id = ?i', $admin_email, 5);
}

function fn_form_builder_delete_page(&$page_id)
{
    // deleting form elements
    $element_ids = db_get_fields("SELECT element_id FROM ?:form_options WHERE page_id = ?i", $page_id);
    db_query("DELETE FROM ?:form_descriptions WHERE object_id IN (?n)", $element_ids);
    db_query("DELETE FROM ?:form_options WHERE page_id = ?i", $page_id);
}

function fn_form_builder_update_page_post(&$page_data, &$page_id, &$lang_code)
{
    // page form processing
    if (!empty($page_data['form'])) {

        $elements_data = empty($page_data['form']['elements_data']) ? array() : $page_data['form']['elements_data'];
        $general_data = empty($page_data['form']['general']) ? array() : $page_data['form']['general'];

        $elm_ids = array();

        if (!empty($elements_data)) {

            // process elements
            foreach ($elements_data as $data) {

                if (empty($data['description']) && $data['element_type'] != FORM_SEPARATOR) {
                    continue;
                }

                if (!empty($data['element_type']) && strpos(FORM_HEADER . FORM_SEPARATOR, $data['element_type']) !== false) {
                    $data['required'] = 'N';
                }

                $data['page_id'] = $page_id;

                if (!empty($data['element_id'])) {
                    $data['object_id'] = $element_id = $data['element_id'];
                    db_query('UPDATE ?:form_options SET ?u WHERE element_id = ?i', $data, $element_id);
                    db_query('UPDATE ?:form_descriptions SET ?u WHERE object_id = ?i AND lang_code = ?s', $data, $element_id, $lang_code);
                } else {
                    $data['object_id'] = $element_id = db_query('INSERT INTO ?:form_options ?e', $data);
                    foreach (fn_get_translation_languages() as $data['lang_code'] => $_v) {
                        db_query('INSERT INTO ?:form_descriptions ?e', $data);
                    }
                }

                $elm_ids[] = $element_id;

                // process variants
                if (!empty($data['variants'])) {
                    foreach ($data['variants'] as $k => $v) {

                        if (empty($v['description'])) {
                            continue;
                        }

                        $v['parent_id'] = $element_id;
                        $v['element_type'] = FORM_VARIANT; // variant
                        $v['page_id'] = $page_id;

                        if (!empty($v['element_id'])) {
                            $v['object_id'] = $v['element_id'];
                            db_query('UPDATE ?:form_options SET ?u WHERE element_id = ?i', $v, $v['element_id']);
                            db_query('UPDATE ?:form_descriptions SET ?u WHERE object_id = ?i AND lang_code = ?s', $v, $v['element_id'], $lang_code);
                        } else {
                            $v['object_id'] = $v['element_id'] = db_query('INSERT INTO ?:form_options ?e', $v);
                            foreach (fn_get_translation_languages() as $v['lang_code'] => $_v) {
                                db_query('INSERT INTO ?:form_descriptions ?e', $v);
                            }
                        }

                        $elm_ids[] = $v['element_id'];
                    }
                }

            }
        }

        // update or insert general form data
        if (!empty($general_data)) {
            //$gdata = fn_trusted_vars('general_data', true);
            foreach ($general_data as $type => $data) {

                $elm_id = db_get_field("SELECT element_id FROM ?:form_options WHERE page_id = ?i AND element_type = ?s", $page_id, $type);
                $_description = array();
                $_data = array (
                    'element_type' => $type,
                    'page_id' => $page_id,
                    'status' => 'A',
                );

                if (($type == FORM_RECIPIENT) || ($type == FORM_IS_SECURE)) {
                    $_data['value'] = $data;
                }

                $_description = array(
                    'description' => $data
                );

                if (empty($elm_id)) {
                    $_description['object_id'] = $elm_id = db_query('INSERT INTO ?:form_options ?e', $_data);
                    foreach (fn_get_translation_languages() as $_description['lang_code'] => $_v) {
                        db_query('INSERT INTO ?:form_descriptions ?e', $_description);
                    }
                } else {
                    db_query('UPDATE ?:form_options SET ?u WHERE element_id = ?i', $_data, $elm_id);
                    db_query('UPDATE ?:form_descriptions SET ?u WHERE object_id = ?i AND lang_code = ?s', $_description, $elm_id, $lang_code);
                }

                $elm_ids[] = $elm_id;
            }
        }

        // Delete obsolete elements
        $obsolete_ids = db_get_fields("SELECT element_id FROM ?:form_options WHERE page_id = ?i AND element_id NOT IN (?n)", $page_id, $elm_ids);

        if (!empty($obsolete_ids)) {
            db_query("DELETE FROM ?:form_options WHERE parent_id IN (?n)", $obsolete_ids);
            db_query("DELETE FROM ?:form_options WHERE element_id IN (?n)", $obsolete_ids);
            db_query("DELETE FROM ?:form_descriptions WHERE object_id IN (?n)", $obsolete_ids);
        }
    }
}

function fn_form_builder_get_page_data(&$page_data)
{
    if (!empty($page_data['page_type']) && $page_data['page_type'] == PAGE_TYPE_FORM) {
        list($page_data['form']['elements'], $page_data['form']['general']) = fn_get_form_elements($page_data['page_id'], true);
    }
}

//
// Get form
// @page_id - ID of page to get form for
// @return array(form elements, general form data )
function fn_get_form_elements($page_id, $avail_only = false, $lang = CART_LANGUAGE)
{
    $where = ($avail_only == true) ? " AND f.status = 'A'" : '';
    $general_data = array();
    $elms = db_get_hash_array("SELECT f.*, d.description FROM ?:form_options as f LEFT JOIN ?:form_descriptions as d ON d.object_id=f.element_id AND d.lang_code = ?s WHERE f.page_id = ?i $where ORDER BY f.position", 'element_id', $lang, $page_id);

    // Build variants
    foreach ($elms as $elm_id => $data) {
        if ($data['element_type'] == FORM_VARIANT) { // this is variant
            if (!empty($elms[$data['parent_id']])) {
                $elms[$data['parent_id']]['variants'][$elm_id] = $data;
            }
            unset($elms[$elm_id]);
            continue;
        }

        // Get general form options
        if (strpos(FORM_SUBMIT, $data['element_type']) !== false) {
            $general_data[$data['element_type']] = $data['description'];
            unset($elms[$elm_id]);
            continue;
        }

        if (strpos(FORM_IS_SECURE . FORM_RECIPIENT, $data['element_type']) !== false) {
            $general_data[$data['element_type']] = $data['value'];
            unset($elms[$elm_id]);
            continue;
        }

    }

    return array($elms, $general_data);
}

//
// Send form
// @page_id - form page ID
// @elements_data - elements data
function fn_send_form($page_id, $form_values)
{
    $result = false;
    if (!empty($form_values)) {
        $page_data = fn_get_page_data($page_id);

        if (!empty($page_data['form']['elements'])) {

            $result = true;
            $attachments = array();
            $fb_files = fn_filter_uploaded_data('fb_files');

            if (!empty($fb_files)) {
                foreach ($fb_files as $k => $v) {
                    $attachments[$v['name']] = $v['path'];
                    $form_values[$k] = $v['name'];
                }
            }

            $max_length = 0;

            $sender = '';
            foreach ($page_data['form']['elements'] as $k => $v) {
                if (($l = strlen($v['description'])) > $max_length) {
                    $max_length = $l;
                }

                // Check if sender email exists
                if ($v['element_type'] == FORM_EMAIL) {
                    $sender = $form_values[$k];
                }

                if ($v['element_type'] == FORM_DATE) {
                    $form_values[$k] = fn_parse_date($form_values[$k]);
                }

                if ($v['element_type'] == FORM_REFERER) {
                    $form_values[$k] = $_SESSION['auth']['referer'];
                }

                if ($v['element_type'] == FORM_IP_ADDRESS) {
                    $ip = fn_get_ip();
                    $form_values[$k] = $ip['host'];
                }
            }
            $max_length += 2;

            if ($result == true) {

                $from = 'default_company_support_department';
                $is_html = true;

                fn_set_hook('send_form', $page_data, $form_values, $result, $from, $sender, $attachments, $is_html);

                if ($result == true) {
                    Mailer::sendMail(array(
                        'to' => $page_data['form']['general'][FORM_RECIPIENT],
                        'from' => $from,
                        'reply_to' => $sender,
                        'data' => array(
                            'max_length' => $max_length,
                            'elements' => $page_data['form']['elements'],
                            'form_title' => $page_data['page'],
                            'form_values' => $form_values,
                        ),
                        'attachments' => $attachments,
                        'tpl' => 'addons/form_builder/form.tpl',
                        'is_html' => $is_html
                    ), 'A');
                }
            }
        }
    }

    return $result;
}

function fn_form_builder_clone_page(&$page_id, &$clone_id)
{
    $elements = db_get_array('SELECT * FROM ?:form_options WHERE page_id = ?i AND parent_id = ?i', $page_id, 0);
    foreach ($elements as $entry) {
        $entry['page_id'] = $clone_id;
        $element_id = $entry['element_id'];
        unset($entry['element_id']);

        $new_element_id = db_query('INSERT INTO ?:form_options ?e', $entry);

        $descriptions = db_get_array('SELECT * FROM ?:form_descriptions WHERE object_id = ?i', $element_id);
        foreach ($descriptions as $array) {
            $array['object_id'] = $new_element_id;

            db_query('INSERT INTO ?:form_descriptions ?e', $array);
        }

        $sub_elements = db_get_array('SELECT * FROM ?:form_options WHERE page_id = ?i AND parent_id = ?i', $page_id, $element_id);

        if (!empty($sub_elements)) {
            foreach ($sub_elements as $row) {
                $row['parent_id'] = $new_element_id;
                $row['page_id'] = $clone_id;
                $sub_element_id = $row['element_id'];
                unset($row['element_id']);

                $new_sub_element_id = db_query('INSERT INTO ?:form_options ?e', $row);

                $descriptions = db_get_array('SELECT * FROM ?:form_descriptions WHERE object_id = ?i', $sub_element_id);
                foreach ($descriptions as $array) {
                    $array['object_id'] = $new_sub_element_id;

                    db_query('INSERT INTO ?:form_descriptions ?e', $array);
                }
            }
        }
    }
}

function fn_form_builder_page_object_by_type(&$types)
{
    $types[PAGE_TYPE_FORM] = array(
        'single' => 'form',
        'name' => 'forms',
        'add_name' => 'add_form',
        'edit_name' => 'editing_form',
        'new_name' => 'new_form',
    );
}

function fn_form_builder_init_secure_controllers(&$controllers)
{
    $controllers['pages'] = 'passive';
}

function fn_form_builder_selectable_elements()
{
    static $elms = array(
        FORM_MULTIPLE_CB,
        FORM_MULTIPLE_SB,
        FORM_RADIO,
        FORM_SELECT,
    );

    fn_set_hook('form_selectable_elements', $elms);

    return $elms;
}

function fn_form_builder_remove_pages()
{
    $pages = db_get_fields("SELECT page_id FROM ?:pages WHERE page_type = ?s ", PAGE_TYPE_FORM);

    foreach ($pages as $page_id) {
        fn_delete_page($page_id, $recurse = true);
    }
}
