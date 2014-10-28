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
use Tygh\Http;
use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

/**
 * Normalize path (URL also accepted): remove "../", "./" and duplicated slashes
 *
 * @param string $path
 * @param string $separator
 * @return string normilized path
 */
function fn_normalize_path($path, $separator = '/')
{
    $prefix = '';
    if (strpos($path, '://') !== false) { // url is passed
        list($prefix, $path) = explode('://', $path);
        $prefix .= '://';
    }

    $result = array();
    $path = preg_replace("/[\\\\\/]+/S", $separator, $path);
    $path_array = explode($separator, $path);
    if (!$path_array[0]) {
        $result[] = '';
    }

    foreach ($path_array as $key => $dir) {
        if ($dir == '..') {
            if (end($result) == '..') {
               $result[] = '..';
            } elseif (!array_pop($result)) {
               $result[] = '..';
            }
        } elseif ($dir != '' && $dir != '.') {
            $result[] = $dir;
        }
    }

    if (!end($path_array)) {
        $result[] = '';
    }

    return fn_is_empty($result) ? '' : $prefix . implode($separator, $result);
}

/**
 * Create directory wrapper. Allows to create included directories
 *
 * @param string $dir
 * @param int $perms permission for new directory
 * @return array List of directories
 */
function fn_mkdir($dir, $perms = DEFAULT_DIR_PERMISSIONS)
{
    $result = false;

    if (!empty($dir)) {

        clearstatcache();
        if (@is_dir($dir)) {

            $result = true;

        } else {

            // Truncate the full path to related to avoid problems with some buggy hostings
            if (strpos($dir, DIR_ROOT) === 0) {
                $dir = './' . substr($dir, strlen(DIR_ROOT) + 1);
                $old_dir = getcwd();
                chdir(DIR_ROOT);
            }

            $dir = fn_normalize_path($dir, '/');
            $path = '';
            $dir_arr = array();
            if (strstr($dir, '/')) {
                $dir_arr = explode('/', $dir);
            } else {
                $dir_arr[] = $dir;
            }

            foreach ($dir_arr as $k => $v) {
                $path .= (empty($k) ? '' : '/') . $v;
                clearstatcache();
                if (!is_dir($path)) {
                    umask(0);
                    $result = @mkdir($path, $perms);
                    if (!$result) {
                        $parent_dir = dirname($path);
                        $parent_perms = fileperms($parent_dir);
                        @chmod($parent_dir, 0777);
                        $result = @mkdir($path, $perms);
                        @chmod($parent_dir, $parent_perms);
                        if (!$result) {
                            break;
                        }
                    }
                }
            }

            if (!empty($old_dir)) {
                @chdir($old_dir);
            }
        }
    }

    return $result;
}

/**
 * Compress files with Tar archiver
 *
 * @param string $archive_name - archive name (zip, tgz, gz and tar.gz supported)
 * @param string $file_list - list of files to place into archive
 * @param string $dirname - directory, where the files should be get from
 * @return bool true
 */
function fn_compress_files($archive_name, $file_list, $dirname = '')
{
    if (!class_exists('PharData')) {
        fn_set_notification('E', __('error'), __('error_class_phar_data_not_found'));

        return false;
    }

    if (empty($dirname)) {
        $dirname = Registry::get('config.dir.files');
    }

    if (!is_array($file_list)) {
        $file_list = array($file_list);
    }

    $ext = fn_get_file_ext($archive_name);

    $_exts = explode('.', $archive_name);
    array_shift($_exts);

    $first_dot_ext = '.' . implode('.', $_exts); // https://bugs.php.net/bug.php?id=58852. Phar gets ext from the first dot: 'test.1.2.3.tgz' -> ext = 1.2.3.tgz

    $arch = fn_normalize_path($dirname . '/' . $archive_name);

    fn_rm($arch);

    if ($ext != 'zip') {
        $arch = fn_normalize_path($dirname . '/' . $archive_name . '.tmp');
        fn_rm($arch);
    }

    if ($ext == 'gz' && strpos($archive_name, '.tar.gz') !== false) {
        $ext = 'tar.gz';
    }

    try {
        $phar = new PharData($arch);

        foreach ($file_list as $file) {
            $path = fn_normalize_path($dirname . '/' . $file);

            if (is_file($path)) {
                $phar->addFile($path, basename($path));

            } elseif (is_dir($path)) {
                $phar->buildFromDirectory($path);
            }
        }

        if ($ext == 'zip') {
            $phar->compressFiles(Phar::GZ);
        } else {
            $phar->compress(Phar::GZ, $first_dot_ext);

            // We need to unset Phar because the PharData class still has the file "open".
            // Windows servers cannot delete the files with the "open" handlers.
            unset($phar);

            fn_rm($arch);
        }

    } catch (Exception $e) {
        fn_set_notification('E', __('error'), $e->getMessage());

        return false;
    }

    return true;
}

