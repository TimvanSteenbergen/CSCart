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

namespace Tygh\SmartyEngine;

use Tygh\Registry;

class ViewDeprecated
{
    public function __construct($object = 'view')
    {
        $this->object = $object;
    }

    public function __call($name, $arguments)
    {
        $this->_triggerDeprecatedError($name);

        $view = Registry::get('view');
        if (is_callable(array($view, $name))) {
            return call_user_func_array(array($view, $name), $arguments);
        } else {
            return false;
        }
    }

    private function _triggerDeprecatedError($method)
    {
        $msg = '';
        $backtrace = debug_backtrace();
        if (!empty($backtrace[1]['file']) && !empty($backtrace[1]['line'])) {
            $msg .= 'File: ' . $backtrace[1]['file'] . ', line: '. $backtrace[1]['line'] . ': ';
        }
        $msg .= "Called method '$method' of deprecated global object \$$this->object. Please get access to templater through the Registry object.";
        trigger_error($msg, E_USER_DEPRECATED);
    }
}
