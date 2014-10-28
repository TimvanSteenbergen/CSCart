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

use Tygh\Bootstrap;
use Tygh\Registry;
use Tygh\Storage;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

// Set line endings autodetection
ini_set('auto_detect_line_endings', true);
set_time_limit(3600);
fn_define('DB_LIMIT_SELECT_ROW', 30);

if (empty($_SESSION['export_ranges'])) {
    $_SESSION['export_ranges'] = array();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $suffix = '';

    $layout_data = !empty($_REQUEST['layout_data']) ? $_REQUEST['layout_data'] : array();
    //
    // Select layout
    //
    if ($mode == 'set_layout') {
        db_query("UPDATE ?:exim_layouts SET active = 'N' WHERE pattern_id = ?s", $layout_data['pattern_id']);
        db_query("UPDATE ?:exim_layouts SET active = 'Y' WHERE layout_id = ?i", $layout_data['layout_id']);

        return array(CONTROLLER_STATUS_OK, "exim.export?section=$_REQUEST[section]&pattern_id=$layout_data[pattern_id]");
    }

    //
    // Store layout
    //
    if ($mode == 'store_layout') {

        if (!empty($layout_data['cols'])) {
            $layout_data['cols'] = implode(',', $layout_data['cols']);

            // Update current layout
            if ($action == 'save_as') {
                unset($layout_data['layout_id']);
                if (!empty($layout_data['name'])) {
                    $layout_data['active'] = 'Y';
                    $layout_data['options'] = serialize($_REQUEST['export_options']);
                    db_query("UPDATE ?:exim_layouts SET active = 'N' WHERE pattern_id = ?s", $layout_data['pattern_id']);
                    db_query("INSERT INTO ?:exim_layouts ?e", $layout_data);

                    return array(CONTROLLER_STATUS_OK, "exim.export?section=$_REQUEST[section]&pattern_id=$layout_data[pattern_id]");
                }
            } else {
                if (!empty($layout_data['layout_id'])) {
                    unset($layout_data['name']);
                    db_query("UPDATE ?:exim_layouts SET ?u WHERE layout_id = ?i", $layout_data, $layout_data['layout_id']);
                }
            }
        }

        return array(CONTROLLER_STATUS_OK, "exim.export?section=$_REQUEST[section]&pattern_id=$layout_data[pattern_id]");
    }

    //
    // Delete layout
    //
    if ($mode == 'delete_layout') {
        db_query("DELETE FROM ?:exim_layouts WHERE layout_id = ?i", $layout_data['layout_id']);

        return array(CONTROLLER_STATUS_OK, "exim.export?section=$_REQUEST[section]&pattern_id=$layout_data[pattern_id]");
    }

    //
    // Perform export
    //
    if ($mode == 'export') {
        $_suffix = '';
        if (!empty($layout_data['cols'])) {
            $pattern = fn_get_pattern_definition($layout_data['pattern_id'], 'export');

            if (empty($pattern)) {
                fn_set_notification('E', __('error'), __('error_exim_pattern_not_found'));
                exit;
            }

            if (!empty($_SESSION['export_ranges'][$pattern['section']])) {
                if (empty($pattern['condition']['conditions'])) {
                    $pattern['condition']['conditions'] = array();
                }

                $pattern['condition']['conditions'] = fn_array_merge($pattern['condition']['conditions'], $_SESSION['export_ranges'][$pattern['section']]['data']);
            }

            if (fn_export($pattern, $layout_data['cols'], $_REQUEST['export_options']) == true) {

                fn_set_notification('N', __('notice'), __('text_exim_data_exported'));

                // Direct download
                if ($_REQUEST['export_options']['output'] == 'D') {
                    $url = fn_url("exim.get_file?filename=" . $_REQUEST['export_options']['filename'], 'A', 'current');

                // Output to screen
                } elseif ($_REQUEST['export_options']['output'] == 'C') {
                    $url = fn_url("exim.get_file?to_screen=Y&filename=" . $_REQUEST['export_options']['filename'], 'A', 'current');
                }

                if (defined('AJAX_REQUEST') && !empty($url)) {
                    Registry::get('ajax')->assign('force_redirection', $url);

                    exit;
                }

                $url = empty($url) ? fn_url('exim.export?section=' . $_REQUEST['section']) : $url;

                return array(CONTROLLER_STATUS_OK, $url);

            } else {
                fn_set_notification('E', __('error'), __('error_exim_no_data_exported'));
            }
        } else {
            fn_set_notification('E', __('error'), __('error_exim_fields_not_selected'));
        }

        exit;
    }

    //
    // Perform import
    //
    if ($mode == 'import') {
        $file = fn_filter_uploaded_data('csv_file');

        if (!empty($file)) {
            if (empty($_REQUEST['pattern_id'])) {
                fn_set_notification('E', __('error'), __('error_exim_pattern_not_found'));
            } else {
                $pattern = fn_get_pattern_definition($_REQUEST['pattern_id'], 'import');

                if (($data = fn_get_csv($pattern, $file[0]['path'], $_REQUEST['import_options'])) != false) {

                    fn_import($pattern, $data, $_REQUEST['import_options']);
                }
            }
        } else {
            fn_set_notification('E', __('error'), __('error_exim_no_file_uploaded'));
        }

        return array(CONTROLLER_STATUS_OK, "exim.import?section=$_REQUEST[section]&pattern_id=$_REQUEST[pattern_id]");
    }

    exit;
}

if ($mode == 'export') {

    if (empty($_REQUEST['section'])) {
        $_REQUEST['section'] = 'products';
    }

    list($sections, $patterns) = fn_get_patterns($_REQUEST['section'], 'export');

    if (empty($sections) && empty($patterns) || (isset($_REQUEST['section']) && empty($sections[$_REQUEST['section']]))) {
        return array(CONTROLLER_STATUS_DENIED);
    }

    $pattern_id = (empty($_REQUEST['pattern_id']) || empty($patterns[$_REQUEST['pattern_id']])) ? key($patterns) : $_REQUEST['pattern_id'];

    foreach ($patterns as $p_id => $p) {
        Registry::set('navigation.tabs.' . $p_id, array (
            'title' => $p['name'],
            'href' => "exim.export?pattern_id=" . $p_id . '&section=' . $_REQUEST['section'],
            'ajax' => true
        ));
    }

    if (!empty($_SESSION['export_ranges'][$_REQUEST['section']])) {
        $key = key($_SESSION['export_ranges'][$_REQUEST['section']]['data']);
        if (!empty($key)) {
            Registry::get('view')->assign('export_range', count($_SESSION['export_ranges'][$patterns[$pattern_id]['section']]['data'][$key]));
            Registry::get('view')->assign('active_tab', $_SESSION['export_ranges'][$_REQUEST['section']]['pattern_id']);
        }
    }

    // Get available layouts
    $layouts = db_get_array("SELECT * FROM ?:exim_layouts WHERE pattern_id = ?s", $pattern_id);

    // Extract columns information
    foreach ($layouts as $k => $v) {
        $layouts[$k]['cols'] = explode(',', $v['cols']);
        $layouts[$k]['options'] = unserialize($v['options']);

        if ($v['active'] == 'Y' && !empty($v['options'])) {
            foreach ($layouts[$k]['options'] as $option => $value) {
                if (isset($patterns[$pattern_id]['options'][$option])) {
                    $patterns[$pattern_id]['options'][$option]['default_value'] = $value;
                }
            }
        }
    }

    // Get export files
    $export_files = fn_get_dir_contents(fn_get_files_dir_path(), false, true);
    $result = array();

    foreach ($export_files as $file) {
        $result[] = array (
            'name' => $file,
            'size' => filesize(fn_get_files_dir_path() . $file),
        );
    }

    // Export languages
    foreach (fn_get_translation_languages() as $lang_code => $lang_data) {
        $export_langs[$lang_code] = $lang_data['name'];
    }

    Registry::set('navigation.dynamic.sections', $sections);
    Registry::set('navigation.dynamic.active_section', $_REQUEST['section']);

    Registry::get('view')->assign('export_files', $result);
    Registry::get('view')->assign('files_rel_dir', fn_get_rel_dir(fn_get_files_dir_path()));

    Registry::get('view')->assign('pattern', $patterns[$pattern_id]);
    Registry::get('view')->assign('layouts', $layouts);

    Registry::get('view')->assign('export_langs', $export_langs);

} elseif ($mode == 'import') {

    if (empty($_REQUEST['section'])) {
        $_REQUEST['section'] = 'products';
    }

    list($sections, $patterns) = fn_get_patterns($_REQUEST['section'], 'import');

    if (empty($sections) && empty($patterns) || (isset($_REQUEST['section']) && empty($sections[$_REQUEST['section']]))) {
        return array(CONTROLLER_STATUS_DENIED);
    }

    $pattern_id = empty($_REQUEST['pattern_id']) ? key($patterns) : $_REQUEST['pattern_id'];

    foreach ($patterns as $p_id => $p) {
        Registry::set('navigation.tabs.' . $p_id, array (
            'title' => $p['name'],
            'href' => "exim.import?pattern_id=" . $p_id . '&section=' . $_REQUEST['section'],
            'ajax' => true
        ));
    }

    Registry::set('navigation.dynamic.sections', $sections);
    Registry::set('navigation.dynamic.active_section', $_REQUEST['section']);

    unset($patterns[$pattern_id]['options']['lang_code']);
    Registry::get('view')->assign('pattern', $patterns[$pattern_id]);
    Registry::get('view')->assign('sections', $sections);

} elseif ($mode == 'get_file' && !empty($_REQUEST['filename'])) {
    $file = fn_basename($_REQUEST['filename']);

    if (!empty($_REQUEST['to_screen'])) {
        header("Content-type: text/plain");
        readfile(fn_get_files_dir_path() . $file);
        exit;
    } else {
        fn_get_file(fn_get_files_dir_path() . $file);
    }

} elseif ($mode == 'delete_file' && !empty($_REQUEST['filename'])) {
    $file = fn_basename($_REQUEST['filename']);
    fn_rm(fn_get_files_dir_path() . $file);

    return array(CONTROLLER_STATUS_REDIRECT);

} elseif ($mode == 'delete_range') {
    unset($_SESSION['export_ranges'][$_REQUEST['section']]);

    return array(CONTROLLER_STATUS_REDIRECT, "exim.export?section=$_REQUEST[section]&pattern_id=$_REQUEST[pattern_id]");

} elseif ($mode == 'select_range') {
    $_SESSION['export_ranges'][$_REQUEST['section']] = array (
        'pattern_id' => $_REQUEST['pattern_id'],
        'data' => array(),
    );
    $pattern = fn_get_pattern_definition($_REQUEST['pattern_id']);

    return array(CONTROLLER_STATUS_REDIRECT, $pattern['range_options']['selector_url']);

}