/**
 * Extracts files from archive to specified place
 *
 * @param $archive_name - path to the compressed file
 * @param $dirname - directory, where the files should be extracted to
 * @return bool true if archive was succesfully extracted, false otherwise
 */
function fn_decompress_files($archive_name, $dirname = '')
{
    if (empty($dirname)) {
        $dirname = Registry::get('config.dir.files');
    }

    $ext = fn_get_file_ext($archive_name);

    try {
        // We cannot use PharData for ZIP archives. All extracted data looks broken after extract.
        if ($ext == 'zip') {
            if (!class_exists('ZipArchive')) {
                fn_set_notification('E', __('error'), __('error_class_zip_archive_not_found'));

                return false;
            }

            $zip = new ZipArchive;
            $zip->open($archive_name);
            $zip->extractTo($dirname);
            $zip->close();

        } elseif ($ext == 'tgz' || $ext == 'gz') {
            if (!class_exists('PharData')) {
                fn_set_notification('E', __('error'), __('error_class_phar_data_not_found'));

                return false;
            }

            $phar = new PharData($archive_name);
            $phar->extractTo($dirname, null, true); // extract all files, and overwrite
        }

    } catch (Exception $e) {
        fn_set_notification('E', __('error'), __('unable_to_unpack_file'));

        return false;
    }

    return true;
}

/**
 * Get MIME type by the file name
 *
 * @param string $filename
 * @param string $not_available_result MIME type that will be returned in case all checks fail
 * @return string $file_type MIME type of the given file.
 */
function fn_get_file_type($filename, $not_available_result = 'application/octet-stream')
{
    $file_type = $not_available_result;

    static $types = array (
        'zip' => 'application/zip',
        'tgz' => 'application/tgz',
        'rar' => 'application/rar',

        'exe' => 'application/exe',
        'com' => 'application/com',
        'bat' => 'application/bat',

        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ico' => 'image/x-icon',
        'swf' => 'application/x-shockwave-flash',

        'csv' => 'text/csv',
        'txt' => 'text/plain',
        'doc' => 'application/msword',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pdf' => 'application/pdf',

        'css' => 'text/css',
        'js' => 'text/javascript'
    );

    $ext = fn_get_file_ext($filename);

    if (!empty($types[$ext])) {
        $file_type = $types[$ext];
    }

    return $file_type;
}

/**
 * Function tries to get MIME type by different ways.
 *
 * @param string $filename Full path with name to file
 * @param boolean $check_by_extension Try to get MIME type by extension of the file
 * @param string $not_available_result MIME type that will be returned in case all checks fail
 * @return string MIME type of the given file.
 */
function fn_get_mime_content_type($filename, $check_by_extension = true, $not_available_result = 'application/octet-stream')
{
    $type = '';

    if (class_exists('finfo')) {
        $finfo_handler = @finfo_open(FILEINFO_MIME);
        if ($finfo_handler !== false) {
            $type = @finfo_file($finfo_handler, $filename);
            list($type) = explode(';', $type);
            @finfo_close($finfo_handler);
        }
    }

    if (empty($type) && function_exists('mime_content_type')) {
        $type = @mime_content_type($filename);
    }

    if (empty($type) && $check_by_extension && strpos(fn_basename($filename), '.') !== false) {
        $type = fn_get_file_type(fn_basename($filename), $not_available_result);
    }

    return !empty($type) ? $type : $not_available_result;
}

/**
 * Get the EDP downloaded
 *
 * @param string $path path to the file
 * @param string $filename file name to be displayed in download dialog
 * @param boolean $delete deletes original file after download
 * @return bool Always false
 */
function fn_get_file($filepath, $filename = '', $delete = false)
{
    $fd = @fopen($filepath, 'rb');
    if ($fd) {
        $fsize = filesize($filepath);
        $ftime = date('D, d M Y H:i:s T', filemtime($filepath)); // get last modified time

        if (isset($_SERVER['HTTP_RANGE'])) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 206 Partial Content');
            $range = $_SERVER['HTTP_RANGE'];
            $range = str_replace('bytes=', '', $range);
            list($range, $end) = explode('-', $range);

            if (!empty($range)) {
                fseek($fd, $range);
            }
        } else {
            header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK');
            $range = 0;
        }

        if (empty($filename)) {
            // Non-ASCII filenames containing spaces and underscore characters are chunked if no locale is provided
            setlocale(LC_ALL, 'en_US.UTF8');
            $filename = fn_basename($filepath);
        }

        // Browser bug workaround: Filenames can't be sent to IE if there is any kind of traffic compression enabled on the server side
        if (USER_AGENT == 'ie') {
            if (function_exists('apache_setenv')) {
                apache_setenv('no-gzip', '1');
            }

            ini_set("zlib.output_compression", "Off");

            // Browser bug workaround: During the file download with IE, non-ASCII filenames appears with a broken encoding
            $filename = rawurlencode($filename);
        }

        header("Content-disposition: attachment; filename=\"$filename\"");
        header('Content-type: ' . fn_get_mime_content_type($filepath));
        header('Last-Modified: ' . $ftime);
        header('Accept-Ranges: bytes');
        header('Content-Length: ' . ($fsize - $range));
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);

        if ($range) {
            header("Content-Range: bytes $range-" . ($fsize - 1) . '/' . $fsize);
        }

        $result = fpassthru($fd);
        fclose($fd);

        if ($delete) {
            fn_rm($filepath);
        }

        if ($result == false) {
            return false;
        }

        exit;
    }

    return false;
}

