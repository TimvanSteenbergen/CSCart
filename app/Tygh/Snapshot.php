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

namespace Tygh;

class Snapshot
{
    /**
     * Generates new snapshot
     * @param array $params params list
     */
    public static function create($params)
    {
        if (empty($params['dir_root'])) {
            $params['dir_root'] = Registry::get('config.dir.root');
        }

        if (empty($params['dist'])) {
            $params['dist'] = false;
        }

        $dir_root = $params['dir_root'];
        $dist = $params['dist'];

        $folders = array('app', 'js', $params['theme_rel_backend']);

        if ($dist) {
            $themes_dir = $params['themes_repo'];
            $themes_dir_to = $params['themes_frontend'];
        } else {
            $themes_dir = $params['themes_frontend'];
        }

        $themes = fn_get_dir_contents($themes_dir);

        $snapshot = array('time' => time(), 'files' => array(), 'dirs' => array(), 'themes' => array());

        if ($dist) {
            $snapshot['db_scheme'] = fn_get_contents($dir_root . '/install/database/scheme.sql');

            // remove myslqdump comments
            $snapshot['db_scheme'] = preg_replace('|/\*!.+\*/;\n|imSU', '', $snapshot['db_scheme']);

            // form list of tables
            preg_match_all('/create table `(.+)`/imSU', $snapshot['db_scheme'], $tables);
            $snapshot['db_tables'] = !empty($tables[1]) ? $tables[1] : array();
        }

        $new_snapshot = self::make($dir_root, $folders, array('config.local.php'));
        self::arrayMerge($snapshot, $new_snapshot);

        foreach ($folders as $folder_name) {
            $path = $dir_root . '/' . $folder_name;
            $new_snapshot = self::make($path);
            self::arrayMerge($snapshot, $new_snapshot);
        }

        foreach ($themes as $theme_name) {
            if (is_numeric($theme_name) && $theme_name === strval($theme_name + 0)) {
                continue; // this is company subfolder
            }
            $path = "$themes_dir/$theme_name";
            if ($dist) {
                $new_snapshot = self::make($path, array(), array(), array($themes_dir => $themes_dir_to), true);
            } else {
                $new_snapshot = self::make($path, array(), array(), array(), true);
            }
            $snapshot['themes'][$theme_name]['files'] = $snapshot['themes'][$theme_name]['dirs'] = array();
            self::arrayMerge($snapshot['themes'][$theme_name], $new_snapshot);
        }

        $snapshot['addons'] = fn_get_dir_contents(Registry::get('config.dir.addons'));

        fn_mkdir(Registry::get('config.dir.snapshots'));

        $snapshot_filename = fn_strtolower(PRODUCT_VERSION . '_' . (PRODUCT_STATUS ? (PRODUCT_STATUS . '_') : '') . PRODUCT_EDITION . ($dist ? '_dist' : ''));
        $snapshot_filecontent = '<?php $snapshot' . ($dist ? '_dist' : ''). ' = ' . var_export($snapshot, true) . '; ?>';

        fn_put_contents(Registry::get('config.dir.snapshots') . "{$snapshot_filename}.php", $snapshot_filecontent);
    }