// --------- ExIm core functions ------------------

//
// Import data using pattern
// Parameters:
// @pattern - import/export pattern
// @import_data - data to import
// @options - import options

function fn_import($pattern, $import_data, $options)
{
    if (empty($pattern) || empty($import_data)) {
        return false;
    }

    $processed_data = array (
        'E' => 0, // existent
        'N' => 0, // new
        'S' => 0, // skipped
    );

    $alt_keys = array();
    $primary_fields = array();
    $table_groups = array();
    $default_groups = array();
    $add_fields = array();
    $primary_object_ids = array();
    $required_fields = array();
    $alt_fields = array();

    if (!empty($pattern['pre_processing'])) {
        $data_pre_processing = array(
            'import_data' => &$import_data,
            'pattern' => &$pattern,
        );
        fn_exim_processing('import', $pattern['pre_processing'], $options, $data_pre_processing);
    }

    if (!empty($pattern['references'])) {
        $table_groups =  $pattern['references'];
    }

    if (!fn_import_parse_languages($pattern, $import_data, $options)) {
        fn_set_notification('E', __('error'), __('error_exim_invalid_count_langs'));

        return false;
    }

    // Get keys to detect primary record
    foreach ($pattern['export_fields'] as $field => $data) {

        $_db_field = (empty($data['db_field']) ? $field : $data['db_field']);

        // Collect fields with default values
        if (isset($data['default'])) {
            if (is_array($data['default'])) {
                $default_groups[$_db_field] = call_user_func_array(array_shift($data['default']), $data['default']);
            } else {
                $default_groups[$_db_field] = $data['default'];
            }
        }

        // Get alt keys for primary table
        if (!empty($data['alt_key'])) {
            $alt_keys[$field] = $_db_field;
        }

        if (!empty($data['alt_field'])) {
            $alt_fields[$_db_field] = $data['alt_field'];
        }

        if (!empty($data['required']) && $data['required'] = true) {
            $required_fields[] = $_db_field;
        }

        if (!isset($data['linked']) || $data['linked'] == true) {
            // Get fields for primary table
            if (empty($data['table']) || $data['table'] == $pattern['table']) {
                $primary_fields[$field] = $_db_field;
            }

            // Group fields by tables
            if (!empty($data['table'])) {
                $table_groups[$data['table']]['fields'][$_db_field] = true;
            }
        }

        // Create set with fields that must be added to data import if they are not exist
        // %'s are for compatibility with %% field type in "process_put" key
        if (!empty($data['use_put_from'])) {
            $_f = str_replace('%', '', $data['use_put_from']);
            $_f = !empty($pattern['export_fields'][$_f]['db_field']) ? $pattern['export_fields'][$_f]['db_field'] : $_f;
            $add_fields[$_f][] = $_db_field;
        }
    }

    // Generate processing groups
    $processing_groups = fn_import_build_groups('process_put', $pattern['export_fields']);

    // Generate converting groups
    $converting_groups = fn_import_build_groups('convert_put', $pattern['export_fields']);

    //Generate pre inserting groups
    $pre_inserting_groups = fn_import_build_groups('pre_insert', $pattern['export_fields']);

    //Generate post inserting groups
    $post_inserting_groups = fn_import_build_groups('post_insert', $pattern['export_fields']);

    fn_set_progress('parts', sizeof($import_data));

    $data = reset($import_data);
    $multi_lang = array_keys($data);
    $main_lang = reset($multi_lang);

    foreach ($import_data as $k => $v) {

        //If the required field is empty skip this record
        foreach ($required_fields as $field) {
            if (empty($v[$main_lang][$field]) && $v[$main_lang][$field] !== 0) {
                if (empty($alt_fields[$field]) || empty($v[$main_lang][$alt_fields[$field]])) {
                    $processed_data['S']++;
                    continue 2;
                }
            }
        }

        $_alt_keys = array();
        $object_exists = true;

        // Check if converting groups exist and convert fields if it is so
        fn_import_prepare_groups($v[$main_lang], $converting_groups, $options);

        foreach ($alt_keys as $import_field => $real_field) {
            if (!isset($v[$main_lang][$real_field])) {
                continue;
            }
            if (!empty($v[$main_lang][$real_field])) {
                $_alt_keys[$real_field] = $v[$main_lang][$real_field];
            } elseif (!empty($alt_fields[$real_field])) {
                $_alt_keys[$alt_fields[$real_field]] = $v[$main_lang][$alt_fields[$real_field]];
            }

        }

        foreach ($primary_fields as $import_field => $real_field) {
            if (!isset($v[$main_lang][$real_field])) {
                continue;
            }
            $_primary_fields[$real_field] = $v[$main_lang][$real_field];
        }

        $skip_get_primary_object_id = false;

        if (!empty($pattern['import_get_primary_object_id'])) {
            $data_import_get_primary_object_id = array(
                'pattern' => &$pattern,
                'alt_keys' => &$_alt_keys,
                'object' => &$v[$main_lang],
                'skip_get_primary_object_id' => &$skip_get_primary_object_id,
            );

            fn_exim_processing('import', $pattern['import_get_primary_object_id'], $options, $data_import_get_primary_object_id);
        }

        if ($skip_get_primary_object_id) {
            $primary_object_id = array();
        } else {
            $where = array();
            foreach ($_alt_keys as $field => $value) {
                $where[] = db_quote("?p = ?s", $field, $value);
            }
            $where = implode(' AND ', $where);

            $primary_object_id = db_get_row('SELECT ' . implode(', ', $pattern['key']) . ' FROM ?:' . $pattern['table'] . ' WHERE ?p', $where);
        }

        $primary_object_ids[] = $primary_object_id;
        $skip_record = false;

        if (!empty($pattern['import_process_data'])) {
            $data_import_process_data = array(
                'primary_object_id' => &$primary_object_id,
                'object' => &$v[$main_lang],
                'pattern' => &$pattern,
                'options' => &$options,
                'processed_data' => &$processed_data,
                'processing_groups' => &$processing_groups,
                'skip_record' => &$skip_record,
                'data' =>&$v,
            );

            fn_exim_processing('import', $pattern['import_process_data'], $options, $data_import_process_data);
        }

        fn_import_prepare_groups($v[$main_lang], $pre_inserting_groups, $options, $skip_record);

        if ($skip_record) {
            continue;
        }

        if (!(isset($pattern['import_skip_db_processing']) && $pattern['import_skip_db_processing'])) {

            fn_set_progress('echo', __('importing_data'));
            if (empty($primary_object_id)) {

                // If scheme is used for update objects only, skip this record
                if (!empty($pattern['update_only'])) {
                    $_a = array();
                    foreach ($alt_keys as $_d => $_v) {
                        if (!isset($v[$main_lang][$_v])) {
                            continue;
                        }
                        $_a[] = $_d . ' = ' . $v[$_v];
                    }
                    fn_set_progress('echo', __('object_does_not_exist') . ' (' . implode(', ', $_a) . ')...', false);

                    $processed_data['S']++;
                    continue;
                }

                $object_exists = false;
                $processed_data['N']++;

                // For new objects - fill the default values
                if (!empty($default_groups)) {
                    foreach ($default_groups as $field => $value) {
                        if (empty($v[$main_lang][$field])) {
                            $v[$main_lang][$field] = $value;
                        }
                    }
                }
            } else {
                $processed_data['E']++;
            }

            /*foreach ($pattern['key'] as $field) {
                unset($v[$main_lang][$field]);
            }*/
            if ($object_exists == true) {
                fn_set_progress('echo', __('updating') . ' ' . $pattern['name'] . ' <b>' . implode(',', $primary_object_id) . '</b>. ', false);
                db_query('UPDATE ?:' . $pattern['table'] . ' SET ?u WHERE ?w', $v[$main_lang], $primary_object_id);
            } else {
                $o_id = db_query('INSERT INTO ?:' . $pattern['table'] . ' ?e', $v[$main_lang]);

                if ($o_id !== true) {
                    $primary_object_id = array(reset($pattern['key']) => $o_id);
                } else {
                    foreach ($pattern['key'] as $_v) {
                        $primary_object_id[$_v] = $v[$main_lang][$_v];
                    }
                }

                fn_set_progress('echo', __('creating') . ' ' . $pattern['name'] . ' <b>' . implode(',', $primary_object_id) . '</b>. ', false);

            }
        }

        $skip_db_processing_record = false;

        fn_import_prepare_groups($v[$main_lang], $post_inserting_groups, $options, $skip_db_processing_record);

        if (!empty($pattern['import_after_process_data'])) {
            $data_import_after_process_data = array(
                'primary_object_id' => &$primary_object_id,
                'object' => &$v[$main_lang],
                'pattern' => &$pattern,
                'options' => &$options,
                'processed_data' => &$processed_data,
                'processing_groups' => &$processing_groups,
                'skip_db_processing_record' => &$skip_db_processing_record,
            );

            fn_exim_processing('import', $pattern['import_after_process_data'], $options, $data_import_after_process_data);
        }

        if ($skip_db_processing_record) {
            continue;
        }



        if (!(isset($pattern['import_skip_db_processing']) && $pattern['import_skip_db_processing'])) {
            // Update referenced tables
            fn_set_progress('echo', __('updating_links') . '... ', false);

            foreach ($table_groups as $table => $tdata) {
                if (isset($tdata['import_skip_db_processing']) && $tdata['import_skip_db_processing']) {
                    continue;
                }

                foreach ($v as $value_data) {
                    $_data = array();

                    // First, build condition
                    $where_insert = array();

                    // If alternative key is defined, use it
                    if (!empty($tdata['alt_key'])) {

                        foreach ($tdata['alt_key'] as $akey) {
                            if (strval($akey) == '#key') {
                                $where_insert = fn_array_merge($where_insert, $primary_object_id);
                            } elseif (strpos($akey, '@') !== false) {
                                $_opt = str_replace('@', '', $akey);
                                $where_insert[$akey] = $options[$_opt];
                            } else {
                                $where_insert[$akey] = $value_data[$akey];
                            }
                        }
                    // Otherwise - link by reference fields
                    } else {
                        $vars = array('key' => $primary_object_id);
                        if (!empty($value_data['lang_code'])) {
                            $vars['lang_code'] = $value_data['lang_code'];
                        }
                        $where_insert = fn_exim_get_values($tdata['reference_fields'], array(), $options, $vars, array(), '');
                    }

                    // Now, build update fields array
                    if (!empty($tdata['fields'])) {
                        foreach ($tdata['fields'] as $import_field => $set) {
                            if (!isset($value_data[$import_field])) {
                                continue;
                            }
                            $_data[$import_field] = $value_data[$import_field];
                        }
                    }

                    // Check if object exists
                    $is_exists = db_get_field("SELECT COUNT(*) FROM ?:$table WHERE ?w", $where_insert);
                    if ($is_exists == true && !empty($_data)) {
                        db_query("UPDATE ?:$table SET ?u WHERE ?w", $_data, $where_insert);
                    } elseif (empty($is_exists)) { // if reference does not exist, we should insert it anyway to avoid inconsistency
                        $_data = fn_array_merge($_data, $where_insert);

                        if (substr($table, -13) == '_descriptions' && isset($_data['lang_code'])) {
                            // add description for all cart languages when adding object data
                            foreach (fn_get_translation_languages() as $_data['lang_code'] => $lang_v) {
                                db_query("REPLACE INTO ?:$table ?e", $_data);
                            }

                        } else {
                            db_query("INSERT INTO ?:$table ?e", $_data);
                        }
                    }

                    //
                    if (empty($_data['lang_code'])) {
                        break;
                    }
                }
            }
        }

        if (!empty($processing_groups)) {

            foreach ($processing_groups as $group) {

                $args = array();
                $use_this_group = true;
                $_refs = array();

                foreach ($group['args'] as $ak => $av) {

                    foreach ($v as $lang_code => $value) {
                        if ($av == '#key') {
                            $args[$ak] = (sizeof($primary_object_id) >= 1) ? reset($primary_object_id) : $primary_object_id;

                        } elseif ($av == '#keys') {
                            $args[$ak] = is_array($primary_object_id) ? $primary_object_id : (array)$primary_object_id;

                        } elseif ($av == '#new') {
                            $args[$ak] = !$object_exists;

                        } elseif ($av == '#lang_code') {
                            $args[$ak] = $lang_code;

                        } elseif ($av == '#row') {
                            $args[$ak] = $value;

                        } elseif ($av == '#this') {
                            // If we do not have this field in the import data, do not apply the function
                            $this_id = $group['this_field'];

                            if (!isset($value[$this_id])) {
                                $is_empty_data = true;

                                if (!empty($add_fields[$this_id])) {
                                    foreach ($add_fields[$this_id] as $from_field) {
                                        if (isset($value[$from_field])) {
                                            $is_empty_data = false;
                                        }
                                    }
                                }

                                if ($is_empty_data) {
                                    $use_this_group = false;
                                    break;
                                }
                            }

                            $this_multilang = false;

                            if (!empty($pattern['export_fields'][$this_id]['multilang'])) {
                                $this_multilang = true;
                            } else {
                                foreach ($pattern['export_fields'] as $field) {
                                    if (!empty($field['multilang']) && !empty($field['db_field']) && $field['db_field'] == $this_id) {
                                        $this_multilang = true;
                                        break;
                                    }
                                }
                            }

                            if ($this_multilang) {
                                $args[$ak][$lang_code] = $value[$group['this_field']];
                            } else {
                                $args[$ak] = $value[$group['this_field']];
                                break;
                            }

                        } elseif ($av == '#counter') {
                            $args[$ak] = &$processed_data;

                        } elseif (strpos($av, '%') !== false) {
                            $_ref = str_replace('%', '', $av);
                            $arg_multilang = !empty($pattern['export_fields'][$_ref]['multilang']);
                            $_ref = !empty($pattern['export_fields'][$_ref]['db_field']) ? $pattern['export_fields'][$_ref]['db_field'] : $_ref; // FIXME!!! Move to code, which builds processing_groups

                            if ($arg_multilang) {
                                $args[$ak][$lang_code] = isset($value[$_ref]) ? $value[$_ref] : '';
                            } elseif ($lang_code == $main_lang) {
                                $args[$ak] = isset($value[$_ref]) ? $value[$_ref] : '';
                            }

                            $_refs[$lang_code][] = $_ref;

                        } elseif (strpos($av, '@') !== false) {
                            $_opt = str_replace('@', '', $av);
                            $args[$ak] = $options[$_opt];

                        } else {
                            $args[$ak] = $av;
                        }

                        if (empty($group['multilang'])) {
                            break;
                        }
                    }

                }

                if ($use_this_group == false) {
                    continue;
                }

                $result = call_user_func_array($group['function'], $args); // FIXME - add checking for returned value

                if ($group['return_result'] == true) {
                    foreach (array_keys($v) as $lang) {
                        $v[$lang][$group['this_field']] = $result;
                    }
                } else {
                    // Remove processed fields from table groups
                    if (!empty($group['table'])) {
                        unset($table_groups[$group['table']]['fields'][$group['this_field']]);

                        if (!empty($_refs[$main_lang])) {
                            foreach ($_refs[$main_lang] as $_ref) {
                                unset($table_groups[$group['table']]['fields'][$_ref]);
                            }
                        }
                    }
                }

            }
        }
    }

    if (!empty($pattern['post_processing'])) {
        $data_post_processing = array(
            'primary_object_ids' => &$primary_object_ids,
            'import_data' => &$import_data,
        );

        fn_exim_processing('import', $pattern['post_processing'], $options, $data_post_processing);
    }

    fn_set_notification('W', __('important'), __('text_exim_data_imported', array(
        '[new]' => $processed_data['N'],
        '[exist]' => $processed_data['E'],
        '[skipped]' => $processed_data['S'],
        '[total]' => $processed_data['E'] + $processed_data['N'] + $processed_data['S']
    )));

    return true;
}