/**
 * Create temporary file for uploaded file
 *
 * @param $val file path
 * @return array $val
 */
function fn_get_server_data($val)
{
    if (defined('IS_WINDOWS')) {
        $val = str_replace('\\', '/', $val);
    }

    $root_path = Registry::get('config.dir.files');
    $company_id = Registry::get('runtime.company_id');

    if ((empty($company_id) || Registry::get('runtime.allow_upload_external_paths')) && strpos($val, Registry::get('config.dir.root')) !== false) {
        $root_path = $val;
        $company_id = 0;
    } else {
        $company_id = Registry::get('runtime.simple_ultimate') ? Registry::get('runtime.forced_company_id') : $company_id;
    }


    if (!empty($company_id)) {
        $root_path .=  $company_id . '/';
    }

    if (strpos($val, $root_path) === 0) {
        $val = substr_replace($val, '', 0, strlen($root_path));
    }

    setlocale(LC_ALL, 'en_US.UTF8');
    $path = fn_normalize_path($root_path . $val);
    $result = false;
    if (file_exists($path)) {

        $result = array(
            'name' => fn_basename($root_path . $val),
            'path' => $path
        );

        $tempfile = fn_create_temp_file();
        fn_copy($result['path'], $tempfile);
        $result['path'] = $tempfile;
        $result['size'] = filesize($result['path']);

        $cache = Registry::get('temp_fs_data');

        if (!isset($cache[$result['path']])) { // cache file to allow multiple usage
            $cache[$result['path']] = $tempfile;
            Registry::set('temp_fs_data', $cache);
        }
    }

    return $result;
}

/**
 * Rebuilds $_FILES array to more user-friendly look
 *
 * @param string $name Name of file parameter
 * @return array Rebuilt file array
 */
function fn_rebuild_files($name)
{
    $rebuilt = array();

    if (!is_array(@$_FILES[$name])) {
        return $rebuilt;
    }

    if (isset($_FILES[$name]['error'])) {
        if (!is_array($_FILES[$name]['error'])) {
            return $_FILES[$name];
        }
    } elseif (fn_is_empty($_FILES[$name]['size'])) {
        return $_FILES[$name];
    }

    foreach ($_FILES[$name] as $k => $v) {
        if ($k == 'tmp_name') {
            $k = 'path';
        }
        $rebuilt = fn_array_multimerge($rebuilt, $v, $k);
    }

    return $rebuilt;
}

/**
 * Recursively copy directory (or just a file)
 *
 * @param string $source
 * @param string $dest
 * @param bool $silent
 * @param array $exclude_files
 * @return bool True on success, false otherwise
 */
function fn_copy($source, $dest, $silent = true, $exclude_files = array())
{
    /**
     * Ability to forbid file copy or change parameters
     *
     * @param string  $source  source file/directory
     * @param string  $dest    destination file/directory
     * @param boolean $silent  silent flag
     * @param array   $exclude files to exclude
     */
    fn_set_hook('copy_file', $source, $dest, $silent, $exclude_files);

    if (empty($source)) {
        return false;
    }

    // Simple copy for a file
    if (is_file($source)) {
        $source_file_name = fn_basename($source);
        if (in_array($source_file_name, $exclude_files)) {
            return true;
        }
        if (@is_dir($dest)) {
            $dest .= '/' . $source_file_name;
        }
        if (filesize($source) == 0) {
            $fd = fopen($dest, 'w');
            fclose($fd);
            $res = true;
        } else {
            $res = @copy($source, $dest);
        }
        @chmod($dest, DEFAULT_FILE_PERMISSIONS);
        clearstatcache(true, $dest);

        return $res;
    }

    // Make destination directory
    if ($silent == false) {
        $_dir = strpos($dest, Registry::get('config.dir.root')) === 0 ? str_replace(Registry::get('config.dir.root') . '/', '', $dest) : $dest;
        fn_set_progress('echo', $_dir . '<br/>');
    }

    if (!fn_mkdir($dest)) {
        return false;
    }

    // Loop through the folder
    if (@is_dir($source)) {
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            // Deep copy directories
            if ($dest !== $source . '/' . $entry) {
                if (fn_copy($source . '/' . $entry, $dest . '/' . $entry, $silent, $exclude_files) == false) {
                    return false;
                }
            }
        }

        // Clean up
        $dir->close();

        return true;
    } else {
        return false;
    }
}