    /**
     * Makes diff between original and latest snapshots
     * @param  array $params $_REQUEST params
     * @return array array with changed data
     */
    public static function changes($params)
    {
        $results = array();

        $creation_time = 0;
        $changes_tree = array();
        $db_diff = '';
        $db_d_diff = '';
        $dist_filename = self::getName('dist');

        $snapshot_filename = self::getName('current');

        if (is_file($dist_filename)) {

            if (is_file($snapshot_filename)) {

                include($snapshot_filename);
                include($dist_filename);

                list($added, $changed, $deleted) = self::diff($snapshot, $snapshot_dist);

                foreach ($snapshot['themes'] as $theme_name => $data) {
                    $data_dist = self::buildTheme($theme_name, $snapshot_dist);

                    list($theme_added, $theme_changed, $theme_deleted) = self::diff($data, $data_dist);

                    self::arrayMerge($added, $theme_added);
                    self::arrayMerge($changed, $theme_changed);
                    self::arrayMerge($deleted, $theme_deleted);
                }

                $tree = self::buildTree(array('added' => $added, 'changed' => $changed, 'deleted' => $deleted));

                $tables = db_get_fields('SHOW TABLES');

                $creation_time = $snapshot['time'];
                $changes_tree = $tree;

                if (!empty($snapshot_dist['db_scheme'])) {
                    $db_scheme = '';
                    foreach ($tables as $table) {
                        if (!in_array($table, $snapshot_dist['db_tables'])) {
                            continue;
                        }

                        $db_scheme .= "\nDROP TABLE IF EXISTS `" . $table . "`;\n";
                        $__scheme = db_get_row("SHOW CREATE TABLE $table");

                        $__scheme = array_pop($__scheme);
                        $replaced_scheme = preg_replace('/ AUTO_INCREMENT=[0-9]*/i', '', $__scheme);
                        if (!empty($replaced_scheme) && is_string($replaced_scheme)) {
                            $__scheme = $replaced_scheme;
                        }
                        $db_scheme .= $__scheme . ";";
                    }

                    $db_scheme = str_replace('default', 'DEFAULT', $db_scheme);
                    $snapshot_dist['db_scheme'] = str_replace('default', 'DEFAULT', $snapshot_dist['db_scheme']);

                    $db_diff = self::textDiff($snapshot_dist['db_scheme'], $db_scheme);
                }
                if (isset($params['db_ready']) && $params['db_ready'] == 'Y') {
                    $snapshot_dir = Registry::get('config.dir.snapshots');
                    $s_r = fn_get_contents($snapshot_dir . 'cmp_release.sql');
                    $s_c = fn_get_contents($snapshot_dir . 'cmp_current.sql');

                    @ini_set('memory_limit', '255M');
                    $db_d_diff = self::textDiff($s_r, $s_c);
                }
            }

            $dist_filename = '';
        }

        return array(
            'creation_time' => $creation_time,
            'changes_tree' => $changes_tree,
            'db_diff' => $db_diff,
            'db_d_diff' => $db_d_diff,
            'dist_filename' => $dist_filename
        );
    }

