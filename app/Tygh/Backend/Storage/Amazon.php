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

use Tygh\Exceptions\ClassNotFoundException;
use Tygh\Exceptions\ExternalException;
use Tygh\Storage;
use Tygh\Registry;

class Amazon extends ABackend
{
    const LOCATION = 'remote';

    /**
     * @var \AmazonS3
     */
    private $_s3;
    private $_buckets;

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

        $res = $this->s3()->get_object($this->getOption('bucket'), $this->prefix($src), array(
            'fileDownload' => $dest
        ));

        if ($res->isOK()) {
            return true;
        }

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
        if (empty($params['overwrite'])) {
            $file = $this->_generateName($file); // check if name is unique and generate new if not
        }
        $file = $this->prefix($file);

        $s3 = $this->s3(); // get object to initialize class and get access to contstants below

        $data = array(
            'acl' => \AmazonS3::ACL_PUBLIC,
            'headers' => array()
        );

        if (!empty($params['compress'])) {

            $data['headers']['Content-Encoding'] = 'gzip';
            $data['headers']['Cache-control'] = 'private';

            if (!empty($params['contents'])) {
                $params['contents'] = gzencode($params['contents']);
            }
        }

        // File can not be accessible via direct link
        if ($this->getOption('secured')) {
            $data['headers']['Content-disposition'] = 'attachment; filename="' . fn_basename($file) . '"';
            $data['acl'] = \AmazonS3::ACL_PRIVATE;
        }

        $data['contentType'] = fn_get_file_type($file);

        if (!empty($params['contents'])) {
            $data['body'] = $params['contents'];
        } else {
            $data['fileUpload'] = $params['file'];
        }

        $res = $s3->create_object($this->getOption('bucket'), $file, $data);

        if ($res->isOK()) {
            if (!empty($params['caching'])) {
                Registry::set('s3_' . $this->getOption('bucket') . '.' . md5($file), true);
            }

            if (!empty($params['file'])) {
                $filesize = filesize($params['file']);

                if (empty($params['keep_origins'])) {
                    fn_rm($params['file']);
                }
            } else {
                $filesize = strlen($params['contents']);
            }

            return array($filesize, str_replace($this->prefix(), '', $file));
        }

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
        $s3 = $this->s3(); // get object to initialize class and get access to contstants below

        $i = 0;
        $max_batch = 10;

        $files = fn_get_dir_contents($dir, false, true, '', '', true);
        fn_set_progress('step_scale', sizeof($files));

        foreach ($files as $source_file) {
            fn_set_progress('echo', '.');

            $i++;
            $data = array(
                'acl' => \AmazonS3::ACL_PUBLIC,
                'headers' => array()
            );

            // File can not be accessible via direct link
            if ($this->getOption('secured')) {
                $data['headers']['Content-disposition'] = 'attachment; filename="' . fn_basename($source_file) . '"';
                $data['acl'] = \AmazonS3::ACL_PRIVATE;
            }

            $data['contentType'] = fn_get_file_type($source_file);
            $data['fileUpload'] = $dir . '/' . $source_file;

            $res = $s3->batch()->create_object($this->getOption('bucket'), $this->prefix($source_file), $data);

            if ($i == $max_batch) {
                $s3->batch()->send();
                $i = 0;
            }
        }

        if (!empty($i)) {
            $s3->batch()->send(); // send the rest of the batch
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

        if ($protocol == 'http') {
            $prefix = 'http://';
        } elseif ($protocol == 'https') {
            $prefix = 'https://';
        } elseif ($protocol == 'short') {
            $prefix = '//';
        } else {
            $prefix = defined('HTTPS') ? 'https://' : 'http://';
        }

        $host = $this->getOption('host');
        if (empty($host)) {
            $host = $this->getOption('region');
        }
        $host .= '/' . $this->getOption('bucket');

        return $prefix . $host . '/' . $this->prefix($file);
    }

    /**
     * Gets absolute path to file
     *
     * @param  string $file file to get path
     * @return string absolute path
     */
    public function getAbsolutePath($file)
    {
        return $this->getUrl($file);
    }

    /**
     * Push file contents to browser, link to file is active for one hour
     *
     * @param  string $file     file to push
     * @param  string $filename file name to be displayed in download dialog, not supported
     * @return void
     */
    public function get($file, $filename = '')
    {
        header('Location: ' . $this->s3()->get_object_url($this->getOption('bucket'), $this->prefix($file), TIME + SECONDS_IN_HOUR));
    }

    /**
     * Deletes file
     *
     * @param  string  $file file to delete
     * @return boolean true if deleted successfully, false - otherwise
     */
    public function delete($file)
    {
        $file = $this->prefix($file);
        if ($this->s3()->delete_object($this->getOption('bucket'), $file)) {
            $cache_name = 's3_' . $this->getOption('bucket');
            Registry::registerCache($cache_name, array(), Registry::cacheLevel('static'), true);
            Registry::del($cache_name . '.' . md5($file));

            return true;
        }

        return false;
    }