/**
 * Recursively remove directory (or just a file)
 *
 * @param string $source
 * @param bool $delete_root
 * @param string $pattern
 * @return bool
 */
function fn_rm($source, $delete_root = true, $pattern = '')
{
    // Simple copy for a file
    if (is_file($source)) {
        $res = true;
        if (empty($pattern) || (!empty($pattern) && preg_match('/' . $pattern . '/', fn_basename($source)))) {
            $res = @unlink($source);
        }

        return $res;
    }

    // Loop through the folder
    if (is_dir($source)) {
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ($entry == '.' || $entry == '..') {
                continue;
            }
             if (fn_rm($source . '/' . $entry, true, $pattern) == false) {
                return false;
            }
        }
        // Clean up
        $dir->close();

        return ($delete_root == true && empty($pattern)) ? @rmdir($source) : true;
    } else {
        return false;
    }
}

/**
 * Get file extension
 *
 * @param string $filename
 * @return string File extension
 */
function fn_get_file_ext($filename)
{
    $i = strrpos($filename, '.');
    if ($i === false) {
        return '';
    }

    return substr($filename, $i + 1);
}

/**
 * Get directory contents
 *
 * @param string $dir directory path
 * @param bool $get_dirs get sub directories
 * @param bool $get_files
 * @param mixed $extension allowed file extensions
 * @param string $prefix file/dir path prefix
 * @return array $contents directory contents
 */
function fn_get_dir_contents($dir, $get_dirs = true, $get_files = false, $extension = '', $prefix = '', $recursive = false, $exclude = array())
{

    $contents = array();
    if (is_dir($dir)) {
        if ($dh = opendir($dir)) {

            // $extention - can be string or array. Transform to array.
            $extension = is_array($extension) ? $extension : array($extension);

            while (($file = readdir($dh)) !== false) {
                if ($file == '.' || $file == '..' || in_array($file, $exclude)) {
                    continue;
                }

                if ($recursive == true && is_dir($dir . '/' . $file)) {
                    $contents = fn_array_merge($contents, fn_get_dir_contents($dir . '/' . $file, $get_dirs, $get_files, $extension, $prefix . $file . '/', $recursive, $exclude), false);
                }

                if ((is_dir($dir . '/' . $file) && $get_dirs == true) || (is_file($dir . '/' . $file) && $get_files == true)) {
                    if ($get_files == true && !fn_is_empty($extension)) {
                        // Check all extentions for file
                        foreach ($extension as $_ext) {
                             if (substr($file, -strlen($_ext)) == $_ext) {
                                $contents[] = $prefix . $file;
                                break;
                             }
                        }
                    } else {
                        $contents[] = $prefix . $file;
                    }
                }
            }
            closedir($dh);
        }
    }

    asort($contents, SORT_STRING);

    return $contents;
}

/**
 * Get file contents from local or remote filesystem
 *
 * @param string $location file location
 * @param string $base_dir
 * @return string $result
 */
function fn_get_contents($location, $base_dir = '')
{
    $result = '';
    $path = $base_dir . $location;

    if (!empty($base_dir) && !fn_check_path($path)) {
        return $result;
    }

    // Location is regular file
    if (is_file($path)) {
        $result = @file_get_contents($path);

    // Location is url
    } elseif (strpos($path, '://') !== false) {

        // Prepare url
        $path = str_replace(' ', '%20', $path);
        if (Bootstrap::getIniParam('allow_url_fopen') == true) {
            $result = @file_get_contents($path);
        } else {
            $result = Http::get($path);
        }
    }

    return $result;
}

/**
 * Write a string to a file
 *
 * @param string $location file location
 * @param string $content
 * @param string $base_dir
 * @param int $file_perm File access permissions for setting after writing into the file. For example 0666.
 * @param boolean $append append content if set to true
 * @return string $result
 */
function fn_put_contents($location, $content, $base_dir = '', $file_perm = DEFAULT_FILE_PERMISSIONS, $append = false)
{
    $result = '';
    $path = $base_dir . $location;

    if (!empty($base_dir) && !fn_check_path($path)) {
        return false;
    }

    fn_mkdir(dirname($path));

    $flags = 0;
    if ($append == true) {
        $flags = FILE_APPEND;
    }

    // Location is regular file
    $result = @file_put_contents($path, $content, $flags);
    if ($result !== false) {
        @chmod($path, $file_perm);
    }

    return $result;
}

/**
 * Get data from url
 *
 * @param string $val
 * @return array $val
 */