//
// Export data using pattern
// Parameters:
// @pattern - import/export pattern
// @export_fields - export defined fields only
// @options - export options
// FIXME: add export conditions

function fn_export($pattern, $export_fields, $options)
{
    if (empty($pattern) || empty($export_fields)) {
        return false;
    }

    // Languages
    if (!empty($options['lang_code'])) {
        $multi_lang = $options['lang_code'];
        $count_langs = count($multi_lang);
    } else {
        $multi_lang = array(DEFAULT_LANGUAGE);
        $count_langs = 1;
        $options['lang_code'] = $multi_lang;
    }

    $can_continue = true;

    if (!empty($pattern['export_pre_moderation'])) {
        $data_export_pre_moderation = array(
            'pattern' => &$pattern,
            'export_fields' => &$export_fields,
            'options' => &$options,
            'can_continue' => &$can_continue,
        );

        fn_exim_processing('export', $pattern['export_pre_moderation'], $options, $data_export_pre_moderation);
    }

    if (!$can_continue) {
        return false;
    }

    if (!empty($pattern['pre_processing'])) {
        fn_exim_processing('export', $pattern['pre_processing'], $options);
    }

    if (isset($options['fields_names'])) {
        if ($options['fields_names']) {
            $fields_names = $export_fields;
            $export_fields = array_keys($export_fields);
        }
    }

    $primary_key = array();
    $_primary_key = $pattern['key'];
    foreach ($_primary_key as $key) {
        $primary_key[$key] = $key;
    }
    array_walk($primary_key, 'fn_attach_value_helper', $pattern['table'].'.');

    $table_fields = $primary_key;
    $processes = array();

    // Build list of fields that should be retrieved from the database
    fn_export_build_retrieved_fields($processes, $table_fields, $pattern, $export_fields);

    if (empty($pattern['export_fields']['multilang'])) {
        $multi_lang = array(DEFAULT_LANGUAGE);
        $count_langs = 1;
        $options['lang_code'] = $multi_lang;
    }

    // Build the list of joins
    $joins = fn_export_build_joins($pattern, $options, $primary_key, $multi_lang);

    // Add retrieve conditions
    $conditions = fn_export_build_conditions($pattern, $options);

    if (!empty($pattern['pre_export_process'])) {
        $pre_export_process_data = array(
            'pattern' => &$pattern,
            'export_fields' => &$export_fields,
            'options' => &$options,
            'conditions' => &$conditions,
            'joins' => &$joins,
            'table_fields' => &$table_fields,
            'processes' => &$processes
        );
        fn_exim_processing('export', $pattern['pre_export_process'], $options, $pre_export_process_data);
    }

    $total = db_get_field("SELECT COUNT(*) FROM ?:" . $pattern['table'] . " as " . $pattern['table'] .' '. implode(' ', $joins) . (!empty($conditions) ? ' WHERE ' . implode(' AND ', $conditions) : ''));

    fn_set_progress('parts', $total);
    fn_set_progress('step_scale', 1);

    $sorting = '';

    if (!empty($pattern['order_by'])) {
        $sorting = ' ORDER BY ' . $pattern['order_by'];
    }

    // Build main query
    $query = "SELECT " . implode(', ', $table_fields) . " FROM ?:" . $pattern['table'] . " as " . $pattern['table'] .' '. implode(' ', $joins) . (!empty($conditions) ? ' WHERE ' . implode(' AND ', $conditions) : '') . $sorting;

    $step = fn_floor_to_step(DB_LIMIT_SELECT_ROW,  $count_langs); // define number of rows to get from database
    $iterator = 0; // start retrieving from
    $progress = 0;
    $data_exported = false;

    $main_lang = reset($multi_lang);
    $manual_multilang = true;

    $field_lang = '';

    foreach ($pattern['export_fields']['multilang'] as $key => $value) {
        if (array_search('languages', $value, true)) {
            if (!isset($value['linked']) || $value['linked'] === true) {
                $manual_multilang = false;
            }
            $field_lang = $key;

            break;
        }
    }

    if (empty($field_lang) || !in_array($field_lang, $export_fields)) {
        $multi_lang = array($main_lang);
        $count_langs = 1;
    }

    while ($data = db_get_array($query . " LIMIT $iterator, $step")) {
        $data_exported = true;

        if ($manual_multilang) {
            $data_lang = $data;
            $data = array();

            foreach ($data_lang as $data_key => $data_value) {
                $data[] = array_combine($multi_lang, array_fill(0, $count_langs, $data_value));
            }

        } else {

            $data_lang = array_chunk($data, $count_langs);
            $data = array();

            foreach ($data_lang as $data_key => $data_value) {
                // Sort
                foreach ($multi_lang as $lang_code) {
                    foreach ($data_value as $v) {
                        if (array_search($lang_code, $v, true)) {
                            $data[$data_key][$lang_code] = $v;
                        }
                    }
                }
            }
        }

        $result = array();
        foreach ($data as $k => $v) {
            $progress += $count_langs;
            fn_set_progress('echo', __('exporting_data') . ':&nbsp;<b>' . ($progress)  .'</b>');
            fn_export_fill_fields($result[$k], $v, $processes, $pattern, $options);
        }

        $_result = array();

        foreach ($result as $k => $v) {
            foreach ($multi_lang as $lang_code) {
                $_data = array();
                foreach ($export_fields as $field) {
                    if (isset($fields_names[$field])) {
                        $_data[$fields_names[$field]] = $v[$lang_code][$field];
                    } else {
                        $_data[$field] = (isset($v[$lang_code][$field])) ? $v[$lang_code][$field] : '';
                    }
                }
                $_result[] = $_data;
            }
        }

        // Put data
        $enclosure = (isset($pattern['enclosure'])) ? $pattern['enclosure'] : '"';
        fn_echo(' .');

        if (isset($pattern['func_save_content_to_file']) && is_callable($pattern['func_save_content_to_file'])) {
            call_user_func($pattern['func_save_content_to_file'], $_result, $options, $enclosure);
        } else {
            fn_put_csv($_result, $options, $enclosure);
        }

        $iterator += $step;
    }

    if (!empty($pattern['post_processing'])) {
        fn_set_progress('echo', __('processing'), false);

        if (file_exists(fn_get_files_dir_path() . $options['filename'])) {

            $data_exported = fn_exim_processing('export', $pattern['post_processing'], $options);
        }
    }

    return $data_exported;
}

