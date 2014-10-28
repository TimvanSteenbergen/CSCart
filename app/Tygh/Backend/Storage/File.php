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

use Tygh\Cdn;
use Tygh\Registry;
use Tygh\Storage;

class File extends ABackend
{
    const LOCATION = 'local';

    /**
     * Copy file outside the storage
     *
     * @param  string $src  file path in storage
     * @param  string $dest path to local file
     * @return int    number of bytes copied
     */
    public function export($src, $dest)
    {
        if (!fn_mkdir(dirname($dest))) {
            return false;
        }

        return fn_copy($this->prefix($src), $dest);
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
        if (empty($params['overwrite'])) {
            $file = $this->_generateName($file); // check if name is unique and generate new if not
        }
        $file = $this->prefix($file);

        if (!empty($params['compress'])) {
            if (!empty($params['contents'])) {
                $params['contents'] = gzencode($params['contents']);
            }
        }

        if (!fn_mkdir(dirname($file))) {
            return false;
        }

        if (!empty($params['file'])) {
            fn_copy($params['file'], $file);
        } else {
            fn_put_contents($file, $params['contents']);
        }

        if (!file_exists($file)) {
            return false;
        }

        $filesize = filesize($file);

        if (!empty($params['file']) && empty($params['keep_origins'])) {
            fn_rm($params['file']);
        }

        return array($filesize, str_replace($this->prefix(), '', $file));
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
        // Prefix param is required
        if (empty($params['prefix'])) {
            return false;
        }

        $files = fn_get_dir_contents($dir, false, true, '', '', true);
        fn_set_progress('step_scale', sizeof($files));

        foreach ($files as $source_file) {
            fn_set_progress('echo', '.');

            $dest = $this->prefix(rtrim($params['prefix'], '/') . '/' . $source_file);
            if (!is_dir(dirname($dest))) {
                fn_mkdir(dirname($dest));
            }

            fn_copy($dir . '/' . $source_file, $dest);
        }

        return true;
    }

    /**
     * Get file URL
     *
     * @param  string $file file to get URL
     * @return string file URL
     */
    public function getUrl($file = '', $protocol = '')
    {
        if (strpos($file, '://') !== false) {
            return $file;
        }

        if ($this->getOption('cdn') && Cdn::instance()->getOption('is_enabled')) {
            if ($protocol == 'http') {
                $prefix = 'http:';
            } elseif ($protocol == 'https') {
                $prefix = 'https:';
            } elseif ($protocol == 'short') {
                $prefix = '';
            } else {
                $prefix = defined('HTTPS') ? 'https:' : 'http:';
            }

            $prefix .= '//' . Cdn::instance()->getHost('host');
            $real_file = $this->getAbsolutePath($file);
            if (is_file($real_file)) { // add timestamp to files only, skip dirs
                $file .= '?t=' . filemtime($real_file);
            }

        } else {
            if ($protocol == 'http') {
                $prefix = Registry::get('config.http_location');
            } elseif ($protocol == 'https') {
                $prefix = Registry::get('config.https_location');
            } elseif ($protocol == 'short') {
                $prefix = '//' . Registry::get('config.http_host') . Registry::get('config.http_path'); // FIXME
            } else {
                $prefix = Registry::get('config.current_location');
            }
        }

        $path = str_replace(Registry::get('config.dir.root'), '', $this->prefix($file));

        return $prefix . $path;
    }

    /**
     * Gets absolute path to file
     *
     * @param  string $file file to get path
     * @return string absolute path
     */
    public function getAbsolutePath($file)
    {
        return $this->prefix($file);
    }

    /**
     * Push file contents to browser
     *
     * @param  string $file     file to push
     * @param  string $filename file name to be displayed in download dialog
     * @return void
     */
    public function get($file, $filename = '')
    {
        fn_get_file($this->prefix($file), $filename);
    }

    /**
     * Deletes file
     *
     * @param  string  $file file to delete
     * @return boolean true if deleted successfully, false - otherwise
     */
    public function delete($file)
    {
        if (empty($file)) {
            return false;
        }

        return fn_rm($this->prefix($file));
    }

    /**
     * Deletes directory and all it files
     *
     * @param  string  $dir directory to delete
     * @return boolean true if deleted successfully, false - otherwise
     */
    public function deleteDir($dir = '')
    {
        return fn_rm($this->prefix($dir));
    }

    /**
     * Deletes files using glob pattern
     *
     * @param  string  $pattern glob-compatible pattern
     * @return boolean true if deleted successfully, false - otherwise
     */
    public function deleteByPattern($pattern)
    {
        $files = glob($this->prefix($pattern));

        if (!empty($files)) {
            foreach ($files as $file) {
                fn_rm($file);
            }
        }

        return true;
    }

    /**
     * Checks if file exists
     *
     * @param  string  $file     file to check
     * @param  string  $in_cache indicates that file existance should be checked in cache only (useful for non-local storages)
     * @return boolean true if exists, false - otherwise
     */
    public function isExist($file, $in_cache = false)
    {
        return file_exists($this->prefix($file));
    }

    /**
     * Copy files inside storage
     *
     * @param  string  $src  source file/directory
     * @param  string  $dest destination file/directory
     * @return boolean true if copied successfully, false - otherwise
     */
    public function copy($src, $dest)
    {
        $dest = $this->prefix($dest);

        if (!$this->isExist($src) || !fn_mkdir(dirname($dest))) {
            return false;
        }

        return fn_copy($this->prefix($src), $dest);
    }

    /**
     * Lists files
     * @param  string $prefix path prefix
     * @return array  files list
     */
    public function getList($prefix = '')
    {
        return fn_get_dir_contents($this->prefix($prefix), false, true, '', '', true);
    }

    /**
     * Adds prefix to file path
     *
     * @param  string $file file
     * @return string prefixed file path
     */
    protected function prefix($file = '')
    {
        $path = rtrim($this->getOption('dir'), '/') . '/' . parent::prefix($file);

        fn_set_hook('storage_prefix', $path, $this->type);

        return $path;
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