function fn_get_url_data($val)
{
    if (!preg_match('/:\/\//', $val)) {
        $val = 'http://' . $val;
    }

    $result = false;
    $_data = fn_get_contents($val);

    if (!empty($_data)) {
        $result = array(
            'name' => fn_basename($val)
        );

        // Check if the file is dynamically generated
        if (strpos($result['name'], '&') !== false || strpos($result['name'], '?') !== false) {
            $result['name'] = 'url_uploaded_file_' . uniqid(TIME);
        }
        $result['path'] = fn_create_temp_file();
        $result['size'] = strlen($_data);

        $fd = fopen($result['path'], 'wb');
        fwrite($fd, $_data, $result['size']);
        fclose($fd);
        @chmod($result['path'], DEFAULT_FILE_PERMISSIONS);

        $cache = Registry::get('temp_fs_data');

        if (!isset($cache[$result['path']])) { // cache file to allow multiple usage
            $cache[$result['path']] = $result['path'];
            Registry::set('temp_fs_data', $cache);
        }
    }

    return $result;
}

/**
 * Function get local uploaded
 *
 * @param array $val
 * @staticvar array $cache
 * @return array
 */
function fn_get_local_data($val)
{
    $cache = Registry::get('temp_fs_data');

    if (!isset($cache[$val['path']])) { // cache file to allow multiple usage
        $tempfile = fn_create_temp_file();
        if (move_uploaded_file($val['path'], $tempfile) == true) {
            @chmod($tempfile, DEFAULT_FILE_PERMISSIONS);
            clearstatcache(true, $tempfile);
            $cache[$val['path']] = $tempfile;
        } else {
            $cache[$val['path']] = '';
        }

        Registry::set('temp_fs_data', $cache);
    }

    if (defined('KEEP_UPLOADED_FILES')) {
        $tempfile = fn_create_temp_file();
        fn_copy($cache[$val['path']], $tempfile);
        $val['path'] = $tempfile;
    } else {
        $val['path'] = $cache[$val['path']];
    }

    return !empty($val['size']) ? $val : false;
}

/**
 * Finds the last key in the array and applies the custom function to it.
 *
 * @param array $arr
 * @param string $fn
 * @param bool $is_first
 */
function fn_get_last_key(&$arr, $fn = '', $is_first = false)
{
    if (!is_array($arr)&&$is_first == true) {
        $arr = call_user_func($fn, $arr);

        return;
    }

    foreach ($arr as $k => $v) {
        if (is_array($v) && count($v)) {
            fn_get_last_key($arr[$k], $fn);
        } elseif (!is_array($v)&&!empty($v)) {
            $arr[$k] = call_user_func($fn, $arr[$k]);
        }
    }
}

/**
 * Filters data from instant file uploader
 * @param string $name name of uploaded data
 * @param array $filter_by_ext allow file extensions
 * @return mixed filtered file data on success, false otherwise
 */
function fn_filter_uploaded_data($name, $filter_by_ext = array())
{
    $udata_local = fn_rebuild_files('file_' . $name);
    $udata_other = !empty($_REQUEST['file_' . $name]) ? $_REQUEST['file_' . $name] : array();
    $utype = !empty($_REQUEST['type_' . $name]) ? $_REQUEST['type_' . $name] : array();

    if (empty($utype)) {
        return array();
    }

    $filtered = array();

    foreach ($utype as $id => $type) {
        if ($type == 'local' && !fn_is_empty(@$udata_local[$id])) {
            $filtered[$id] = fn_get_local_data(Bootstrap::stripSlashes($udata_local[$id]));

        } elseif ($type == 'server' && !fn_is_empty(@$udata_other[$id]) && AREA == 'A') {
            fn_get_last_key($udata_other[$id], 'fn_get_server_data', true);
            $filtered[$id] = $udata_other[$id];

        } elseif ($type == 'url' && !fn_is_empty(@$udata_other[$id])) {
            fn_get_last_key($udata_other[$id], 'fn_get_url_data', true);
            $filtered[$id] = $udata_other[$id];
        }

        if (isset($filtered[$id]) && $filtered[$id] === false) {
            unset($filtered[$id]);
            fn_set_notification('E', __('error'), __('cant_upload_file'));
            continue;
        }

        if (!empty($filtered[$id]['name'])) {
            $filtered[$id]['name'] = str_replace(' ', '_', urldecode($filtered[$id]['name'])); // replace spaces with underscores
            if (!fn_check_uploaded_data($filtered[$id], $filter_by_ext)) {
                unset($filtered[$id]);
            }
        }
    }

    static $shutdown_inited;

    if (!$shutdown_inited) {
        $shutdown_inited = true;
        register_shutdown_function('fn_remove_temp_data');
    }

    return $filtered;
}

/**
 * Filters data from instant file uploader
 * @param array $filter_by_ext allow file extensions
 * @return mixed filtered file data on success, false otherwise
 */
function fn_filter_instant_upload($filter_by_ext = array())
{
    if (!empty($_FILES['upload'])) {
        $_FILES['upload']['path'] = $_FILES['upload']['tmp_name'];
        $uploaded_data = fn_get_local_data(Bootstrap::stripSlashes($_FILES['upload']));
        if (fn_check_uploaded_data($uploaded_data, $filter_by_ext)) {
            return $uploaded_data;
        }
    }

    return false;
}