//
// Process csv file using pattern
// Parameters:
// @pattern - import/export pattern
// @file - path to csv file on filesystem
// @options - processing options

function fn_get_csv($pattern, $file, $options)
{
    $max_line_size = 65536; // 64 Кб
    $result = array();

    if ($options['delimiter'] == 'C') {
        $delimiter = ',';
    } elseif ($options['delimiter'] == 'T') {
        $delimiter = "\t";
    } else {
        $delimiter = ';';
    }

    if (!empty($file) && file_exists($file)) {

        $encoding = fn_detect_encoding($file, 'F', !empty($options['lang_code']) ? $options['lang_code'] : CART_LANGUAGE);

        if (!empty($encoding)) {
             $file = fn_convert_encoding($encoding, 'UTF-8', $file, 'F');
        } else {
            fn_set_notification('W', __('warning'), __('text_exim_utf8_file_format'));
        }

        $f = false;
        if ($file !== false) {
            $f = fopen($file, 'rb');
        }

        if ($f) {
            // Get import schema
            $import_schema = fgetcsv($f, $max_line_size, $delimiter);
            if (empty($import_schema)) {
                fn_set_notification('E', __('error'), __('error_exim_cant_read_file'));

                return false;
            }

            // Check if we selected correct delimiter
            // If line was read without delimition, array size will be == 1.
            if (sizeof($import_schema) == 1) {

                // we could export one column if it is correct, otherwise show error
                if (!in_array($import_schema[0], array_keys($pattern['export_fields']))) {

                    fn_set_notification('E', __('error'), __('error_exim_incorrent_delimiter'));

                    return false;
                }
            }

            // Analyze schema - check for required fields
            if (fn_analyze_schema($import_schema, $pattern) == false) {
                return false;
            }

            // Collect data
            $schema_size = sizeof($import_schema);
            $skipped_lines = array();
            $line_it = 1;
            while (($data = fn_fgetcsv($f, $max_line_size, $delimiter)) !== false) {

                $line_it ++;
                if (fn_is_empty($data)) {
                    continue;
                }

                if (sizeof($data) != $schema_size) {
                    $skipped_lines[] = $line_it;
                    continue;
                }

                $result[] = array_combine($import_schema, Bootstrap::stripSlashes($data));
            }

            if (!empty($skipped_lines)) {
                fn_set_notification('W', __('warning'), __('error_exim_incorrect_lines', array(
                    '[lines]' => implode(', ', $skipped_lines)
                )));
            }

            return $result;
        } else {
            fn_set_notification('E', __('error'), __('error_exim_cant_open_file'));

            return false;
        }
    } else {
        fn_set_notification('E', __('error'), __('error_exim_file_doesnt_exist'));

        return false;
    }
}

