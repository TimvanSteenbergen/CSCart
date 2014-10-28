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
use Tygh\Themes\Styles;
use Tygh\Registry;

class Less extends \lessc
{
    protected $overrides = array();

    /**
     * Override default lessc method
     */
    public function injectVariables($args)
    {
        $this->pushEnv();
        $parser = new \lessc_parser($this, __METHOD__);
        foreach ($args as $name => $strValue) {
            if ($name{0} != '@') $name = '@'.$name;
            $parser->count = 0;
            $parser->buffer = (string) $strValue;
            if (!$parser->propertyValue($value)) {
                throw new \Exception("failed to parse passed in variable $name: $strValue");
            }

            $this->overrides[$name] = $value;
        }
    }

    /**
     * Override default lessc method
     */
    public function extractVars($less)
    {
        $vars = array();
        $this->compile($less);

        if (!empty($this->parser->env->props)) {
            foreach ($this->parser->env->props as $prop) {
                if ($prop[0] == 'assign') {
                    list(, $var_name, $value) = $prop;

                    $var_name = str_replace('@', '', $var_name);
                    $vars[$var_name] = $this->_parseVarValue($value);
                }
            }
        }

        return $vars;
    }

    /**
     * Compile LESS to CSS, appending data from styles and parsing urls
     * @param  string $less_output    LESS code
     * @param  string $dirname        absolute path where compiled file will be saved (to parse URLs correctly)
     * @param  array  $data           style data
     * @param  string $prepend_prefix prefix to prepend all selectors (for widget mode)
     * @param  string $area current working area
     * @return string CSS code
     */
    public function customCompile($less_output, $dirname, $data = array(), $prepend_prefix = '', $area = AREA)
    {
        // Apply all Custom styles styles
        if ($area == 'C') {
            $less_output .= Styles::factory(fn_get_theme_path('[theme]', $area))->getLess($data);

            // Inject Bootstrap fluid variables
            $less_output .= self::getLayoutStyleVariables();
        }

        if (!empty($prepend_prefix)) {
            $less_output = $prepend_prefix . " {\n" . $less_output . "\n}";
        }

        $output = $this->parse($less_output);

        // Remove "body" definition
        if (!empty($prepend_prefix)) {
            $output = str_replace($prepend_prefix . ' body', $prepend_prefix, $output);
        }

        return Less::parseUrls($output, $dirname, fn_get_theme_path('[themes]/[theme]/media'));

    }

    /**
     * Gets data from layout to pass it to LESS
     * @param  string $layout_data layout data
     * @return string LESS code
     */
    public static function getLayoutStyleVariables($layout_data = array())
    {
        if (empty($layout_data)) {
            $layout_data = Registry::get('runtime.layout');
        }

        $variables = '';

        if ($layout_data['layout_width'] == 'fluid') {
            $variables = self::arrayToLessVars(array(
                'fluidContainerMinWidth' => $layout_data['min_width'] . 'px',
                'fluidContainerMaxWidth' => $layout_data['max_width'] . 'px',
            ));

        } elseif ($layout_data['layout_width'] == 'full_width') {
            $variables = self::arrayToLessVars(array(
                'fluidContainerMinWidth' => 'auto',
                'fluidContainerMaxWidth' => 'auto',
            ));
        }

        if (!empty($layout_data['width'])) {
            $variables .= self::arrayToLessVars(array(
                'gridColumns' => $layout_data['width'],
            ));
        }

        return $variables;
    }

    /**
     * Converts array with LESS vars to LESS code
     * @param  array  $vars LESS vars
     * @return string LESS code
     */
    public static function arrayToLessVars($vars)
    {
        $less = '';

        foreach ($vars as $var_name => $value) {
            $less .= '@' . $var_name . ': ' . $value . ";\n";
        }

        return $less;
    }

    /**
     * Parses CSS code to make correct relative URLs in case of CSS/LESS files compiled and placed to another directory
     * @param  string $content   CSS/LESS code
     * @param  string $from_path path, where original CSS/LESS file is placed
     * @param  string $to_path   path, where compiled CSS/LESS file is placed
     * @return string parsed content
     */
    public static function parseUrls($content, $from_path, $to_path)
    {
        if (preg_match_all("/url\((?![\"]?data\:).*?\)/", $content, $m)) {
            $relative_path = self::_relativePath($from_path, $to_path);

            foreach ($m[0] as $match) {
                if (strpos($match, '?') === false) { // if ? is added - it means that this url is already parsed
                    $url = trim(str_replace('url(', '', $match), "'()\"");
                    if (strpos($url, '://') !== false || strpos($url, '//') === 0) { // skip absolute URLs
                        continue;
                    }

                    $url = $relative_path . '/' . preg_replace("/^(\.\.\/)+media\//", '', $url);

                    $content = str_replace($match, "url('" . $url . '?' . TIME . "')", $content);
                }
            }
        }

        return $content;
    }

    /**
     * Default method override
     */
    protected function get($name, $default=null)
    {
        $current = $this->env;

        $isArguments = $name == $this->vPrefix . 'arguments';

        while ($current) {
            if ($isArguments && isset($current->arguments)) {
                return array('list', ' ', $current->arguments);
            }

            if (isset($this->overrides[$name])) {
                return $this->overrides[$name];
            } elseif (isset($current->store[$name]))

                return $current->store[$name];
            else {
                $current = isset($current->storeParent) ? $current->storeParent : $current->parent;
            }
        }

        return $default;
    }

    /**
     * Creates relative path from one directory to another
     * @param  string $from from directory
     * @param  string $to   to directory
     * @return string relative path
     */
    private static function _relativePath($from, $to)
    {
        $from = fn_normalize_path($from);
        $to = fn_normalize_path($to);

        $_from = explode('/', rtrim($from, '/'));
        $_to = explode('/', rtrim($to, '/'));

        while (count($_from) && count($_to) && ($_from[0] == $_to[0])) {
            array_shift($_from);
            array_shift($_to);
        }

        return str_pad('', count($_from) * 3, '../') . implode('/', $_to);
    }

    /**
     * Gets LESS variable value
     * @param  array  $value LESS variabe
     * @return string value
     */
    private function _parseVarValue($value)
    {
        $result = '';

        switch ($value[0]) {
            case 'keyword': case 'raw_color':
                $result = $value[1];

                break;

            case 'list':
                $delimiter = $value[1];

                foreach ($value[2] as $iteration => $_val) {
                    $result .= $this->_parseVarValue($_val);

                    if (++$iteration < count($value[2])) {
                        $result .= $delimiter;
                    }
                }

                $result = trim($result);

                break;

            case 'number':
                $number = $value[1];
                $metric = $value[2];

                $result = $number . $metric;

                break;

            case 'string':
                $delimiter = $value[1];
                $result = $delimiter . implode('', $value[2]) . $delimiter;

                break;

            case 'function':
                $function_name = $value[1];
                $result = $function_name . '(' . $this->_parseVarValue($value[2]) . ')';

                break;
            case 'escape':
                $result = '';
                break;

            default:
                $result = $value;
                break;
        }

        $result = preg_replace('/,\s+/', ',', $result);

        return $result;
    }
}