    /**
     * Creates database and imports dump there
     * @param  string  $db_name db name
     * @return boolean true on success, false - otherwise
     */
    public static function createDb($db_name = '')
    {
        $snapshot_dir = Registry::get('config.dir.snapshots');

        $dbdump_filename = empty($db_name) ? 'cmp_current.sql' : 'cmp_release.sql';

            if (!fn_mkdir($snapshot_dir)) {
                fn_set_notification('E', __('error'), __('text_cannot_create_directory', array(
                    '[directory]' => fn_get_rel_dir($snapshot_dir)
                )));

                return false;
            }
            $dump_file = $snapshot_dir . $dbdump_filename;
            if (is_file($dump_file)) {
                if (!is_writable($dump_file)) {
                    fn_set_notification('E', __('error'), __('dump_file_not_writable'));

                    return false;
                }
            }

            $fd = @fopen($snapshot_dir . $dbdump_filename, 'w');
            if (!$fd) {
                fn_set_notification('E', __('error'), __('dump_cant_create_file'));

                return false;
            }

            if (!empty($db_name)) {
                Database::changeDb($db_name);
            }
            // set export format
            db_query("SET @SQL_MODE = 'MYSQL323'");

            fn_start_scroller();
            $create_statements = array();
            $insert_statements = array();

            $status_data = db_get_array("SHOW TABLE STATUS");

            $dbdump_tables = array();
            foreach ($status_data as $k => $v) {
                $dbdump_tables[] = $v['Name'];
            }

            // get status data
            $t_status = db_get_hash_array("SHOW TABLE STATUS", 'Name');

            foreach ($dbdump_tables as $k => $table) {

                fn_echo('<br />' . __('backupping_data') . ': <b>' . $table . '</b>&nbsp;&nbsp;');
                $total_rows = db_get_field("SELECT COUNT(*) FROM $table");

                $index = db_get_array("SHOW INDEX FROM $table");
                $order_by = array();
                foreach ($index as $kk => $vv) {
                    if ($vv['Key_name'] == 'PRIMARY') {
                    $order_by[] = '`' . $vv['Column_name'] . '`';
                    }
                }

                if (!empty($order_by)) {
                    $order_by = 'ORDER BY ' . implode(',', $order_by);
                } else {
                    $order_by = '';
                }
                // Define iterator
                if (!empty($t_status[$table]) && $t_status[$table]['Avg_row_length'] < DB_MAX_ROW_SIZE) {
                    $it = DB_ROWS_PER_PASS;
                } else {
                    $it = 1;
                }
                for ($i = 0; $i < $total_rows; $i = $i + $it) {
                    $table_data = db_get_array("SELECT * FROM $table $order_by LIMIT $i, $it");
                    foreach ($table_data as $_tdata) {
                        $_tdata = fn_add_slashes($_tdata, true);
                        $values = array();
                        foreach ($_tdata as $v) {
                            $values[] = ($v !== null) ? "'$v'" : 'NULL';
                        }
                        fwrite($fd, "INSERT INTO $table (`" . implode('`, `', array_keys($_tdata)) . "`) VALUES (" . implode(', ', $values) . ");\n");
                    }

                    fn_echo(' .');
                }

            }
            fn_stop_scroller();
            if (!empty($db_name)) {
                Settings::instance()->reloadSections();
            }

            if (fn_allowed_for('ULTIMATE')) {
                $companies = fn_get_short_companies();
                asort($companies);
                $settings['company_root'] = Settings::instance()->getList();
                foreach ($companies as $k=>$v) {
                    $settings['company_'.$k] = Settings::instance()->getList(0, 0, false, $k);
                }
            } else {
                $settings['company_root'] = Settings::instance()->getList();
            }

            if (!empty($db_name)) {
                Database::changeDb(Registry::get('config.db_name'));
            }

            $settings = self::processSettings($settings, '');
            $settings = self::formatSettings($settings['data']);
            ksort($settings);

            $data = print_r($settings, true);
            fwrite($fd,$data);

            fclose($fd);
            @chmod($snapshot_dir . $dbdump_filename, DEFAULT_FILE_PERMISSIONS);

            return true;
    }

    /**
     * Gets the array of core addons (that was included into distributive)
     *
     * @return array List of core addons
     */
    public static function getCoreAddons()
    {
        $addons = array();

        $dist_snapshot_filename = self::getName('dist');
        if (is_file($dist_snapshot_filename)) {
            include($dist_snapshot_filename);
            if (!empty($snapshot_dist['addons'])) {
                $addons = $snapshot_dist['addons'];
            }
        }

        return $addons;
    }

    /**
     * Merges arrays
     * @param array &$array source array
     * @param array $additional data to merge into array
     */
    private static function arrayMerge(&$array, $additional)
    {
        foreach ($array as $key => $v) {
            if (is_array($array[$key])) {
                $array[$key] = array_merge($array[$key], !empty($additional[$key]) ? $additional[$key] : array());
            }
        }
    }

    /**
     * Builds files tree
     * @param  array $changes hashes list
     * @return array built tree
     */
    private static function buildTree($changes)
    {
        $tree = array();

        foreach ($changes as $action => $dataset) {
            foreach ($dataset as $types => $data) {

                $type = substr($types, 0, -1);

                foreach ($data as $path) {
                    $parent = '';
                    $dirs = explode('/', $path);
                    $dirs_size = count($dirs);
                    $elm = & $tree;
                    $level = 0;
                    foreach ($dirs as $key => $name) {
                        if ($name == '') {
                            $name = '/';
                        }
                        if ($key + 1 < $dirs_size) {
                            $new_key = md5("dir-$name-$parent");
                            if (!isset($elm[$new_key]['content'])) {
                                $elm[$new_key] = array(
                                    'name' => $name,
                                    'type' => 'dir',
                                    'level' => $level,
                                    'content' => array(),
                                );
                            }
                            $elm = & $elm[$new_key]['content'];
                        }
                        if ($key + 1 == $dirs_size) {
                            $new_key = md5("$type-$name-$parent");
                            $elm[$new_key]['name'] = $name;
                            $elm[$new_key]['type'] = $type;
                            $elm[$new_key]['level'] = $level;
                            $elm[$new_key]['action'] = $action;
                        }
                        $parent = $new_key;
                        $level++;
                    }
                }
            }
        }

        self::sortTree($tree);

        return $tree;
    }