//
// Analyze import schema and convert fields using pattern
// Parameters:
// @schema - import schema
// @pattern - import/export pattern

function fn_analyze_schema(&$schema, $pattern)
{
    $failed_fields = array();
    $schema_match = false;
    array_walk($schema, 'fn_trim_helper');

    foreach ($pattern['export_fields'] as $field => $data) {

        if (!empty($data['required']) && $data['required'] == true && !in_array($field, $schema)) {
            if (empty($data['db_field']) || $data['db_field'] != 'lang_code') {
                $failed_fields[] = $field;
            }
        }

        if (in_array($field, $schema)) {
            $schema_match = true;
        }

        // Replace fields aliases with database representation
        if (!empty($data['db_field'])) {
            $key = array_search($field, $schema);
            if ($key !== false) {
                $schema[$key] = $data['db_field'];
            }
        }
    }

    if (!empty($failed_fields)) {
        fn_set_notification('E', __('error'), __('error_exim_pattern_required_fields', array(
            '[fields]' => implode(', ', $failed_fields)
        )));

        return false;
    }

    if ($schema_match == false) {
        fn_set_notification('E', __('error'), __('error_exim_pattern_dont_match'));

        return false;
    }

    return true;
}

//
// Put data to csv file
// Parameters:
// @data - export data
// @options - options

function fn_put_csv(&$data, &$options, $enclosure)
{
    static $output_started = false;

    $eol = "\n";

    if ($options['delimiter'] == 'C') {
        $delimiter = ',';
    } elseif ($options['delimiter'] == 'T') {
        $delimiter = "\t";
    } else {
        $delimiter = ';';
    }

    fn_mkdir(fn_get_files_dir_path());

    foreach ($data as $k => $v) {
        foreach ($v as $name => $value) {
            $data[$k][$name] = $enclosure . str_replace(array("\r","\n","\t",$enclosure), array('','','',$enclosure.$enclosure), $value) . $enclosure;
        }
    }

    if ($output_started == false || isset($options['force_header'])) {
        Registry::get('view')->assign('fields', array_keys($data[0]));
    } else {
        Registry::get('view')->clearAssign('fields');
    }

    Registry::get('view')->assign('export_data', $data);
    Registry::get('view')->assign('delimiter', $delimiter);
    Registry::get('view')->assign('eol', $eol);

    $csv = Registry::get('view')->fetch('views/exim/components/export_csv.tpl');
    $fd = fopen(fn_get_files_dir_path() . $options['filename'], ($output_started && !isset($options['force_header'])) ? 'ab' : 'wb');
    if ($fd) {
        fwrite($fd, $csv, strlen($csv));
        fclose($fd);
        @chmod(fn_get_files_dir_path() . $options['filename'], DEFAULT_FILE_PERMISSIONS);
    }

    if ($output_started == false) {
        $output_started = true;
    }

    unset($options['force_header']);

    return true;
}

//
// Helper function: attaches prefix to value
//
function fn_attach_value_helper(&$value, $key, $attachment)
{
    $value = $attachment . $value;

    return true;
}


// -------------- ExIm utility functions ---------------------

/**
 * Export image (moves to selected directory on filesystem)
 *
 * @param int $image_id ID of the image
 * @param string $object object to export image for (product, category, etc...)
 * @param string $backup_path path to export image
 * @return string path to the exported image
 */
function fn_export_image($image_id, $object, $backup_path = '')
{
    if (empty($backup_path)) {
        $backup_path = 'exim/backup/images/' . $object . '/';
    }

    $backup_path = rtrim(fn_normalize_path($backup_path), '/');
    $images_path = fn_get_files_dir_path() . $backup_path;

    // if backup dir does not exist then try to create it
    fn_mkdir($images_path);

    $image_data = db_get_row("SELECT image_id, image_path FROM ?:images WHERE image_id = ?i", $image_id);
    if (empty($image_data)) {
        return '';
    }

    $alt_data = db_get_hash_single_array("SELECT lang_code, description FROM ?:common_descriptions WHERE ?:common_descriptions.object_id = ?i AND ?:common_descriptions.object_holder = 'images'", array('lang_code', 'description'), $image_id);
    $alt_text = '{';
    if (!empty($alt_data)) {
        foreach ($alt_data as $lang_code => $text) {
            $alt_text .= '[' . $lang_code . ']:' . $text . ';';
        }
    }
    $alt_text .= '}';

    $path = $images_path . '/' . fn_basename($image_data['image_path']);

    Storage::instance('images')->export($object . '/' . floor($image_id / MAX_FILES_IN_DIR) . '/' . $image_data['image_path'], $path);

    return ($backup_path . '/' . fn_basename($image_data['image_path'])) . (!empty($alt_data) ? '#' . $alt_text : '');
}

/**
 * Import image pair
 *
 * @param string $prefix path prefix
 * @param string $image_file thumbanil path or filename
 * @param string $detailed_path detailed image path or filename
 * @param string $position image position
 * @param string $type pair type
 * @param int $object_id ID of object to attach images to
 * @param string $object name of object to attach images to
 * @return array|bool True if images were imported
 */
