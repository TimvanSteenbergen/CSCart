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

namespace Tygh\StoreImport\Pro;
use Tygh\StoreImport\General;
use Tygh\Registry;

class F301T302
{
    protected $store_data = array();
    protected $main_sql_filename = 'pro_F301T302.sql';

    public function __construct($store_data)
    {
        $store_data['product_edition'] = 'PROFESSIONAL';
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

        $main_sql = Registry::get('config.dir.addons') . 'store_import/database/' . $this->main_sql_filename;
        if (is_file($main_sql)) {
            //Process main sql
            if (!db_import_sql_file($main_sql)) {
                return false;
            }
        }

        General::setEmptyProgressBar(General::getUnavailableLangVar('updating_languages'));
        General::updateAltLanguages('language_values', 'name');
        General::updateAltLanguages('settings_descriptions', array('object_id', 'object_type'));
        General::updateAltLanguages('state_descriptions', 'state_id');
        General::updateAltLanguages('shipping_service_descriptions', 'service_id');
        General::updateAltLanguages('privilege_descriptions', 'privilege');
        General::updateAltLanguages('privilege_section_descriptions', 'section_id');
        General::updateAltLanguages('country_descriptions', 'code');

        db_query('DROP TABLE IF EXISTS ?:se_queue');

        General::setEmptyProgressBar();
        General::processBlocks();

        if (db_get_field("SHOW TABLES LIKE '?:mailing_lists'")) {
            db_query("ALTER TABLE ?:mailing_lists DROP `show_on_sidebar`");
        }

        General::setEmptyProgressBar();

        return true;
    }
}
