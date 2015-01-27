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

class F302T303
{
    protected $store_data = array();
    protected $main_sql_filename = 'mve_F302T303.sql';

    public function __construct($store_data)
    {
        $store_data['product_edition'] = 'MULTIVENDOR';
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

        General::setEmptyProgressBar();
        General::restoreSettings();

        db_query("
            DELETE FROM ?:settings_objects
            WHERE name IN (
                'product_share_discussion',
                'news_share_discussion',
                'page_share_discussion',
                'testimonials_from_all_stores'
            )
            AND section_id IN (
                SELECT section_id FROM ?:settings_sections
                WHERE name = 'discussion'
            )
        ");

        General::setEmptyProgressBar(General::getUnavailableLangVar('updating_languages'));
        General::updateAltLanguages('language_values', 'name');
        General::updateAltLanguages('settings_descriptions', array('object_id', 'object_type'));
        General::updateAltLanguages('shipping_service_descriptions', 'service_id');
        General::updateAltLanguages('state_descriptions', 'state_id');

        General::setEmptyProgressBar();

        return true;
    }
}