function fn_import_images($prefix, $image_file, $detailed_file, $position, $type, $object_id, $object)
{
    static $updated_products = array();

    if (!empty($object_id)) {
        if (empty($updated_products[$object_id]) && !empty($_REQUEST['import_options']['remove_images']) && $_REQUEST['import_options']['remove_images'] == 'Y') {
            $updated_products[$object_id] = true;

            fn_delete_image_pairs($object_id, $object, 'A');
        }

        $_REQUEST["server_import_image_icon"] = '';
        $_REQUEST["type_import_image_icon"] = '';

        // Get image alternative text if exists
        if (!empty($image_file) && strpos($image_file, '#') !== false) {
            list ($image_file, $image_alt) = explode('#', $image_file);
        }

        if (!empty($detailed_file) && strpos($detailed_file, '#') !== false) {
            list ($detailed_file, $detailed_alt) = explode('#', $detailed_file);
        }

        if (!empty($image_alt)) {
            preg_match_all('/\[([A-Za-z]+?)\]:(.*?);/', $image_alt, $matches);
            if (!empty($matches[1]) && !empty($matches[2])) {
                $image_alt = array_combine(array_values($matches[1]), array_values($matches[2]));
            }
        }

        if (!empty($detailed_alt)) {
            preg_match_all('/\[([A-Za-z]+?)\]:(.*?);/', $detailed_alt, $matches);
            if (!empty($matches[1]) && !empty($matches[2])) {
                $detailed_alt = array_combine(array_values($matches[1]), array_values($matches[2]));
            }
        }

        $type_image_detailed = (strpos($detailed_file, '://') === false) ? 'server' : 'url';
        $type_image_icon = (strpos($image_file, '://') === false) ? 'server' : 'url';

        $_REQUEST["type_import_image_icon"] = array($type_image_icon);
        $_REQUEST["type_import_image_detailed"] = array($type_image_detailed);

        $image_file = fn_find_file($prefix, $image_file);

        if ($image_file !== false) {
            if ($type_image_detailed == 'url') {
                $_REQUEST["file_import_image_icon"] = array($image_file);

            } elseif (strpos($image_file, Registry::get('config.dir.root')) === 0) {
                $_REQUEST["file_import_image_icon"] = array(str_ireplace(fn_get_files_dir_path(), '', $image_file));

            } else {
                fn_set_notification('E', __('error'), __('error_images_need_located_root_dir'));
                $_REQUEST["file_import_image_detailed"] = array();
            }
        } else {
            $_REQUEST["file_import_image_icon"] = array();
        }

        $detailed_file = fn_find_file($prefix, $detailed_file);

        if ($detailed_file !== false) {
            if ($type_image_detailed == 'url') {
                $_REQUEST["file_import_image_detailed"] = array($detailed_file);

            } elseif (strpos($detailed_file, Registry::get('config.dir.root')) === 0) {
                $_REQUEST["file_import_image_detailed"] = array(str_ireplace(fn_get_files_dir_path(), '', $detailed_file));

            } else {
                fn_set_notification('E',  __('error'), __('error_images_need_located_root_dir'));
                $_REQUEST["file_import_image_detailed"] = array();
            }

        } else {
            $_REQUEST["file_import_image_detailed"] = array();
        }

        $_REQUEST['import_image_data'] = array(
            array(
                'type' => $type,
                'image_alt' => empty($image_alt) ? '' : $image_alt,
                'detailed_alt' => empty($detailed_alt) ? '' : $detailed_alt,
                'position' => empty($position) ? 0 : $position,
            )
        );

        return fn_attach_image_pairs('import', $object, $object_id);
    }

    return false;
}

//
// Converts timestamp to human-readable date
//

function fn_timestamp_to_date($timestamp)
{
    return date('d M Y H:i:s', intval($timestamp));
}

//
// Converts human-readable date to timestamp
//

function fn_date_to_timestamp($date)
{
    return strtotime($date);
}

//
// Get absolute url to the image
// Parameters:
// @image_id - Id of image
// @object_type - type of image object

function fn_exim_get_image_url($product_id, $object_type, $pair_type, $get_icon, $get_detailed, $lang_code)
{
    $image_pair = fn_get_image_pairs($product_id, $object_type, $pair_type, true, true, $lang_code);
    $image_data = fn_image_to_display($image_pair, Registry::get('settings.Thumbnails.product_details_thumbnail_width'), Registry::get('settings.Thumbnails.product_details_thumbnail_height'));

    return !empty($image_data) ? $image_pair['detailed']['http_image_path'] : '';
}

//
// Get absolute url to the detailed image
// Parameters:
// @image_id - Id of image
// @object_type - type of image object

function fn_exim_get_detailed_image_url($product_id, $object_type, $pair_type, $lang_code)
{
    $image_pair = fn_get_image_pairs($product_id, $object_type, $pair_type, false, true, $lang_code);

    return !empty($image_pair['detailed']['http_image_path']) ? $image_pair['detailed']['http_image_path'] : '';
}

//
// Get pattern definition by its id
// Parameters:
// @pattern_id - pattern ID
function fn_get_pattern_definition($pattern_id, $get_for = '')
{
    // First, check basic patterns
    $schema = fn_get_schema('exim', $pattern_id);

    if (empty($schema)) {
        fn_set_notification('E', __('error'), __('error_exim_pattern_not_found'));

        return false;
    }

    if ((!empty($schema['export_only']) && $get_for == 'import') || (!empty($schema['import_only']) && $get_for == 'export')) {
        return array();
    }

    $has_alt_keys = false;

    foreach ($schema['export_fields'] as $field_id => $field_data) {
        if (!empty($field_data['table'])) {
            // Table exists in export fields, but doesn't exist in references definition
            if (empty($schema['references'][$field_data['table']])) {
                fn_set_notification('E', __('error'), __('error_exim_pattern_definition_references'));

                return false;
            }
        }

        // Check if schema has alternative keys to import basic data
        if (!empty($field_data['alt_key'])) {
            $has_alt_keys = true;
        }

        if ((!empty($field_data['export_only']) && $get_for == 'import') || (!empty($field_data['import_only']) && $get_for == 'export')) {
            unset($schema['export_fields'][$field_id]);
        }
    }

    if ($has_alt_keys == false) {
        fn_set_notification('E', __('error'), __('error_exim_pattern_definition_alt_keys'));

        return false;
    }

    return $schema;
}

/**
 * Gets all available patterns for the section
 *
 * @param string $section section to get patterns for
 * @param string $get_for get import or export patterns
 * @return array
 */
function fn_get_patterns($section, $get_for)
{
    // Get core patterns
    $files = fn_get_dir_contents(Registry::get('config.dir.schemas') . 'exim', false, true, '.php');

    foreach (Registry::get('addons') as $addon_name => $addon_data) {
        if ($addon_data['status'] != 'A') {
            continue;
        }

        $schema_dir = Registry::get('config.dir.addons') . $addon_name . '/schemas/exim';
        if (is_dir($schema_dir)) {
            $_files = fn_get_dir_contents($schema_dir, false, true, '.php');
            foreach ($_files as $key => $filename) {
                if (strpos($filename, '.post.php') !== false) {
                    unset($_files[$key]);
                }
            }

            if (!empty($_files)) {
                $files = fn_array_merge($files, $_files, false);
            }
        }
    }

    $patterns = array();
    $sections = array();

    foreach ($files as $schema_file) {
        if (strpos($schema_file, '.functions.') !== false) { // skip functions schema definition
            continue;
        }

        $pattern_id = str_replace('.php', '', $schema_file);
        $pattern = fn_get_pattern_definition($pattern_id, $get_for);

        if (empty($pattern)) {
            continue;
        }

        $sections[$pattern['section']] = array (
            'title' => __($pattern['section']),
            'href' => 'exim.' . Registry::get('runtime.mode') . '?section=' . $pattern['section'],
        );
        if ($pattern['section'] == $section) {
            $patterns[$pattern_id] = $pattern;
        }
    }

    if (Registry::get('runtime.company_id')) {
        $schema = fn_get_permissions_schema('vendor');

        // Check if the selected section is available
        if (isset($schema[$get_for]['sections'][$section]['permission']) && !$schema[$get_for]['sections'][$section]['permission']) {
            return array('', '');
        }

        if (!empty($schema[$get_for]['sections'])) {
            foreach ($schema[$get_for]['sections'] as $section_id => $data) {
                if (isset($data['permission']) && !$data['permission']) {
                    unset($sections[$section_id]);
                }
            }
        }

        if (!empty($schema[$get_for]['patterns'])) {
            foreach ($schema[$get_for]['patterns'] as $pattern_id => $data) {
                if (isset($data['permission']) && !$data['permission']) {
                    unset($patterns[$pattern_id]);
                }
            }
        }
    }

    ksort($sections, SORT_STRING);
    uasort($patterns, 'fn_sort_patterns');

    return array($sections, $patterns);
}