    /**
     * Gets snapshot file name
     * @param  string $type snapshot type
     * @return string snapshot file name
     */
    private static function getName($type = 'dist')
    {
        $snapshot_filename = Registry::get('config.dir.snapshots') . fn_strtolower(PRODUCT_VERSION . '_' . (PRODUCT_STATUS ? (PRODUCT_STATUS . '_') : '') . PRODUCT_EDITION);

        if ($type == 'dist') {
            $snapshot_filename .= '_dist.php';
        } else {
            $snapshot_filename .= '.php';
        }

        return $snapshot_filename;
    }

    /**
     * Diffs data in array
     * @param  array $current current snapshot data
     * @param  array $dist    original snapshot data
     * @return array diff'ed data
     */
    private static function diff($current, $dist)
    {
        $deleted = $added = array('files' => array(), 'dirs' => array());
        $changed['files'] = array();

        $deleted['files'] = array_diff($dist['files'], $current['files']);
        $deleted['dirs'] = array_diff($dist['dirs'], $current['dirs']);

        $added['files'] = array_diff($current['files'], $dist['files']);
        $added['dirs'] = array_diff($current['dirs'], $dist['dirs']);

        $tmp['files'] = array_diff_assoc($current['files'], $dist['files']);

        $changed['files'] = array_diff($tmp['files'], $added['files']);

        return array($added, $changed, $deleted);
    }

    /**
     * Sorts tree
     * @param array &$tree tree
     */
    private static function sortTree(&$tree)
    {
        foreach ($tree as $key => &$elm) {
            if (!empty($elm['content'])) {
                if (count($elm['content'] > 1)) {
                    uasort($tree[$key]['content'], function($a, $b) {
                        $a1 = (!empty($a['type']) ? $a['type'] : 'file') . (!empty($a['name']) ? $a['name'] : '');
                        $b1 = (!empty($b['type']) ? $b['type'] : 'file') . (!empty($b['name']) ? $b['name'] : '');
                        if ($a1 == $b1) {
                            return 0;
                        }

                        return ($a1 < $b1) ? -1 : 1;
                    });
                }
                self::sortTree($tree[$key]['content']);
            }
        }
    }

    /**
     * Generates hashes list
     * @param  string  $path         files path
     * @param  array   $dirs_list    list of directories should be included in the result
     * @param  array   $skip_files   list of files should be excluded
     * @param  array   $path_replace new path prefix
     * @param  boolean $themes       include themes or not
     * @return array   hashes list
     */
    private static function make($path, $dirs_list = array(), $skip_files = array(), $path_replace = array(), $themes = false)
    {
        $results = array('files' => array(), 'dirs' => array());
        $dir_root_strlen = strlen(Registry::get('config.dir.root'));

        if (is_dir($path)) {
            if ($dh = opendir($path)) {

                while (($file = readdir($dh)) !== false) {
                    if ($file == '.' || $file == '..' || $file{0} == '.') {
                        continue;
                    }

                    $full_file_path = $_full_file_path = $path . '/' . $file;
                    if ($path_replace) {
                        $_find = key($path_replace);
                        $_replace = $path_replace[$_find];
                        if (substr($full_file_path, 0, strlen($_find)) == $_find) {
                            $_full_file_path = substr_replace($full_file_path, $_replace, 0, strlen($_find));
                        }
                    }
                    $short_file_path = $_short_file_path = substr($_full_file_path, $dir_root_strlen);
                    if ($themes) {
                        $_ar = explode('/', $short_file_path);
                        $_short_file_path = implode('/', array_slice($_ar, 3));
                    }
                    if (is_file($full_file_path) && !in_array($file, $skip_files)) {
                        $results['files'][md5($_short_file_path . md5_file($full_file_path))] = $short_file_path;
                    } elseif (is_dir($full_file_path)) {
                        $hash = md5($_short_file_path);
                        if (!empty($dirs_list)) {
                            if (in_array($file, $dirs_list)) {
                                $results['dirs'][$hash] = $short_file_path;
                            }
                        } else {
                            $results['dirs'][$hash] = $short_file_path;
                            $new_results = self::make($full_file_path, array(), array(), $path_replace, $themes);
                            self::arrayMerge($results, $new_results);
                        }
                    }
                }
                closedir($dh);
            }
        }

        return $results;
    }

