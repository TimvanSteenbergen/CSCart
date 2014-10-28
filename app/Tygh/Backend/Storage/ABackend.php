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

namespace Tygh\Backend\Storage;

abstract class ABackend
{
    /**
     * @const indicates storage location (local or remote)
     */
    const LOCATION = '';

    public $options = array();
    public $type = '';

    private $_file_suffix_length = 6;

    /**
     * Gets option
     *
     * @param  string $key option name
     * @return mixed  option value
     */
    protected function getOption($key)
    {
        return isset($this->options[$key]) ? $this->options[$key] : null;
    }

    /**
     * Adds prefix to file path
     *
     * @param  string $file file
     * @return string prefixed file path
     */
    protected function prefix($file = '')
    {
        $prefix = '';

        if ($prefix_opt = $this->getOption('prefix')) {
            if (is_array($prefix_opt)) {
                $function = array_shift($prefix_opt);
                $prefix .= call_user_func_array($function, $prefix_opt) . '/';
            } else {
                $prefix .= $this->getOption('prefix') . '/';
            }
        }

        return $prefix . $file;
    }

    /**
     * Checks if file with this name is already exist and generate new name if it is so
     *
     * @param  string $file path to file
     * @return string unique file name
     */
    protected function _generateName($file)
    {
        if ($this->isExist($file)) {
            $parts = explode('.', $file);
            $parts[0] .= '_' . fn_strtolower(fn_generate_code('', $this->_file_suffix_length));

            $file = implode('.', $parts);
        }

        return $file;
    }

    /**
     * Copy file outside the storage
     *
     * @param  string $src  file path in storage
     * @param  string $dest path to local file
     * @return int    number of bytes copied
     */
    public function export($src, $dest)
    {
        return false;
    }

    /**
     * Put file to storage
     *
     * @param  string $file   file path in storage
     * @param  array  $params uploaded data and options
     * @return array  file size and file name
     */
    public function put($file, $params)
    {
        return false;
    }

    /**
     * Put directory to storage
     *
     * @param  string  $dir    directory to get files from
     * @param  array   $params additional parameters
     * @return boolean true of success, false on fail
     */
    public function putDir($dir, $params = array())
    {
        return false;
    }

    /**
     * Get file URL
     *
     * @param  string $file file to get URL
     * @return string file URL
     */
    public function getUrl($file, $protocol = '')
    {
        return false;
    }

    /**
     * Gets absolute path to file
     *
     * @param  string $file file to get path
     * @return string absolute path
     */
    public function getAbsolutePath($file)
    {
        return false;
    }

    /**
     * Push file contents to browser
     *
     * @param string $file     file to push
     * @param string $filename file name to be displayed in download dialog
     */
    public function get($file, $filename = '')
    {
        return false;
    }

    /**
     * Deletes file
     *
     * @param  string  $file file to delete
     * @return boolean true if deleted successfully, false - otherwise
     */
    public function delete($file)
    {
        return false;
    }

    /**
     * Deletes directory and all it files
     *
     * @param  string  $dir directory to delete
     * @return boolean true if deleted successfully, false - otherwise
     */
    public function deleteDir($dir)
    {
        return false;
    }

    /**
     * Deletes files using glob pattern
     *
     * @param  string  $pattern glob-compatible pattern
     * @return boolean true if deleted successfully, false - otherwise
     */
    public function deleteByPattern($pattern)
    {
        return false;
    }

    /**
     * Checks if file exists
     *
     * @param  string $file     file to check
     * @param  bool   $in_cache indicates that file existance should be checked in cache only (useful for non-local storages)
     * @return bool   true if exists, false - otherwise
     */
    public function isExist($file, $in_cache = false)
    {
        return false;
    }

    /**
     * Copies files inside storage
     *
     * @param  string  $src  source file/directory
     * @param  string  $dest destination file/directory
     * @return boolean true if copied successfully, false - otherwise
     */
    public function copy($src, $dest)
    {
        return false;
    }

    /**
     * Lists files
     *
     * @param  string $prefix path prefix
     * @return array  files list
     */
    public function getList($prefix = '')
    {
        return false;
    }

   /**
     * Puts list of local files to storage
     *
     * @param  array   $list   files list (relative path)
     * @param  string  $prefix absolute path prefix
     * @param  array   $params additional parameters list
     * @return boolean true on success, false if at least one put was failed
     */
    public function putList($list, $prefix, $params = array())
    {
        if (!empty($list)) {
            fn_set_progress('step_scale', sizeof($list));
            foreach ($list as $item) {
                fn_set_progress('echo', '.');

                if (strpos($prefix, '://') !== false) {
                    $params['contents'] = fn_get_contents($prefix . $item);
                } else {
                    $params['file'] = $prefix . $item;
                    $params['keep_origins'] = true;
                }

                if (!$this->put($item, $params)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Tests storage settings
     *
     * @param  array $settings settings list
     * @return mixed boolean true if settings are correct, error message (string) otherwise
     */
    public function testSettings($settings)
    {
       return true;
    }
}