/**
 * Checks uploaded file can be processed
 * @param array $uploaded_data uploaded file data
 * @param array $filter_by_ext allowed file extensions
 * @return boolean true if file can be processed, false - otherwise
 */
function fn_check_uploaded_data($uploaded_data, $filter_by_ext)
{
    if (!empty($uploaded_data) && is_array($uploaded_data) && !empty($uploaded_data['name'])) {
        $ext = fn_get_file_ext($uploaded_data['name']);

        if (!empty($filter_by_ext) && !in_array(fn_strtolower($ext), $filter_by_ext)) {
            fn_set_notification('E', __('error'), __('text_not_allowed_to_upload_file_extension', array(
                '[ext]' => $ext
            )));

            return false;
        }

        if (in_array(fn_strtolower($ext), Registry::get('config.forbidden_file_extensions'))) {
            fn_set_notification('E', __('error'), __('text_forbidden_file_extension', array(
                '[ext]' => $ext
            )));

            return false;
        }

        if (!empty($uploaded_data['path']) && in_array(fn_get_mime_content_type($uploaded_data['path'], true, 'text/plain'), Registry::get('config.forbidden_mime_types'))) {
            fn_set_notification('E', __('error'), __('text_forbidden_file_mime', array(
                '[mime]' => fn_get_mime_content_type($filtered[$id]['path'], true, 'text/plain')
            )));

            return false;
        }
    }

    return true;
}

/**
 * Remove temporary files
 */
function fn_remove_temp_data()
{
    $fs_data = Registry::get('temp_fs_data');
    if (!empty($fs_data)) {
        foreach ($fs_data as $file) {
            fn_rm($file);
        }
    }
}

/**
 * Create temporary file
 *
 * @return temporary file
 */
function fn_create_temp_file()
{
    fn_mkdir(Registry::get('config.dir.cache_misc') . 'tmp');
    $tmpnam = fn_normalize_path(tempnam(Registry::get('config.dir.cache_misc') . 'tmp/', 'tmp_'));

    return $tmpnam;
}

/**
 * Returns correct path from url "path" component
 *
 * @param string $path
 * @return correct path
 */
function fn_get_url_path($path)
{
    $dir = dirname($path);

    if ($dir == '.' || $dir == '/') {
        return '';
    }

    return (defined('WINDOWS')) ? str_replace('\\', '/', $dir) : $dir;
}

/**
 * Check path to file
 *
 * @param string $path
 * @return bool
 */
function fn_check_path($path)
{
    $real_path = realpath($path);

    return str_replace('\\', '/', $real_path) == $path ? true : false;
}

/**
 * Gets line from file pointer and parse for CSV fields
 *
 * @param resource $f a valid file pointer to a file successfully opened by fopen(), popen(), or fsockopen().
 * @param int $length maximum line length
 * @param string $d field delimiter
 * @param string $q the field enclosure character
 * @return array structured data
 */
function fn_fgetcsv($f, $length, $d = ',', $q = '"')
{
    $list = array();
    $st = fgets($f, $length);
    if ($st === false || $st === null) {
        return $st;
    }

    if (trim($st) === '') {
        return array('');
    }

    $st = rtrim($st, "\n\r");
    if (substr($st, -strlen($d)) == $d) {
        $st .= '""';
    }

    while ($st !== '' && $st !== false) {
        if ($st[0] !== $q) {
            // Non-quoted.
            list ($field) = explode($d, $st, 2);
            $st = substr($st, strlen($field) + strlen($d));
        } else {
            // Quoted field.
            $st = substr($st, 1);
            $field = '';
            while (1) {
                // Find until finishing quote (EXCLUDING) or eol (including)
                preg_match("/^((?:[^$q]+|$q$q)*)/sx", $st, $p);
                $part = $p[1];
                $partlen = strlen($part);
                $st = substr($st, strlen($p[0]));
                $field .= str_replace($q . $q, $q, $part);
                if (strlen($st) && $st[0] === $q) {
                    // Found finishing quote.
                    list ($dummy) = explode($d, $st, 2);
                    $st = substr($st, strlen($dummy) + strlen($d));
                    break;
                } else {
                    // No finishing quote - newline.
                    $st = fgets($f, $length);
                }
            }
        }

        $list[] = $field;
    }

    return $list;
}

/**
 * Wrapper for rename with chmod
 *
 * @param string $oldname The old name. The wrapper used in oldname must match the wrapper used in newname.
 * @param string $newname The new name.
 * @param resource $context Note: Context support was added with PHP 5.0.0. For a description of contexts, refer to Stream Functions.
 *
 * @return boolean Returns TRUE on success or FALSE on failure.
 */
