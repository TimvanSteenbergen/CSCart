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

use Tygh\Registry;

class CompanySingleton
{
    public $params = array(); // addon can pass params here to use them in hooks later

    /**
     * @var array Array of object instances
     */
    protected static $_instance;

    /**
     * @var int Company identifier
     */
    protected $_company_id;

    /**
     * Gets part of SQL query with company condition
     * @param  string $db_field database field which holds company ID
     * @return string part of SQL query
     */
    public function getCompanyCondition($db_field)
    {
        $company_id = $this->_company_id;

        if (!$this->_company_id) {
            $company_id = '';
        }

        return fn_get_company_condition($db_field, true, $company_id);
    }

    /**
     * Returns object instance of this class or create it if it is not exists.
     * @static
     * @param  int              $company_id Company identifier
     * @param  array            $params     additional params
     * @return CompanySingleton
     */
    public static function instance($company_id = 0, $params = array())
    {
        $class_name = get_called_class();
        $company_id = self::getCompany($company_id);
        $instance_key = $class_name . $company_id;

        if (empty(self::$_instance[$instance_key])) {
            self::$_instance[$instance_key] = new $class_name();
            self::$_instance[$instance_key]->_company_id = $company_id;
        }

        self::$_instance[$instance_key]->params = $params;

        return self::$_instance[$instance_key];
    }

    /**
     * Gets current company ID
     * @param  integer $company_id company ID
     * @return integer company ID
     */
    private static function getCompany($company_id = 0)
    {
        if (empty($company_id) && Registry::get('runtime.company_id')) {
            $company_id = Registry::get('runtime.company_id');
        }

        return $company_id;
    }

    /**
     * We Can create object only inside it
     */
    protected function __construct() {}
}