    /**
     * Processes settings
     * @param  array  $data data
     * @param  string $key  key
     * @return array  processed settings
     */
    private static function processSettings($data, $key)
    {
        $res = array();

        foreach ($data as $k=>$v) {
        if (is_array($v)) {
            $tmp = self::processSettings($v, $k);
            $res[$tmp['key']] = $tmp['data'];
        } else {
            if ($k == 'name') {
                $key = $v;
            }
            //remove dynamic data
            if ($k != 'object_id' &&
                $k != 'section_id' &&
                $k != 'section_tab_id')
            {
                $res[$k] = $v;
            }
        }

        }

        return array('key'=>$key, 'data'=>$res);
    }

    /**
     * Formats settings
     * @param  array     $data data
     * @param  string    $path path
     * @param  int       $lev  level
     * @return formatted settings
     */
    private static function formatSettings($data, $path = array(), $lev = 0)
    {
        $res = array();

        foreach ($data as $k=>$v) {
            if (is_array($v) && $lev < 3) {
                $path[$lev] = $k;
                $tmp = self::formatSettings($v, $path, $lev + 1);
                $res = array_merge($res, $tmp);
            } elseif ($lev == 3) {
                $path[$lev] = $k;
                $res[implode('.', $path)] = $v;
            }
        }

        return $res;
    }

    /**
     * Makes diff betweed 2 strings
     * @param  sring   $source       original data
     * @param  string  $dest         new data
     * @param  boolean $side_by_side side-by-side diff if set to true
     * @return string  diff
     */
    private static function textDiff($source, $dest, $side_by_side = false)
    {
        $diff = new \Text_Diff('auto', array(explode("\n", $source), explode("\n", $dest)));
        $renderer = new \Text_Diff_Renderer_inline();
        $renderer->_leading_context_lines = 3;
        $renderer->_trailing_context_lines = 3;

        if ($side_by_side == false) {
            $renderer->_split_level = 'words';
        }

        $res = $renderer->render($diff);

        if ($side_by_side == true) {
            $res = $renderer->sideBySide($res);
        }

        return $res;
    }

    /**
     * Builds theme files list
     * @param string $theme_name theme name
     * @param array &$snapshot_dist snapshot data
     * @return array theme files list
     */
    private static function buildTheme($theme_name, &$snapshot_dist)
    {
        $theme = !empty($snapshot_dist['themes'][$theme_name]) ? $snapshot_dist['themes'][$theme_name] : array('files' => array(), 'dirs' => array());

        $base = $snapshot_dist['themes'][Registry::get('config.base_theme')];
        $len = strlen('/design/themes/' . Registry::get('config.base_theme'));

        foreach ($base as $type => $dataset) {
            foreach ($dataset as $key => $filename) {
                $base[$type][$key] = substr_replace($filename, '/design/themes/' . $theme_name, 0, $len);
                if (in_array($base[$type][$key], $theme[$type])) {
                    unset($base[$type][$key]);
                }
            }
        }

        self::arrayMerge($base, $theme);

        return $base;
    }

}
