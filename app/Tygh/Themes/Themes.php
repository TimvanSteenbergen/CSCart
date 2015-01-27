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

use Tygh\Less;
use Tygh\Registry;
use Tygh\BlockManager\Layout;
use Tygh\Storage;
use Tygh\Themes\Styles;

class Themes
{
    public static $compiled_less_filename = 'styles.pcl.css';
    public static $less_backup_dirname = '__less_backup';
    public static $css_backup_dirname = '__css_backup';

    private static $instances = array();

    protected $less = null;
    protected $less_reflection = null;
    protected $theme_name = '';
    protected $theme_path = '';
    protected $relative_path = '';
    protected $repo_path = '';
    protected $manifest = array();

    public function __construct($theme_name)
    {
        $this->theme_name = $theme_name;
        $this->theme_path = fn_get_theme_path('[themes]/' . $theme_name, 'C');
        $this->relative_path = fn_get_theme_path('[relative]/' . $theme_name, 'C');
        $this->repo_path = fn_get_theme_path('[repo]/' . $theme_name, 'C');
    }

    /**
     * Convert theme LESS to CSS files
     *
     * @return boalean Result
     */
    public function convertToCss()
    {
        if (!file_exists($this->theme_path . '/' . THEME_MANIFEST)) {
            fn_put_contents($this->theme_path . '/' . THEME_MANIFEST, '');
        }

        if (!is_writable($this->theme_path . '/' . THEME_MANIFEST)) {
            return false;
        }

        $theme_css_path = $this->theme_path . '/css';

        $less_reflection = $this->getLessReflection();

        if (!empty($less_reflection['output']['main'])) {

            $exclude = array(
                'addons', self::$less_backup_dirname, self::$css_backup_dirname
            );

            if (!(
                $this->convertChunkToCss($less_reflection['output']['main'], $theme_css_path)
                && $this->removeLessFiles($theme_css_path, $theme_css_path . '/' . self::$less_backup_dirname, $exclude)
            )) {
                return false;
            }

        }

        if (!empty($less_reflection['output']['addons'])) {
            foreach ($less_reflection['output']['addons'] as $addon_name => $addon_less_output) {
                if (!empty($addon_less_output)) {
                    if (!$this->convertAddonToCss($addon_name, $addon_less_output)) {
                        return false;
                    }
                }
            }
        }

        $manifest = &$this->getManifest();
        $manifest['converted_to_css'] = true;

        return $this->saveManifest();
    }

    /**
     * Precompile addon LESS
     *
     * @param string $addon             Addon name
     * @param string $addon_less_output Addon less output
     *
     * @return boolean Result
     */
    public function convertAddonToCss($addon, $addon_less_output = '')
    {
        $manifest = &$this->getManifest();

        $_temporary_restore_less = false;

        if (!empty($manifest['converted_to_css'])) {
            $_temporary_restore_less = true;
            $this->restoreLess(false);
        }

        if (empty($addon_less_output)) {
            $less_reflection = $this->getLessReflection();
            $addon_less_output = '';
            if (!empty($less_reflection['output']['addons'][$addon])) {
                $addon_less_output = $less_reflection['output']['addons'][$addon];
            }
        }

        if ($_temporary_restore_less) {
            $exclude = array(
                'addons', self::$less_backup_dirname, self::$css_backup_dirname
            );
            $this->removeLessFiles($this->theme_path . '/css', null, $exclude);
            $manifest['converted_to_css'] = true;
            $this->saveManifest();
        }

        $addon_css_path = $this->theme_path . '/css/addons/' . $addon;
        $addon_less_backup_path = $this->theme_path . '/css/' . self::$less_backup_dirname . '/addons/' . $addon;

        if (!(
            $this->convertChunkToCss($addon_less_output, $addon_css_path)
            && $this->removeLessFiles($addon_css_path, $addon_less_backup_path)
        )) {
            return false;
        }

        return true;
    }

    /**
     * Get CSS content from a file
     *
     * @param mixed $filename CSS file name or relative path
     *
     * @return mixed CSS content or false on failure
     */
    public function getCssContents($filename = null)
    {
        if (is_null($filename)) {
            $filename = Themes::$compiled_less_filename;
        }

        return fn_get_contents($this->theme_path . '/css/' . $filename);
    }