/**
 * Patterns sort function
 *
 * @param array $a scheme array
 * @param array $b scheme array
 * @return int
 */
function fn_sort_patterns($a, $b)
{
    $s1 = isset($a['order']) ? $a['order'] : $a['pattern_id'];
    $s2 = isset($b['order']) ? $b['order'] : $b['pattern_id'];
    if ($s1 == $s2) {
        return 0;
    }

    return ($s1 < $s2) ? -1 : 1;
}

/**
 * Gets product url
 *
 * @param $product_id
 * @param string $lang_code
 * @return bool
 */
function fn_exim_get_product_url($product_id, $lang_code = '')
{
    if (fn_allowed_for('ULTIMATE')) {
        if (Registry::get('runtime.company_id')) {
            $company_id = Registry::get('runtime.company_id');
        } else {
            $company_id = db_get_field('SELECT company_id FROM ?:products WHERE product_id = ?i', $product_id);
        }

        $company_url = '&company_id=' . $company_id;
    } else {
        $company_url = '';
    }

    $url = fn_url('products.view?product_id=' . $product_id . $company_url, 'C', 'http', $lang_code);

    fn_set_hook('exim_get_product_url', $url, $product_id, $options, $lang_code);

    return $url;
}

/**
 * Convert price to it representation with selected decimal separator
 *
 * @param float $price Price
 * @param string $decimals_separator
 * @return string Converted price
 */
function fn_exim_export_price($price, $decimals_separator)
{
    if ($decimals_separator == '.') {
        return $price;
    }

    return str_replace('.', $decimals_separator, $price);
}

/**
 * Convert price to float with dot decimal separator
 *
 * @param float $price Price
 * @param string $decimals_separator
 * @return string Converted price
 */
function fn_exim_import_price($price, $decimals_separator)
{
    if ($decimals_separator == '.') {
        return $price;
    }

    return str_replace($decimals_separator, '.', $price);
}

function fn_exim_processing($type_processing, $processing, $options, $vars = array())
{
    $result = true;

    foreach ($processing as $data) {
        if ((!empty($data['import_only']) && $type_processing == 'export') || (!empty($data['export_only']) && $type_processing == 'import')) {
            continue;
        }

        $args = fn_exim_get_values($data['args'], array(), $options, array(), $vars, '');
        $result = call_user_func_array($data['function'], $args) && $result;
    }

    return $result;
}

function fn_export_build_retrieved_fields(&$processes, &$table_fields, &$pattern, $export_fields)
{
    $_pattern['export_fields']['main'] = $_pattern['export_fields']['multilang'] = array();
    $processes['main'] = $processes['multilang'] = array();

    foreach ($pattern['export_fields'] as $field => $data) {
        if (!in_array($field, $export_fields)) {
            continue;
        }

        // Do no link this field
        if (isset($data['linked']) && $data['linked'] == false) {
            // do something?
        }
        // Primary object table
        elseif (empty($data['table']) || $data['table'] == $pattern['table']) {
            $table_fields[] = $pattern['table'] . '.' . (!empty($data['db_field']) ? $data['db_field'] . ' as "' .$field. '"' : $field);
        // Linked object tables
        } else {
            $table_fields[] = $data['table'] . '.' . (!empty($data['db_field']) ? $data['db_field'] . ' as "' .$field. '"' : $field);
        }

        $type_data = (array_key_exists('multilang', $data)) ? 'multilang' : 'main';

        $_pattern['export_fields'][$type_data][$field] = $data;

        if (!empty($data['process_get'])) {
            $processes[$type_data][$field]['function'] = array_shift($data['process_get']);
            $processes[$type_data][$field]['args'] = $data['process_get'];
        }

    }

    $pattern['export_fields'] = $_pattern['export_fields'];

    return true;
}

function fn_export_build_joins($pattern, $options, $primary_key, $langs)
{
    $joins = array();
    if (!empty($pattern['references'])) {
        foreach ($pattern['references'] as $table => $data) {
            $ref = array();
            $vars = array(
                'key' => $primary_key,
                'lang_code' => $langs,
            );
            $values = fn_exim_get_values($data['reference_fields'], $pattern, $options, $vars);

            foreach ($data['reference_fields'] as $k => $v) {
                $_val = $values[$k];

                if (is_array($_val)) {
                    $ref[] = "$table.$k IN (" . implode(", ", $_val) . ")";
                } else {
                    $ref[] = "$table.$k = $_val";
                }
            }

            $joins[] = $data['join_type'] . ' JOIN ?:' . $table . " as $table ON " . implode(' AND ', $ref);
        }
    }

    return $joins;
}

function fn_export_build_conditions($pattern, $options)
{
    $conditions = array();

    if (!empty($pattern['condition'])) {
        $_cond = array();

        if (!empty($pattern['condition']['conditions'])) {
            $values = fn_exim_get_values($pattern['condition']['conditions'], $pattern, $options);
            foreach ($pattern['condition']['conditions'] as $field => $value) {

                $_val = $values[$field];

                if (strpos($field, '&') !== false) {
                    $_field = substr($field, 1);
                } else {
                    $_field = $pattern['table'] . '.' .$field;
                }

                if (is_array($_val)) {
                    $_val = implode(",", $_val);
                    $_cond[] = "$_field IN ($_val)";

                } else {
                    $_cond[] = "$_field = $_val";
                }
            }
        }

        if (!empty($pattern['condition']['use_company_condition'])) {
            $company_condition = fn_get_company_condition($pattern['table'] . '.company_id', false);
            if (!empty($company_condition)) {
                $_cond[] = $company_condition;
            }
        }

        if (!empty($_cond)) {
            $conditions[] = implode(' AND ', $_cond);
        }
    }

    return $conditions;
}

function fn_exim_set_quotes($value, $quote = "'")
{
    if (is_array($value) && !empty($value)) {
        foreach ($value as $k => $v) {
            $values[$k] = fn_exim_set_quotes($v, $quote);
        }
        $result = $values;
    } elseif (gettype($value) == 'string') {
        $result = $quote . $value . $quote;

    } else {
        $result = $value;
    }

    return $result;
}


function fn_exim_get_values($values, $pattern, $options, $vars = array(), $data = array(), $quote = "'")
{
    $val = array();

    foreach ($values as $field => $value) {

        if (is_array($value)) {
            $val[$field] = fn_exim_set_quotes($value);

        } else {
            $operator = substr($value, 0, 1);

            if ($operator === '@') {
                $opt = str_replace('@', '', $value);
                $val[$field] = (isset($options[$opt])) ? fn_exim_set_quotes($options[$opt], $quote) : '';

            } elseif ($value === '#this') {
                if (!empty($vars['field'])) {
                    $val[$field] = fn_exim_set_quotes($data[$vars['field']], $quote);
                } else {
                    $val[$field] = fn_exim_set_quotes($data[$field], $quote);
                }

            } elseif ($value === '#key') {
                $val[$field] = (sizeof($vars['key']) == 1) ? reset($vars['key']) : (isset($vars['key'][$field]) ? $vars['key'][$field] : $vars['key']);

            } elseif ($operator === '&') {
                $val[$field] = $pattern['table'] . '.' . substr($value, 1);

            } elseif ($value === '#field') {
                if (!empty($vars['field'])) {
                    $val[$field] = $vars['field'];
                } else {
                    $val[$field] = $field;
                }

            } elseif ($value === '#lang_code') {
                $val[$field] = !empty($vars['lang_code']) ? fn_exim_set_quotes($vars['lang_code'], $quote) : '';

            } elseif ($value === '#row') {
                $val[$field] = $data;

            } elseif ($operator === '#') {
                $val[$field] = substr($value, 1);

            } elseif ($operator === '$') {
                $opt = str_replace('$', '', $value);
                if (isset($data[$opt])) {
                    $data[$opt] = fn_exim_set_quotes($data[$opt], $quote);
                    $val[$field] = &$data[$opt];
                } else {
                    $val[$field] = '';
                }

            } else {
                $val[$field] = fn_exim_set_quotes($value, $quote);
            }
        }
    }

    return $val;
}