function fn_rename($oldname, $newname, $context = null)
{
    $result = ($context === null) ? rename($oldname, $newname) : rename($oldname, $newname, $context);
    if ($result !== false) {
        @chmod($newname, is_dir($new_name) ? DEFAULT_DIR_PERMISSIONS : DEFAULT_FILE_PERMISSIONS);
    }

    return $result;
}

/*
 * Returns pathinfo with using UTF characters.
 *
 * @param string $path
 * @param string $encoding
 * @return array
 */
function fn_pathinfo($path, $encoding = 'UTF-8')
{
    $path = fn_unified_path($path);
    $basename = explode("/", $path);
    $basename = end($basename);

    if (strpos($path, '/') === false) {
        $path = './' . $path;
    }

    $dirname = rtrim(fn_substr($path, 0, fn_strlen($path, $encoding) - fn_strlen($basename, $encoding) - 1, $encoding), '/');
    $dirname .= empty($dirname) ? '/' : '';

    if (strpos($basename, '.') !== false) {
        $_name_components = explode('.', $basename);
        $extension = array_pop($_name_components);
        $filename = implode('.', $_name_components);
    } else {
        $extension = '';
        $filename = $basename;
    }

    return array (
        'dirname' => $dirname,
        'basename' => $basename,
        'extension' => $extension,
        'filename' => $filename
    );
}

/*
 * Returns basename with using UTF characters.
 *
 * @param string $path
 * @param string $suffix
 * @param string $encoding
 * @return string
 */
function fn_basename($path, $suffix = '', $encoding = 'UTF-8')
{
    $basename = explode("/", $path);
    $basename = end($basename);

    if (!empty($suffix) && fn_substr($basename, (0 - fn_strlen($suffix, $encoding)), fn_strlen($basename, $encoding), $encoding) == $suffix) {
        $basename = fn_substr($basename, 0, (0 - fn_strlen($suffix, $encoding)), $encoding);
    }

    return $basename;
}

/**
 * Replace backslashes in windows-style path
 *
 * @param string $path path
 * @return string filtered path
 */
function fn_unified_path($path)
{
    if (defined('IS_WINDOWS')) {
        $path = str_replace('\\', '/', $path);
    }

    return $path;
}

/**
 * Connect to ftp server
 *
 * @param array $settings options
 * @return boolean true if connected successfully and working directory is correct, false - otherwise
 */
function fn_ftp_connect($settings)
{
    $result = true;

    if (function_exists('ftp_connect')) {
        if (!empty($settings['ftp_hostname'])) {
            $ftp_port = !empty($settings['ftp_port']) ? $settings['ftp_port'] : '21';
            if (substr_count($settings['ftp_hostname'], ':') > 0) {
                $start_pos = strrpos($settings['ftp_hostname'], ':');
                $ftp_port = substr($settings['ftp_hostname'], $start_pos + 1);
                $settings['ftp_hostname'] = substr($settings['ftp_hostname'], 0, $start_pos);
            }

            $ftp = @ftp_connect($settings['ftp_hostname'], $ftp_port);
            if (!empty($ftp)) {
                if (@ftp_login($ftp, $settings['ftp_username'], $settings['ftp_password'])) {

                    ftp_pasv($ftp, true);

                    if (!empty($settings['ftp_directory'])) {
                        @ftp_chdir($ftp, $settings['ftp_directory']);
                    }

                    $files = ftp_nlist($ftp, '.');
                    if (!empty($files) && in_array('config.php', $files)) {
                        Registry::set('ftp_connection', $ftp);
                    } else {
//                        fn_set_notification('E', __('error'), __('text_uc_ftp_cart_directory_not_found'));
                        $result = false;
                    }
                } else {
//                    fn_set_notification('E', __('error'), __('text_uc_ftp_login_failed'));
                    $result = false;
                }
            } else {
//                fn_set_notification('E', __('error'), __('text_uc_ftp_connect_failed'));
                $result = false;
            }
        }
    } else {
//        fn_set_notification('E', __('error'), __('text_uc_no_ftp_module'));
        $result = false;
    }

    return $result;
}

function fn_ftp_chmod_file($filename, $perm = DEFAULT_FILE_PERMISSIONS, $recursive = false)
{
    $result = false;

    $ftp = Registry::get('ftp_connection');
    if (is_resource($ftp)) {
        $dest = dirname($filename);
        $dest = rtrim($dest, '/') . '/'; // force adding trailing slash to path

        $rel_path = str_replace(Registry::get('config.dir.root') . '/', '', $dest);
        $cdir = ftp_pwd($ftp);

        if (empty($rel_path)) { // if rel_path is empty, assume it's root directory
            $rel_path = $cdir;
        }

        if (@ftp_chdir($ftp, $rel_path)) {
            $result = @ftp_site($ftp, 'CHMOD ' . sprintf('0%o', $perm) . ' ' . fn_basename($filename));

            if ($recursive) {
                $path = fn_normalize_path($cdir . '/' . $rel_path . fn_basename($filename));

                if (is_dir($path)) {
                    $_files = fn_get_dir_contents($path, true, true, '', '', true);

                    if (!empty($_files)) {
                        foreach ($_files as $_file) {
                            fn_ftp_chmod_file($path . '/' . $_file, $perm, false);
                        }
                    }

                }
            }

            ftp_chdir($ftp, $cdir);
        }
    }

    return $result;
}