    /**
     * Update CSS file
     *
     * @param string $css_file    CSS file name or relative path
     * @param string $css_content CSS content
     *
     * @return boolean Result
     */
    public function updateCssFile($css_file, $css_content)
    {
        return fn_put_contents($this->theme_path . '/css/' . $css_file, $css_content);
    }

    /**
     * Restore LESS files and remove precompiled LESS files
     *
     * @return bolean Result
     */
    public function restoreLess($remove_precompiled_less = true)
    {
        if (!file_exists($this->theme_path . '/' . THEME_MANIFEST)) {
            fn_put_contents($this->theme_path . '/' . THEME_MANIFEST, '');
        }

        if (!is_writable($this->theme_path . '/' . THEME_MANIFEST)) {
            return false;
        }

        $theme_css_path = $this->theme_path . '/css';

        $less_backup_path = $theme_css_path . '/' . self::$less_backup_dirname;

        if (!is_dir($less_backup_path)) {
            return false;
        }

        if (!fn_copy($less_backup_path, $theme_css_path)) {
            return false;
        }

        if ($remove_precompiled_less) {
            $this->removePrecompiledLess();
        }

        $manifest = &$this->getManifest();
        $manifest['converted_to_css'] = false;

        return $this->saveManifest();
    }

    /**
     * Remove precompiled LESS files
     *
     * @return boolean Result
     */
    public function removePrecompiledLess()
    {
        $theme_css_path = $this->theme_path . '/css';

        $exclude = array(
            self::$less_backup_dirname, self::$css_backup_dirname
        );

        $precompiled_files = fn_get_dir_contents(
            $theme_css_path, false, true, self::$compiled_less_filename, '', true, $exclude
        );

        foreach ($precompiled_files as $pcl_file) {

            $pcl_filepath = $theme_css_path . '/' . $pcl_file;
            $css_backup_filepath = $theme_css_path . '/' . self::$css_backup_dirname . '/' . $pcl_file;

            if (!fn_mkdir(dirname($css_backup_filepath)) || !fn_copy($pcl_filepath, $css_backup_filepath)) {
                return false;
            }

            fn_rm($pcl_filepath);
        }

        return true;
    }

    /**
     * Get theme CSS files list
     *
     * @return array CSS files list
     */
    public function getCssFilesList()
    {
        $from = $this->theme_path . '/css';
        $exclude = array('addons', self::$less_backup_dirname, self::$css_backup_dirname);

        $css_files = fn_get_dir_contents($from, false, true, '.css', '', true, $exclude);

        list($active_addons) = fn_get_addons(array('type' => 'active'));

        foreach ($active_addons as $addon_name => $addon) {
            $css_files = array_merge(
                $css_files,
                fn_get_dir_contents($from . "/addons/$addon_name", false, true, '.css', "addons/$addon_name/", true)
            );
        }

        return $css_files;
    }

    /**
     * Get URL to the file with joint theme CSS
     *
     * @return mixed Url or false on failure
     */
    public function getCssUrl()
    {
        $res = $this->fetchFrontendStyles();

        if (!preg_match('/href="([^"]+)"/is', $res, $m)) {
            return false;
        }

        return $m[1];
    }

    /**
     * Get theme manifest information
     *
     * @return array Manifest information
     */
    public function &getManifest()
    {
        if (empty($this->manifest)) {
            if (file_exists($this->theme_path . '/' . THEME_MANIFEST)) {
                $manifest_path = $this->theme_path . '/' . THEME_MANIFEST;

                $ret = json_decode(fn_get_contents($manifest_path), true);
            } elseif (file_exists($this->theme_path . '/' . THEME_MANIFEST_INI)) {
                $ret = parse_ini_file($this->theme_path . '/' . THEME_MANIFEST_INI);
            } else {
                $ret = array();
            }

            if ($ret) {
                $this->manifest = $ret;
            }
        }

        // Backward compatibility: "Theme" parameter will be completely removed in 4.4 version
        if (isset($this->manifest['logo'])) {
            $this->manifest['theme'] = $this->manifest['logo'];
        }

        return $this->manifest;
    }