    /**
     * Deletes directory and all it files
     *
     * @param  string  $dir directory to delete
     * @return boolean true if deleted successfully, false - otherwise
     */
    public function deleteDir($dir = '')
    {
        $dir = rtrim($this->prefix($dir), '/') . '/';
        if ($this->s3()->delete_all_objects($this->getOption('bucket'), '/^' . preg_quote($dir, '/') . '/i')) {
            return true;
        }

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
        $p = preg_quote($this->prefix($pattern), '/');
        $p = str_replace('\*', '[^\/]*', $p);
        $p = str_replace('\?', '.', $p);

        if ($this->s3()->delete_all_objects($this->getOption('bucket'), '/' . $p . '/i')) {
            return true;
        }

        return false;
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
        $file = $this->prefix($file);

        $cache_name = 's3_' . $this->getOption('bucket');
        Registry::registerCache($cache_name, array(), Registry::cacheLevel('static'), true);
        $is_exist = Registry::get($cache_name . '.' . md5($file));

        if ($in_cache == false && $is_exist == false && $is_exist = $this->s3()->if_object_exists($this->getOption('bucket'), $file)) {
            Registry::set($cache_name . '.' . md5($file), true);
        }

        return $is_exist;
    }

    /**
     * Copy files inside storage (FIXME: now supports max 1000 items to copy)
     *
     * @param  string  $src  source file/directory
     * @param  string  $dest destination file/directory
     * @return boolean true if copied successfully, false - otherwise
     */
    public function copy($src, $dest)
    {
        $src = $this->prefix($src);
        $dest = $this->prefix($dest);

        $items = $this->s3()->get_object_list($this->getOption('bucket'), array(
            'prefix' => $src
        ));

        if (!empty($items)) {
            foreach ($items as $item) {

                $_dest = substr_replace($item, $dest, strlen($src));
                $this->s3()->batch()->copy_object(
                    array(
                        'bucket' => $this->getOption('bucket'),
                        'filename' => $item
                    ),
                    array(
                        'bucket' => $this->getOption('bucket'),
                        'filename' => $_dest
                    ),
                    array(
                        'acl' => \AmazonS3::ACL_PUBLIC
                    )
                );
            }

            $res = $this->s3()->batch()->send();

            return true;
        }

        return false;
    }

    /**
     * Lists files
     * @param  string $prefix path prefix
     * @return array  files list
     */
    public function getList($prefix = '')
    {
        $prefix = $this->prefix($prefix);
        $items = $this->s3()->get_object_list($this->getOption('bucket'), array(
            'prefix' => $prefix
        ));

        if (!empty($items)) {
            $prefix_len = strlen($prefix);
            foreach ($items as $item_key => $item) {
                $items[$item_key] = substr_replace($item, '', 0, $prefix_len);
            }
        }

        return $items;
    }

    /**
     * Adds prefix to file path
     *
     * @param  string $file file
     * @return string prefixed file path
     */
    protected function prefix($file = '')
    {
        $path = parent::prefix($file);

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
        $old_options = $this->options;

        $this->options = fn_array_merge($this->options, $settings);
        $this->_s3 = null;

        $result = $this->s3(true);

        $this->_s3 = null;
        $this->options = $old_options;

        if (is_object($result)) {
            return true;
        }

        return $result;
    }

    /**
     * Gets s3 object
     *
     * @param  boolean   $debug return error message instead of script stop
     * @return \AmazonS3 s3 object
     */
    public function s3($debug = false)
    {
        // This is workaround to composer autoloader
        if (!class_exists('CFLoader')) {
            throw new ClassNotFoundException('Amazon: autoload failed');
        }

        if (empty($this->_s3)) {
            \CFCredentials::set(array(
                '@default' => array(
                    'key' => $this->getOption('key'),
                    'secret' => $this->getOption('secret')
                )
            ));

            $this->_s3 = new \AmazonS3();
            $this->_s3->use_ssl = false;
            $this->_buckets = fn_array_combine($this->_s3->get_bucket_list(), true);
        }

        $message = '';
        $bucket = $this->getOption('bucket');
        if (empty($this->_buckets[$bucket])) {
            $res = $this->_s3->create_bucket($bucket, $this->getOption('region'));

            if ($res->isOK()) {
                $res = $this->_s3->create_cors_config($bucket, array(
                    'cors_rule' => array(
                        array(
                            'allowed_origin' => '*',
                            'allowed_method' => 'GET'
                        )
                    )
                ));

                if ($res->isOK()) {
                    $this->_buckets[$bucket] = true;
                } else {
                    $message = (string) $res->body->Message;
                }
            } else {
                $message = (string) $res->body->Message;
            }
        }

        if (!empty($message)) {
            if ($debug == true) {
                return $message;
            }

            throw new ExternalException('Amazon: ' . $message);
        }

        return $this->_s3;
    }
}