function fn_export_fill_fields(&$result, $data, $processes, $pattern, $options)
{
    $multi_lang = array_keys($data);
    $main_lang = reset($multi_lang);

    // Filter
    $export_fields_all =  array_merge($pattern['export_fields']['main'], $pattern['export_fields']['multilang']);
    $result[$main_lang] = fn_array_key_intersect($data[$main_lang], $export_fields_all);
    foreach (array_diff($multi_lang, array($main_lang)) as $lang_code) {
        $result[$lang_code] = fn_array_key_intersect($data[$lang_code], $pattern['export_fields']['multilang']);
    }

    foreach ($processes['main'] as $field => $process_data) {
        $vars = array(
            'key' => array($data[$main_lang][reset($pattern['key'])]),
            'field' => $field,
            'lang_code' => $main_lang
        );

        $args = fn_exim_get_values($process_data['args'], $pattern, $options, $vars, $data[$main_lang], '');

        if (!empty($process_data['function'])) {
            $result[$main_lang][$field] = call_user_func_array($process_data['function'], $args);
        } else {
            $result[$main_lang][$field] = array_shift($args);
        }
    }

    foreach ($processes['multilang'] as $field => $process_data) {

        foreach ($multi_lang as $lang_code) {
            $vars = array(
                'key' => array($data[$lang_code][reset($pattern['key'])]),
                'field' => $field,
                'lang_code' => $lang_code,
            );

            $args = fn_exim_get_values($process_data['args'], $pattern, $options, $vars, $data[$lang_code], '');
            //$args[$index_lang] = $lang_code;

            if (!empty($process_data['function'])) {
                $result[$lang_code][$field] = call_user_func_array($process_data['function'], $args);
            } else {
                $result[$lang_code][$field] = array_shift($args);
            }

        }
    }
}

function fn_import_build_groups($type_group, $export_fields)
{
    $groups = array();
    if (!empty($type_group)) {
        foreach ($export_fields as $field => $data) {
            $db_field = (empty($data['db_field']) ? $field : $data['db_field']);
            if (!empty($data[$type_group])) {
                $args = $data[$type_group];
                $function = array_shift($args);
                $groups[] = array (
                    'function' => $function,
                    'this_field' => $db_field,
                    'args' => $args,
                    'table' => !empty($data['table']) ? $data['table'] : '',
                    'multilang' => !empty($data['multilang']) ? true : false,
                    'return_result' => !empty($data['return_result']) ? $data['return_result'] : false,
                );
            }
        }
    }

    return $groups;
}

function fn_import_prepare_groups(&$data, $groups, $options, $skip_record = false)
{
    if (!empty($groups)) {
        foreach ($groups as $group) {
            if (!isset($data[$group['this_field']])) {
                continue;
            }
            $vars = array(
                'lang_code' => !empty($data['lang_code']) ? $data['lang_code'] : '',
                'field' => $group['this_field']
            );

            $params = fn_exim_get_values($group['args'], array(), $options, $vars, $data, '');
            $params[] = & $skip_record;

            $data[$group['this_field']] = call_user_func_array($group['function'], $params);
        }
    }

    return true;
}

function fn_import_parse_languages($pattern, &$import_data, $options)
{

    foreach ($pattern['export_fields'] as $field_name => $field) {
        if (!empty($field['type']) && $field['type'] == 'languages') {
            if (empty($field['db_field'])) {
                $field_lang = $field_name;
            } else {
                $field_lang = $field['db_field'];
            }
        }
    }

    // Languages
    $langs = array();

    // Get all lang from data
    foreach ($import_data as $k => $v) {
        if (!isset($v['lang_code']) || in_array($v['lang_code'], $langs)) {
            break;
        }
        $langs[] = $v['lang_code'];
    }

    if (empty($langs)) {
        foreach ($import_data as $key => $data) {
            $import_data[$key]['lang_code'] = DEFAULT_LANGUAGE;
        }

        $langs[] = DEFAULT_LANGUAGE;
    }

    $langs = array_intersect($langs, array_keys(fn_get_translation_languages()));
    $count_langs = count($langs);

    $count_lang_data = array();
    foreach ($langs as $lang) {
        $count_lang_data[$lang] = 0;
    }

    $data = array();
    $result = true;
    if (isset($field_lang)) {
        foreach ($import_data as $v) {
            if (!empty($v[$field_lang]) && in_array($v[$field_lang], $langs)) {
                $data[] = $v;
                $count_lang_data[$v[$field_lang]]++;
            }
        }

        // Check
        $count_data = reset($count_lang_data);
        foreach ($langs as $lang) {
            if ($count_lang_data[$lang] != $count_data) {
                $result = false;
                break;
            }
        }

        if ($result) {
            // Chunk on languages
            $data_lang = array_chunk($data, $count_langs);
            $data = array();

            foreach ($data_lang as $data_key => $data_value) {
                foreach ($data_value as $v) {
                    $data[$data_key][$v[$field_lang]] = $v;
                }
            }

            if (fn_allowed_for('ULTIMATE')) {
                foreach ($data as $data_key => $data_value) {
                    $data_main = array_shift($data_value);
                    if (empty($data_main['company'])) {
                        $data_main['company'] = Registry::get('runtime.company_data.company');
                    }
                    foreach ($data_value as $v) {
                        $data[$data_key][$v[$field_lang]]['company'] = $data_main['company'];
                    }
                }
            }

            $import_data = $data;
        }
    } else {
        $main_lang = reset($langs);
        foreach ($import_data as $data_key => $data_value) {
            $data[$data_key][$main_lang] = $data_value;
        }
        $import_data = $data;
    }

    return $result;
}

/**
 * Finds file and return real path to it
 *
 * @param string $prefix path to search in
 * @param string $file Filename, can be URL, absolute or relative path
 * @return mixed String path to the file or false if file is not found.
 */
function fn_find_file($prefix, $file)
{
    $file = Bootstrap::stripSlashes($file);

    // Url
    if (strpos($file, '://') !== false) {
        return $file;
    }

    $prefix = fn_normalize_path(rtrim($prefix, '/'));
    $file = fn_normalize_path($file);
    $files_path = fn_get_files_dir_path();

    // Absolute path
    if (is_file($file) && strpos($file, $files_path) === 0) {
        return $file;
    }

    // Path is relative to files directory
    if (is_file($files_path . $file)) {
        return $files_path . $file;
    }

    // Path is relative to prefix inside files directory
    if (is_file($files_path . $prefix . '/' . $file)) {
        return $files_path . $prefix . '/' . $file;
    }

    // Prefix is absolute path
    if (strpos($prefix, $files_path) === 0 && is_file($prefix . '/' . $file)) {
        return $prefix . '/' . $file;
    }

    return false;
}

/**
 * Import company
 *
 * @param string $object_type Type of object ('currencies', 'pages', etc)
 * @param integer $object_id Product ID
 * @param string $company_name Company name
 * @return integer $company_id Company identifier.
 */

function fn_exim_set_company($object_type, $object_key, $object_id, $company_name)
{
    if (empty($company_name) || empty($object_id) || empty($object_type)) {
        return false;
    }

    if (Registry::get('runtime.company_id')) {
        $company_id = Registry::get('runtime.company_id');
    } else {
        $company_id = fn_get_company_id_by_name($company_name);

        if (!$company_id) {
            $company_data = array('company' => $company_name, 'email' => '');
            $company_id = fn_update_company($company_data, 0);
        }
    }

    db_query("UPDATE ?:$object_type SET company_id = ?s WHERE $object_key = ?i", $company_id, $object_id);

    return $company_id;
}
