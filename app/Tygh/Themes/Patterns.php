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

namespace Tygh\Themes;

use Tygh\Registry;

class Patterns
{
    public $params = array(); // addon can pass params here to use them in hooks later

    private static $instance;

    /**
     * Gets theme patterns
     * @param  string  $style_id   style ID
     * @param  boolean $url_prefix prefix files with URL if set to true
     * @return array   theme patterns
     */
    public function get($style_id, $url_prefix = true)
    {
        $style_id = fn_basename($style_id);

        $patterns = $this->getPath($style_id);
        $url = $this->getUrl($style_id);

        $prefix = $patterns . '/';
        if ($url_prefix) {
            $prefix = Registry::get('config.current_location') . '/' . fn_get_rel_dir($this->getPath($style_id) . '/');
        }

        return fn_get_dir_contents($patterns, false, true, '', $prefix);
    }

    /**
     * Saves uploaded pattern to theme
     * @param  string $style_id      style ID
     * @param  array  $style         style
     * @param  array  $uploaded_data uploaded data
     * @return array  modified style
     */
    public function save($style_id, $style, $uploaded_data)
    {
        $style_id = fn_basename($style_id);
        $patterns = $this->getPath($style_id);
        if (!is_dir($patterns)) {
            fn_mkdir($patterns);
        }

        foreach ($uploaded_data as $var => $file) {
            $fname = $var . '.' . fn_get_file_ext($file['name']);

            if (fn_copy($file['path'], $patterns . '/' . $fname)) {
                $style['data'][$var] = "url(../" . $this->getRelPath($style_id) . $fname . ")";
            }
        }

        return $style;
    }

    /**
     * Gets patterns absolute path
     * @param  string $style_id style ID
     * @return string patterns absolute path
     */
    public function getPath($style_id)
    {
        $path = fn_get_theme_path('[themes]/[theme]/media/images/patterns/', 'C');

        /**
         * Modifies path to patterns
         *
         * @param object  $this Patterns object
         * @param string  $path current path
         * @param string  $style_id style to get path for
         */
        fn_set_hook('patterns_get_path', $this, $path, $style_id);

        return $path . fn_basename($style_id);
    }

    /**
     * Gets patterns relative (to theme root) path
     * @param  string $style_id style ID
     * @return string patterns relative path
     */
    public function getRelPath($style_id)
    {
        return 'media/images/patterns/' . fn_basename($style_id) . '/';
    }

    /**
     * Gets pattern URL
     * @param  string  $style_id style ID
     * @param  boolean $root     get style root directory if set to true
     * @return string  pattern URL
     */
    public function getUrl($style_id, $root = false)
    {
        $patterns = $this->getPath($style_id);
        $url = Registry::get('config.current_location') . '/' . fn_get_rel_dir($patterns) . '/';
        if ($root == true) {
            $url = str_replace('/' . $this->getRelPath($style_id), '', $url);
        }

        return $url;
    }

    public static function instance($params = array())
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        self::$instance->params = $params;

        return self::$instance;
    }
}
