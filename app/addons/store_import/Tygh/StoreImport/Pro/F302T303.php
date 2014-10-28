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

class F302T303
{
    protected $store_data = array();
    protected $main_sql_filename = 'pro_F302T303.sql';

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

        General::restoreSettings();

        db_query("
            DELETE FROM ?:settings_objects
            WHERE name IN (
                'product_notify_vendor',
                'order_notify_vendor',
                'page_notify_vendor',
                'company_discussion_type',
                'company_only_buyers',
                'company_posts_per_page',
                'company_post_approval',
                'company_post_ip_check',
                'company_notification_email',
                'company_notify_vendor',
                'companies_setting',
                'include_companies',
                'companies_change',
                'companies_priority',
                'product_share_discussion',
                'news_share_discussion',
                'page_share_discussion',
                'testimonials_from_all_stores'
            )
            AND section_id IN (
                SELECT section_id FROM ?:settings_sections
                WHERE name IN (
                    'discussion',
                    'google_sitemap'
                )
            )
        ");

        General::setEmptyProgressBar(General::getUnavailableLangVar('updating_languages'));
        General::updateAltLanguages('language_values', 'name');
        General::updateAltLanguages('settings_descriptions', array('object_id', 'object_type'));
        General::updateAltLanguages('state_descriptions', 'state_id');

        db_query('DROP TABLE IF EXISTS ?:se_queue');

        General::setEmptyProgressBar();
        General::setEmptyProgressBar();

        return true;
    }
}
