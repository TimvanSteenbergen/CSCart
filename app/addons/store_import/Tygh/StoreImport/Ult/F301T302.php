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

namespace Tygh\StoreImport\Ult;
use Tygh\StoreImport\General;
use Tygh\Registry;

class F301T302
{
    protected $store_data = array();
    protected $main_sql_filename = 'ult_F301T302.sql';

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

        $main_sql = Registry::get('config.dir.addons') . 'store_import/database/' . $this->main_sql_filename;
        if (is_file($main_sql)) {
            //Process main sql
            if (!db_import_sql_file($main_sql)) {
                return false;
            }
        }

        General::processAddons($this->store_data, __CLASS__);

        General::setEmptyProgressBar(General::getUnavailableLangVar('updating_languages'));
        General::updateAltLanguages('language_values', 'name');
        General::updateAltLanguages('settings_descriptions', array('object_id', 'object_type'));
        General::updateAltLanguages('shipping_service_descriptions', 'service_id');
        General::updateAltLanguages('privilege_descriptions', 'privilege');
        General::updateAltLanguages('privilege_section_descriptions', 'section_id');
        General::updateAltLanguages('state_descriptions', 'state_id');
        General::updateAltLanguages('country_descriptions', 'code');

        General::processBlocks();

        General::setEmptyProgressBar();
        if (db_get_field("SHOW TABLES LIKE '?:mailing_lists'")) {
            db_query("ALTER TABLE ?:mailing_lists DROP `show_on_sidebar`");
        }

        General::setEmptyProgressBar();
        $this->_processProfileFields();

        return true;
    }

    private function _processProfileFields()
    {
        $companies = General::getCompanies($this->store_data);
        General::connectToOriginalDB(array('table_prefix' => General::formatPrefix()));
        $profile_field_ids = db_get_fields('SELECT field_id FROM ?:profile_fields');

        foreach ($companies as $company) {
            $query = "INSERT INTO `?:ult_objects_sharing` (`share_company_id`, `share_object_id`, `share_object_type`) VALUES ";
            $data = array();
            foreach ($profile_field_ids as $pid) {
                $data[] = "($company[company_id], '$pid', 'profile_fields')";
            }
            $query .= implode(', ', $data);

            db_query($query);
        }

        return true;
    }
}
