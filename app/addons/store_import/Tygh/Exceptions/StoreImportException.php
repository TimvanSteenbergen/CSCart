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

namespace Tygh\Exceptions;

use Tygh\Registry;
use Tygh\Logger;

class StoreImportException extends AException
{
    const ERROR_HEADER = 'Error';

    /**
     * Outputs exception information
     * @return type
     */
    public function output()
    {
        $message = $this->message;

        fn_set_notification('E', self::ERROR_HEADER, $message);

        if ($this->getTrace()) {
            $params = array('db_host', 'db_name', 'db_user', 'db_password', 'crypt_key');
            $pattern = '/(\s*\[.*?(' . implode('|', $params) . ')[^\[\]]*\] => )[^\s]*/iS';
            $message .= preg_replace($pattern, '$1*****', print_r($this->getTrace(), true));
        }

        Logger::instance()->write($message);

        $filename = Registry::get('config.dir.database') . 'export.sql';
        if (file_exists($filename)) {
            $new_filename = $filename . '.' . date('Y-m-d_H-i') . '.sql';
            fn_rename($filename, $new_filename);
        }
        exit;
    }   
}
