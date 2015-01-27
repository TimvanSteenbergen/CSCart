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

namespace Tygh\StoreImport\Mve;
use Tygh\StoreImport\General;
use Tygh\Registry;

class F422T423
{
    protected $store_data = array();
    protected $main_sql_filename = 'mve_F422T423.sql';

    public function __construct($store_data)
    {
        $store_data['product_edition'] = 'ULTIMATE';
        $this->store_data = $store_data;
    }

    public function import($db_already_cloned)
    {
        General::setProgressTitle(__CLASS__);
        if (!$db_already_cloned) {
            if (!General::cloneImportedDB($this->store_data)) {
                return false;
            }
        } else {
            General::setEmptyProgressBar(__('importing_data'));
            General::setEmptyProgressBar(__('importing_data'));
        }

        General::connectToOriginalDB(array('table_prefix' => General::formatPrefix()));
        General::processAddons($this->store_data, __CLASS__);

        $main_sql = Registry::get('config.dir.addons') . 'store_import/database/' . $this->main_sql_filename;
        if (is_file($main_sql)) {
            //Process main sql
            if (!db_import_sql_file($main_sql)) {
                return false;
            }
        }

//        General::restoreSettings();
        if (db_get_field("SELECT status FROM ?:addons WHERE addon = 'searchanise'") != 'D') {
            db_query("UPDATE ?:addons SET status = 'D' WHERE addon = 'searchanise'");
            fn_set_notification('W', __('warning'), General::getUnavailableLangVar('uc_searchanise_disabled'));
        }

        db_query("ALTER TABLE ?:currencies CHANGE `decimals_separator` `decimals_separator` varchar(6) NOT NULL DEFAULT '.'");
        db_query("ALTER TABLE ?:currencies CHANGE `thousands_separator` `thousands_separator` varchar(6) NOT NULL DEFAULT ','");
        db_query("ALTER TABLE ?:bm_locations ADD `custom_html` TEXT NOT NULL");
        db_query("ALTER TABLE ?:order_data CHANGE `data` `data` LONGBLOB  NOT NULL");
        db_query("ALTER TABLE ?:order_details CHANGE `extra` `extra` LONGBLOB  NOT NULL");
        db_query("ALTER TABLE ?:order_transactions CHANGE `extra` `extra` LONGBLOB  NOT NULL");

        db_query('UPDATE ?:shipping_services SET `code` = "Media Mail Parcel" WHERE `module` = "usps" AND `code` = "Media Mail"');
        db_query('UPDATE ?:shipping_services SET `code` = "Library Mail Parcel" WHERE `module` = "usps" AND `code` = "Library Mail"');

        db_query("ALTER TABLE ?:categories ADD `level` INT(11) UNSIGNED NOT NULL DEFAULT '1' AFTER `id_path`");
        db_query("UPDATE ?:categories AS `c` SET `c`.`level` = ((length(`c`.`id_path`) - length(REPLACE(`c`.`id_path`, '/', ''))) + 1)");

        General::setActualLangValues();
        General::updateAltLanguages('language_values', 'name');
        General::updateAltLanguages('settings_descriptions', array('object_id', 'object_type'));

        General::setEmptyProgressBar();
        General::setEmptyProgressBar();
        General::setEmptyProgressBar();
        General::setEmptyProgressBar();
        return true;
    }
}
