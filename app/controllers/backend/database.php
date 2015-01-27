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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_define('DB_MAX_ROW_SIZE', 10000);
fn_define('DB_ROWS_PER_PASS', 40);

if ($_SERVER['REQUEST_METHOD']	== 'POST') {

    set_time_limit(3600);

    // Backup database
    if ($mode == 'backup') {

        $dbdump_filename = empty($_REQUEST['dbdump_filename']) ? 'dump_' . date('mdY') . '.sql' : fn_basename($_REQUEST['dbdump_filename']);

        if (!fn_mkdir(Registry::get('config.dir.database'))) {
            fn_set_notification('E', __('error'), __('text_cannot_create_directory', array(
                '[directory]' => fn_get_rel_dir(Registry::get('config.dir.database'))
            )));
            exit;
        }

        $dump_file = Registry::get('config.dir.database') . $dbdump_filename;

        if (is_file($dump_file)) {
            if (!is_writable($dump_file)) {
                fn_set_notification('E', __('error'), __('dump_file_not_writable'));
                exit;
            }
        }

        $dbdump_tables = empty($_REQUEST['dbdump_tables']) ? array() : $_REQUEST['dbdump_tables'];
        $dbdump_schema = !empty($_REQUEST['dbdump_schema']) && $_REQUEST['dbdump_schema'] == 'Y';
        $dbdump_data = !empty($_REQUEST['dbdump_data']) && $_REQUEST['dbdump_data'] == 'Y';

        db_export_to_file($dump_file, $dbdump_tables, $dbdump_schema, $dbdump_data);

        $result = false;
        if ($_REQUEST['dbdump_compress'] == 'Y') {
            fn_set_progress('echo', '<br />' . __('compressing_backup') . '...', false);

            $result = fn_compress_files($dbdump_filename . '.tgz', $dbdump_filename, dirname($dump_file));
            unlink($dump_file);
        }

        if ($result) {
            fn_set_notification('N', __('notice'), __('done'));
        }
    }

    // Restore
    if ($mode == 'restore') {

        if (!empty($_REQUEST['backup_files'])) {
            fn_restore_dump($_REQUEST['backup_files']);
        }

        fn_set_notification('N', __('notice'), __('done'));
    }

    if ($mode == 'm_delete') {
        if (!empty($_REQUEST['backup_files'])) {
            foreach ($_REQUEST['backup_files'] as $file) {
                @unlink(Registry::get('config.dir.database') . fn_basename($file));
            }
        }
    }

    if ($mode == 'upload') {
        $sql_dump = fn_filter_uploaded_data('sql_dump', array('sql', 'tgz'));

        if (!empty($sql_dump)) {
            $sql_dump = array_shift($sql_dump);
            if (fn_copy($sql_dump['path'], Registry::get('config.dir.database') . $sql_dump['name'])) {
                fn_set_notification('N', __('notice'), __('done'));
            } else {
                fn_set_notification('N', __('notice'), __('dump_cant_create_file'));
            }
        } else {
            fn_set_notification('N', __('notice'), __('cant_upload_file'));
        }
    }

    if ($mode == 'optimize') {
        // Log database optimization
        fn_log_event('database', 'optimize');

        $all_tables = db_get_fields("SHOW TABLES");

        fn_set_progress('parts', sizeof($all_tables));

        foreach ($all_tables as $table) {
            fn_set_progress('echo', __('optimizing_table') . "&nbsp;<b>$table</b>...<br />");

            db_query("OPTIMIZE TABLE $table");
            db_query("ANALYZE TABLE $table");
            $fields = db_get_hash_array("SHOW COLUMNS FROM $table", 'Field');

            if (!empty($fields['is_global'])) { // Sort table by is_global field
                fn_echo('.');
                db_query("ALTER TABLE $table ORDER BY is_global DESC");
            } elseif (!empty($fields['position'])) { // Sort table by position field
                fn_echo('.');
                db_query("ALTER TABLE $table ORDER BY position");
            }
        }

        fn_set_notification('N', __('notice'), __('done'));
    }

    return array(CONTROLLER_STATUS_OK, "database.manage");
}

if ($mode == 'getfile' && !empty($_REQUEST['file'])) {
    fn_get_file(Registry::get('config.dir.database') . fn_basename($_REQUEST['file']));

} elseif ($mode == 'manage') {

    Registry::set('navigation.tabs', array (
        'backup' => array (
            'title' => __('backup'),
            'js' => true
        ),
        'restore' => array (
            'title' => __('restore'),
            'js' => true
        ),
        'maintenance' => array (
            'title' => __('maintenance'),
            'js' => true
        ),
    ));

    // Calculate database size and fill tables array
    $status_data = db_get_array("SHOW TABLE STATUS");
    $database_size = 0;
    $all_tables = array();
    foreach ($status_data as $k => $v) {
        $database_size += $v['Data_length'] + $v['Index_length'];
        $all_tables[] = $v['Name'];
    }

    Registry::get('view')->assign('database_size', $database_size);
    Registry::get('view')->assign('all_tables', $all_tables);

    $files = fn_get_dir_contents(Registry::get('config.dir.database'), false, true, array('.sql', '.tgz'), '', true);

    sort($files, SORT_STRING);
    $backup_files = array();
    $date_format = Registry::get('settings.Appearance.date_format'). ' ' . Registry::get('settings.Appearance.time_format');
    if (is_array($files)) {
        foreach ($files as $file) {
            $backup_files[$file]['size'] = filesize(Registry::get('config.dir.database') . $file);
            $backup_files[$file]['type'] = strpos($file, '.tgz')===false ? 'sql' : 'tgz';
            $backup_files[$file]['create'] = fn_date_format(filemtime(Registry::get('config.dir.database') . $file), $date_format);
        }
    }

    Registry::get('view')->assign('backup_files', $backup_files);
    Registry::get('view')->assign('backup_dir', fn_get_rel_dir(Registry::get('config.dir.database')));

} elseif ($mode == 'delete') {
    if (!empty($_REQUEST['backup_file'])) {
        fn_rm(Registry::get('config.dir.database') . fn_basename($_REQUEST['backup_file']));
    }

    return array(CONTROLLER_STATUS_REDIRECT, "database.manage?selected_section=restore");
}

function fn_restore_dump($files)
{
    if (empty($files)) {
        return false;
    }

    fn_set_progress('parts', sizeof($files));

    foreach ($files as $file) {
        $is_archive = false;

        $list = array($file);

        if (in_array(fn_get_file_ext($file), array('zip', 'tgz'))) {
            $is_archive = true;

            fn_decompress_files(Registry::get('config.dir.database') . $file, Registry::get('config.dir.database') . '_tmp');
        
            $list = fn_get_dir_contents(Registry::get('config.dir.database') . '_tmp', false, true, 'sql', '_tmp/');
        }

        foreach ($list as $_file) {
            db_import_sql_file(Registry::get('config.dir.database') . $_file);
        }

        if ($is_archive) {
            fn_rm(Registry::get('config.dir.database') . '_tmp');
        }
    }

    // Log database restore
    fn_log_event('database', 'restore');

    fn_set_hook('database_restore', $files);

    fn_clear_cache();

    return true;
}