    /**
     * Get theme manifest information from Themes repository
     *
     * @return array Manifest information
     */
    public function getRepoManifest()
    {
        $ret = '';

        if (file_exists($this->repo_path . '/' . THEME_MANIFEST)) {
            $manifest_path = $this->repo_path . '/' . THEME_MANIFEST;

            $ret = json_decode(fn_get_contents($manifest_path), true);
        } elseif (file_exists($this->repo_path . '/' . THEME_MANIFEST_INI)) {
            $ret = parse_ini_file($this->repo_path . '/' . THEME_MANIFEST_INI);
        }

        return $ret;
    }

    /**
     * Save theme manifest information
     *
     * @return boolean Result
     */
    public function saveManifest()
    {
        if (empty($this->manifest)) {
            return false;
        }

        return fn_put_contents($this->theme_path . '/' . THEME_MANIFEST, json_encode($this->manifest));
    }

    /**
     * Get theme name
     *
     * @return string Theme name
     */
    public function getThemeName()
    {
        return $this->theme_name;
    }

    /**
     * Get theme path
     *
     * @return string Theme path
     */
    public function getThemePath()
    {
        return $this->theme_path;
    }

    public static function factory($theme_name)
    {
        if (empty(self::$instances[$theme_name])) {
            self::$instances[$theme_name] = new self($theme_name);
        }

        return self::$instances[$theme_name];
    }

    /**
     * Get LESS reflection (information necessary to precompile LESS): LESS import dirs and structured output
     *
     * @return array LESS reflection
     */
    protected function getLessReflection()
    {
        if (empty($this->less_reflection)) {

            $this->fetchFrontendStyles(array('reflect_less' => true));

            $this->less_reflection = json_decode(
                fn_get_contents(fn_get_cache_path(false) . 'less_reflection.json'), true
            );
        }

        return $this->less_reflection;
    }

    /**
     * Fetch frontend styles
     *
     * @param array Params
     *
     * @return string Frontend styles
     */
    protected function fetchFrontendStyles($params = array())
    {
        fn_clear_cache('statics', 'design/');

        $style_id = Registry::get('runtime.layout.style_id');
        if (empty($style_id)) {
            Registry::set('runtime.layout.style_id', Styles::factory($this->theme_name)->getDefault());
        }

        $view = Registry::get('view');

        $view->setArea('C');

        $view->assign('use_scheme', true);
        $view->assign('include_dropdown', true);

        foreach ($params as $key => $val) {
            $view->assign($key, $val);
        }

        $ret = $view->fetch('common/styles.tpl');

        $view->setArea(AREA);

        return $ret;
    }

    /**
     * Compile chunk of LESS output and save the result in the file
     *
     * @param string $less_output Chunk of LESS output
     * @param string $css_path    The path where the precompiled LESS will be saved
     *
     * @return boolean Result
     */
    protected function convertChunkToCss($less_output, $css_path)
    {
        $less = $this->getLess();

        $less_reflection = $this->getLessReflection();

        $less->setImportDir($less_reflection['import_dirs']);

        Registry::set('runtime.layout', Layout::instance()->getDefault($this->theme_name));

        $from_path = Storage::instance('statics')->getAbsolutePath($this->relative_path . '/css');

        $compiled_less = $less->customCompile($less_output, $from_path, array(), '', 'C');

        $res = fn_put_contents($css_path . '/' . self::$compiled_less_filename, $compiled_less);

        if ($res === false) {
            return false;
        }

        return true;
    }

    /**
     * Remove LESS files
     *
     * @param string $from       The directory the LESS files are removed from
     * @param string $backup_dir Backup directory
     * @param array  $exclude    The list of directories to skip while removing
     *
     * @return boolean Result
     */
    protected function removeLessFiles($from, $backup_dir, $exclude = array())
    {
        $less_files = fn_get_dir_contents($from, false, true, '.less', '', true, $exclude);

        foreach ($less_files as $less_file) {

            if (!empty($backup_dir)) {

                if (!(
                    fn_mkdir(dirname($backup_dir . '/' . $less_file))
                    && fn_copy($from . '/' . $less_file, $backup_dir . '/' . $less_file)
                )) {
                    return false;
                }

            }

            fn_rm($from . '/' . $less_file);
        }

        return true;
    }

    /**
     * Get LESS compiler instance
     *
     * @return object LESS compiler instance
     */
    protected function getLess()
    {
        if ($this->less === null) {
            $this->less = new Less;
        }

        return $this->less;
    }

}