/**
 * Gets path user is allowed to put files to
 * @return string files path
 */
function fn_get_files_dir_path()
{
    $path = Registry::get('config.dir.files');
    $company_id = Registry::get('runtime.simple_ultimate') ? Registry::get('runtime.forced_company_id') : Registry::get('runtime.company_id');

    if (!empty($company_id)) {
        $path .=  $company_id . '/';
    }

    return $path;
}

/**
 * Gets HTTP path user is allowed to put files to
 * @return string files path
 */
function fn_get_http_files_dir_path()
{
    $path = fn_get_rel_dir(fn_get_files_dir_path());
    $path = Registry::get('config.http_location') . '/' . $path;

    return $path;
}

/**
 * Gets directory path relative to root directory
 * @param string $dir absolute directory path
 * @return string relative directory path
 */
function fn_get_rel_dir($dir)
{
    $dir = str_replace(Registry::get('config.dir.root') . '/', '', $dir);

    return $dir;
}

/**
 * Checks if folders/files can be copied to destination dir
 *
 * @param string $path path to Root add-on path
 * @return array List if non-writable directories
 */
function fn_check_copy_ability($source, $destination)
{
    $struct_files = fn_get_dir_contents($source, true, true, '', '', true);

    $non_writable = array();

    foreach ($struct_files as $file) {
        if (is_file($source . $file)) {
            $res = fn_check_writable_path_permissions(dirname($destination . '/' . $file));

            if ($res !== true) {
                $non_writable[$res] = true;
            }
        }
    }

    return $non_writable;
}

/**
 * Check if specified file path can be rewritten.
 *
 * Example:
 *      Base struct
 *          app                         r-x
 *              /addons                 r-x
 *                  /widget             rwx
 *                      addon.xml       rw-
 *              /core                   r-x
 *                  /functions          r-x
 *                      fn.addons.php   r--
 *          design                      rwx
 *              /index.tpl              rw-
 *
 * fn_check_writable_path_permissions(app/addons/widget/addon.xml)          true
 * fn_check_writable_path_permissions(app/core/functions/fn.addons.php)     app/core/functions/
 * fn_check_writable_path_permissions(app/core/functions/not_a_file.php)    app/core/functions/
 * fn_check_writable_path_permissions(design/index.tpl)                     true
 * fn_check_writable_path_permissions(design/test_file.tpl)                 true
 *
 * @param string $path Path to file
 * @return bool true of path is writable or (string) path to parent non-writable directory
 *
 */
function fn_check_writable_path_permissions($path)
{
    if (is_writable($path)) {
        $result = true;

    } elseif (is_dir($path)) {
        $result = $path;

    } else {
        $result = call_user_func(__FUNCTION__, dirname($path));
    }

    return $result;
}

/**
 * Copies files using FTP access
 *
 * @param string $source Absolute path (non-ftp) to source dir/file
 * @param string $destination Absolute path (non-ftp) to destination dir/file
 * @param array $ftp_access
 *      array(
 *          'hostname',
 *          'username',
 *          'password',
 *          'directory'
 *      )
 * @return bool true if all files were copied or (string) Error message
 */
function fn_copy_by_ftp($source, $destination, $ftp_access)
{
    try {
        $ftp = new Ftp;

        $ftp->connect($ftp_access['hostname']);
        $ftp->login($ftp_access['username'], $ftp_access['password']);
        $ftp->chdir($ftp_access['directory']);

        $files = $ftp->nlist('');
        if (!empty($files) && in_array('config.php', $files)) {
            $ftp_destination = str_replace(Registry::get('config.dir.root'), '', $destination);
            $ftp->chdir($ftp_access['directory'] . $ftp_destination);

            $struct = fn_get_dir_contents($source, false, true, '', '', true);

            foreach ($struct as $file) {
                $dir = dirname($file);

                if (!$ftp->isDir($dir)) {
                    try {
                        $ftp->mkDirRecursive($dir);

                    } catch (FtpException $e) {
                        throw new FtpException('ftp_access_denied' . ':' . $e->getMessage());
                    }
                }

                try {
                    $ftp->put($file, $source . $file, FTP_BINARY);

                } catch (FtpException $e) {
                    throw new FtpException('ftp_access_denied' . ':' . $e->getMessage());
                }
            }

            return true;

        } else {
            throw new FtpException('ftp_directory_is_incorrect');
        }

    } catch (FtpException $e) {
        return __('invalid_ftp_access') . ': ' . $e->getMessage();
    }

    return false;
}
