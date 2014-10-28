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

use Tygh\Registry;
use Tygh\Mailer;
use Tygh\BlockManager\Block;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

//
// Get all news data
//
function fn_get_news($params, $items_per_page = 0, $lang_code = CART_LANGUAGE)
{
    $default_params = array (
        'page' => 1,
        'items_per_page' => $items_per_page
    );

    $params = array_merge($default_params, $params);

    $fields = array (
        '?:news.*',
        'descr.news',
        'descr.description'
    );

    // Define sort fields
    $sortings = array (
        'position' => '?:news.position',
        'name' => 'descr.news',
        'date' => '?:news.date',
    );

    $limit = $condition = '';

    $join = db_quote(" LEFT JOIN ?:news_descriptions AS descr ON descr.news_id = ?:news.news_id AND descr.lang_code = ?s", $lang_code);

    $condition .= (AREA == 'A') ? '1 ' : " ?:news.status = 'A'";

    $condition .= fn_get_localizations_condition('?:news.localization');

    // Get additional information about companies
    if (fn_allowed_for('ULTIMATE')) {
        $fields[] = ' ?:companies.company as company';
        $sortings['company'] = 'company';
        $join .= db_quote(" LEFT JOIN ?:companies ON ?:companies.company_id = ?:news.company_id");
    }

    if (isset($params['q']) && fn_string_not_empty($params['q'])) {

        $params['q'] = trim($params['q']);
        if ($params['match'] == 'any') {
            $pieces = fn_explode(' ', $params['q']);
            $search_type = ' OR ';
        } elseif ($params['match'] == 'all') {
            $pieces = fn_explode(' ', $params['q']);
            $search_type = ' AND ';
        } else {
            $pieces = array($params['q']);
            $search_type = '';
        }

        $_condition = array ();
        foreach ($pieces as $piece) {
            if (strlen($piece) == 0) {
                continue;
            }
            $tmp = array ();

            $tmp[] = db_quote("descr.news LIKE ?l", "%$piece%");
            $tmp[] = db_quote("descr.description LIKE ?l", "%$piece%");

            $_condition[] = '(' . join(' OR ', $tmp) . ')';
        }

        $_cond = implode($search_type, $_condition);

        if (!empty($_condition)) {
            $condition .= ' AND (' . $_cond . ') ';
        }
    }

    if (!empty($params['period']) && $params['period'] != 'A') {
        list($params['time_from'], $params['time_to']) = fn_create_periods($params);
        $condition .= db_quote(" AND (?:news.date >= ?i AND ?:news.date <= ?i)", $params['time_from'], $params['time_to']);
    }

    if (!empty($params['item_ids'])) {
        $condition .= db_quote(' AND ?:news.news_id IN (?n)', explode(',', $params['item_ids']));
    }

    $limit = '';
    if (!empty($params['limit'])) {
        $limit = db_quote(" LIMIT 0, ?i", $params['limit']);

    } elseif (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(?:news.news_id) FROM ?:news ?p WHERE ?p", $join, $condition);
        $limit = db_paginate($params['page'], $params['items_per_page']);
    }

    fn_set_hook('get_news', $params, $fields, $join, $condition, $sorting, $limit, $lang_code);

    $sorting = db_sort($params, $sortings, 'date', 'desc');

    // Used for Extended search
    if (!empty($params['get_conditions'])) {
        return array($fields, $join, $condition);
    }

    $fields = join(', ', $fields);
    $news = db_get_array("SELECT ?p FROM ?:news ?p WHERE ?p ?p ?p", $fields, $join, $condition, $sorting, $limit);

    /**
     * Get additional data for selected news
     *
     * @param array  $news      news list
     * @param string $lang_code language code
     */
    fn_set_hook('get_news_post', $news, $lang_code);

    return array($news, $params);
}

//
// Get specific news data
//
function fn_get_news_data($news_id, $lang_code = CART_LANGUAGE)
{
    $fields = array (
        '?:news.*',
        '?:news_descriptions.news',
        '?:news.date',
        '?:news_descriptions.description'
    );

    $join = '';
    $condition = (AREA == 'A') ? '' : " AND ?:news.status = 'A' ";

    fn_set_hook('get_news_data', $fields, $join, $condition, $lang_code);

    $news = db_get_row(
        "SELECT " . implode(', ', $fields) . " FROM ?:news "
        . "LEFT JOIN ?:news_descriptions ON ?:news_descriptions.news_id = ?:news.news_id "
        . "AND ?:news_descriptions.lang_code = ?s ?p "
        . "WHERE ?:news.news_id = ?i ?p",
        $lang_code, $join, $news_id, $condition
    );

    /**
     * Changes news data
     *
     * @param array  $news      News data
     * @param string $lang_code 2-letter language code
     */
    fn_set_hook('get_news_data_post', $news, $lang_code);

    return $news;
}

function fn_get_news_name($news_id, $lang_code = CART_LANGUAGE)
{
    if (!empty($news_id)) {
        return db_get_field("SELECT news FROM ?:news_descriptions WHERE news_id = ?i AND lang_code = ?s", $news_id, $lang_code);
    }

    return false;
}

function fn_get_newsletter_name($newsletter_id, $lang_code = CART_LANGUAGE)
{
    if (!empty($newsletter_id)) {
        return db_get_field("SELECT newsletter FROM ?:newsletter_descriptions WHERE newsletter_id = ?i AND lang_code = ?s", $newsletter_id, $lang_code);
    }

    return false;
}

//
// Get all newsletters data
//
function fn_get_newsletters($params = array(), $items_per_page = 0, $lang_code = CART_LANGUAGE)
{
    $default_params = array(
        'type' => NEWSLETTER_TYPE_NEWSLETTER,
        'only_available' => true, // hide hidden and not available newsletters. We use 'false' for admin page
        'page' => 1,
        'items_per_page' => $items_per_page
    );

    $params = array_merge($default_params, $params);

    $_conditions = array();

    if ($params['only_available']) {
        $_conditions[] = "?:newsletters.status = 'A'";
    }

    if ($params['type']) {
        $_conditions[] = db_quote("?:newsletters.type = ?s", $params['type']);
    }

    if (!empty($_conditions)) {
        $_conditions = implode(' AND ', $_conditions);
    } else {
        $_conditions = '1';
    }

    $limit = '';
    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(*) FROM ?:newsletters WHERE ?p", $_conditions);
        $limit = db_paginate($params['page'], $params['items_per_page']);
    }

    $newsletters = db_get_array(
        "SELECT ?:newsletters.newsletter_id, ?:newsletters.status, ?:newsletters.sent_date, "
        . "?:newsletters.status, ?:newsletters.mailing_lists, ?:newsletter_descriptions.newsletter FROM ?:newsletters "
        . "LEFT JOIN ?:newsletter_descriptions ON ?:newsletter_descriptions.newsletter_id=?:newsletters.newsletter_id "
        . "AND ?:newsletter_descriptions.lang_code= ?s "
        . "WHERE ?p ORDER BY ?:newsletters.sent_date DESC, ?:newsletters.status $limit",
        $lang_code, $_conditions
    );

    foreach ($newsletters as $id => $data) {
        $newsletters[$id]['mailing_lists'] = !empty($data['mailing_lists']) ? fn_explode(',', $data['mailing_lists']) : array();
    }

    return array($newsletters, $params);
}

//
// Get specific newsletter data
//
function fn_get_newsletter_data($newsletter_id, $lang_code = CART_LANGUAGE)
{
    $status_condition = (AREA == 'A') ? '' : " AND ?:newsletters.status='A' ";

    $newsletter = db_get_row("SELECT * FROM ?:newsletters LEFT JOIN ?:newsletter_descriptions ON ?:newsletter_descriptions.newsletter_id = ?:newsletters.newsletter_id AND ?:newsletter_descriptions.lang_code = ?s WHERE ?:newsletters.newsletter_id = ?i $status_condition", $lang_code, $newsletter_id);

    if (!empty($newsletter)) {
        $newsletter['mailing_lists'] = explode(',', $newsletter['mailing_lists']);
    }

    return $newsletter;
}

//
// Get mailing list data
//
function fn_get_mailing_list_data($list_id, $lang_code = CART_LANGUAGE)
{
    $status_condition = (AREA == 'A') ? '' : " AND m.status = 'A' ";

    return db_get_row("SELECT * FROM ?:mailing_lists AS m LEFT JOIN ?:common_descriptions AS d ON m.list_id = d.object_id AND d.lang_code = ?s AND d.object_holder = 'mailing_lists' WHERE m.list_id = ?i $status_condition", $lang_code, $list_id);
}

function fn_news_and_emails_get_discussion_object_data(&$data, &$object_id, &$object_type)
{
    if ($object_type == 'N') { // news
        $data['description'] = db_get_field("SELECT news FROM ?:news_descriptions WHERE news_id = ?i AND lang_code = ?s", $object_id, CART_LANGUAGE);
        if (AREA == 'A') {
            $data['url'] = "news.update?news_id=$object_id&selected_section=discussion";
        } else {
            $data['url'] = "news?news_id=$object_id";
        }
    }
}

function fn_news_and_emails_get_discussion_objects(&$objects)
{
    $objects['N'] = 'news';
}

function fn_news_and_emails_is_accessible_discussion(&$data, &$auth, &$access)
{
    if ($data['object_type'] == 'N') {// news
        $access = fn_get_news_data($data['object_id']);
    }
}

function fn_news_and_emails_localization_objects(&$_tables)
{
    $_tables[] = 'news';
}

function fn_news_and_emails_save_log(&$type, &$action, &$data, &$user_id, &$content, &$event_type, &$object_primary_keys)
{
    $object_primary_keys['news'] = 'news_id';

    if ($type == 'news') {
        $news = db_get_field("SELECT news FROM ?:news_descriptions WHERE news_id = ?i AND lang_code = ?s", $data['news_id'], Registry::get('settings.Appearance.backend_default_language'));
        $content = array (
            'news' => $news . ' (#' . $data['news_id'] . ')',
            'id' => $data['news_id'],
        );
    }
}

//
// News condition function
//
function fn_news_and_email_create_news_condition($params, $lang_code = CART_LANGUAGE)
{
    $params['get_conditions'] = true;

    list($fields, $join, $condition) = fn_get_news($params, 0, $lang_code);

    if (AREA == 'C' && fn_allowed_for('ULTIMATE') && Registry::get('runtime.company_id')) {
        $condition .= db_quote(' AND ?:news.company_id = ?i', Registry::get('runtime.company_id'));
    }

    $data = array (
        'fields' => $fields,
        'join' => $join,
        'condition' => $condition,
        'table' => '?:news',
        'key' => 'news_id',
        'sort' => 'descr.news',
        'sort_table' => 'news_descriptions'
    );

    return $data;
}

function fn_update_news($news_id, $news_data, $lang_code = CART_LANGUAGE)
{
    // news title required
    if (empty($news_data['news'])) {
        return false;
    }

    if (!empty($news_id) && !fn_check_company_id('news', 'news_id', $news_id)) {
        fn_company_access_denied_notification();

        return false;
    }

    $_data = $news_data;
    $_data['date'] = fn_parse_date($news_data['date']);
    if (isset($_data['localization'])) {
        $_data['localization'] = empty($_data['localization']) ? '' : fn_implode_localizations($_data['localization']);
    }

    if (empty($news_id)) {
        $create = true;

        $news_id = $_data['news_id'] = db_query("REPLACE INTO ?:news ?e", $_data);

        if (empty($news_id)) {
            return false;
        }

        // Adding descriptions
        foreach (fn_get_translation_languages() as $_data['lang_code'] => $v) {
            db_query("INSERT INTO ?:news_descriptions ?e", $_data);
        }

    } else {
        $create = false;

        db_query("UPDATE ?:news SET ?u WHERE news_id = ?i", $_data, $news_id);

        // update news descriptions
        $_data = $news_data;
        db_query("UPDATE ?:news_descriptions SET ?u WHERE news_id = ?i AND lang_code = ?s", $_data, $news_id, $lang_code);
    }

    // Log news update/add
    fn_log_event('news', !empty($create) ? 'create' : 'update', array(
        'news_id' => $news_id,
    ));

    fn_set_hook('update_news', $news_data, $news_id, $lang_code, $create);

    return $news_id;
}

// if called first time - registers all links in db
// returns newsletter bodies with rewritten links
function fn_rewrite_links($body_html, $newsletter_id, $campaign_id)
{
    $regex = "/href=('|\")((?:http|ftp|https):\/\/[\w-\.]+[?]?[-\w:\+?\/?\.\=%&;~\[\]]+)/ie";
    $url = fn_url('newsletters.track', 'C', 'http');
    $replace_regex = '"href=\\1$url&link=" . fn_register_link("\\2", $newsletter_id, $campaign_id) . "-" . $newsletter_id . "-" . $campaign_id';
    $matches = array();
    $body_html = preg_replace($regex, $replace_regex, $body_html);

    return $body_html;
}

function fn_register_link($url, $newsletter_id, $campaign_id)
{
    $url = str_replace('&amp;', '&', rtrim($url, '/'));
    $_where = array(
        'newsletter_id' => $newsletter_id,
        'campaign_id' => $campaign_id,
        'url' => $url
    );
    $link = db_get_row("SELECT link_id FROM ?:newsletter_links WHERE ?w", $_where);
    if (empty($link)) {
        $_data = array();
        $_data['url'] = $url;
        $_data['campaign_id'] = $campaign_id;
        $_data['newsletter_id'] = $newsletter_id;
        $_data['clicks'] = 0;

        return db_query("INSERT INTO ?:newsletter_links ?e", $_data);
    } else {
        return $link['link_id'];
    }
}

function fn_send_newsletter($to, $from, $subj, $body, $attachments = array(), $lang_code = CART_LANGUAGE, $reply_to = '')
{
    $reply_to = !empty($reply_to) ? $reply_to : 'default_company_newsletter_email';
    $_from = array(
        'email' => !empty($from['from_email']) ? $from['from_email'] : 'default_company_newsletter_email',
        'name' => !empty($from['from_name']) ? $from['from_name'] : (empty($from['from_email']) ? 'default_company_name' : '')
    );

    return Mailer::sendMail(array(
        'to' => $to,
        'from' => $_from,
        'reply_to' => $reply_to,
        'data' => array(
            'body' => $body,
            'subject' => $subj
        ),
        'attachments' => $attachments,
        'mailer_settings' => Registry::get('addons.news_and_emails'),
        'tpl' => 'addons/news_and_emails/newsletter.tpl',
    ), 'C', $lang_code);
}

/**
* generate unsubscribe link. if list_id=0 and subscriber_id=0 - generate stub key for test email
*
* @param int $list_id - mailing list id
* @param int $subscriber_id
* @return string unsubscribe_link
*/
function fn_generate_unsubscribe_link($list_id, $subscriber_id)
{
    if ($list_id && $subscriber_id) {
        $unsubscribe_key = db_get_field("SELECT unsubscribe_key FROM ?:user_mailing_lists WHERE subscriber_id = ?i AND list_id = ?i", $subscriber_id, $list_id);
    } else {
        $unsubscribe_key = '0';
    }

    return fn_url("newsletters.unsubscribe?list_id=$list_id&s_id=$subscriber_id&key=$unsubscribe_key", 'C', 'http');
}

/**
* generate activation link. if list_id=0 and subscriber_id=0 - generate stub key for test email
*
* @param int $list_id - mailing list id
* @param int $subscriber_id
* @return string unsubscribe_link
*/
function fn_generate_activation_link($list_id, $subscriber_id)
{
    if ($list_id && $subscriber_id) {
        $activation_key = db_get_field("SELECT activation_key FROM ?:user_mailing_lists WHERE list_id=?i AND subscriber_id=?i", $list_id, $subscriber_id);
    } else {
        $activation_key = '0';
    }

    return fn_url("newsletters.activate?list_id=$list_id&key=$activation_key&s_id=$subscriber_id", 'C', 'http');
}

/**
* get list of mailing lists
*
* @param array $params - search parameters
* @param array $items_per_page
* @param string $lang_code - language code
* @return array
*/
function fn_get_mailing_lists($params = array(), $items_per_page = 0, $lang_code = CART_LANGUAGE)
{
    $default_params = array(
        'checkout' => false,
        'registration' => false,
        'sidebar' => false,
        'only_available' => true, // hide hidden and not available newsletters. We use 'false' for admin page
        'items_per_page' => $items_per_page
    );

    $params = array_merge($default_params, $params);

    $condition = '1';
    if ($params['checkout']) {
        $condition .= db_quote(" AND ?:mailing_lists.show_on_checkout = ?i", 1);
    }

    if ($params['registration']) {
        $condition .= db_quote(" AND ?:mailing_lists.show_on_registration = ?i", 1);
    }

    if ($params['only_available']) {
        $condition .= db_quote(" AND ?:mailing_lists.status = ?s", 'A');
    }

    $limit = '';
    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_hash_array("SELECT COUNT(*) FROM ?:mailing_lists WHERE ?p", 'list_id', $condition);
        $limit = db_paginate($params['page'], $params['items_per_page']);
    }

    $mailing_lists = db_get_hash_array("SELECT * FROM ?:mailing_lists LEFT JOIN ?:common_descriptions ON ?:common_descriptions.object_id = ?:mailing_lists.list_id AND ?:common_descriptions.object_holder = 'mailing_lists' AND ?:common_descriptions.lang_code = ?s WHERE ?p $limit", 'list_id', $lang_code, $condition);

    return array($mailing_lists, $params);
}

/**
* Save user mailing lists settings.
*
* @param int $subscriber_id
* @param array $user_list_ids
* @param mixed $confirmed - if passed, subscription status set to passed value, if null, depends on autoresponder
* @param boolean $notify
* @param string $lang_code
*/
function fn_update_subscriptions($subscriber_id, $user_list_ids = array(), $confirmed = NULL, $force_notification = array(), $lang_code = CART_LANGUAGE)
{
    if (!empty($user_list_ids)) {
        list($lists) = fn_get_mailing_lists();
        $subscriber = db_get_row("SELECT * FROM ?:subscribers WHERE subscriber_id = ?i", $subscriber_id);

        // to prevent user from subscribing to hidden and disabled mailing lists by manual link edit
        if (AREA != 'A') {
            foreach ($user_list_ids as $k => $l_id) {
                if ($lists[$l_id]['status'] != 'A') {
                    unset($user_list_ids[$k]);
                }
            }
        }

        foreach ($user_list_ids as $list_id) {
            $subscribed = db_get_array("SELECT confirmed FROM ?:user_mailing_lists WHERE subscriber_id = ?i AND list_id = ?i", $subscriber_id, $list_id);

            $already_confirmed = !empty($subscribed['confirmed']) ? true : false;
            $already_subscribed = !empty($subscribed) ? true : false;

            if ($already_confirmed) {
                $_confirmed = 1;
            } else {
                if (is_array($confirmed)) {
                    $_confirmed = !empty($confirmed[$list_id]['confirmed']) ? $confirmed[$list_id]['confirmed'] : 0;
                } else {
                    $_confirmed = !empty($lists[$list_id]['register_autoresponder']) ? 0 : 1;
                }
            }

            if ($already_subscribed && $already_confirmed == $_confirmed) {
                continue;
            }

            $_data = array(
                'subscriber_id' => $subscriber_id,
                'list_id' => $list_id,
                'activation_key' => md5(uniqid(rand())),
                'unsubscribe_key' => md5(uniqid(rand())),
                'email' => $subscriber['email'],
                'timestamp' => TIME,
                'lang_code' => $lang_code,
                'confirmed' => $_confirmed,
            );
            db_query("REPLACE INTO ?:user_mailing_lists ?e", $_data);

            // send confirmation email for each mailing list
            if (empty($_confirmed)) {
                fn_send_confirmation_email($subscriber_id, $list_id, $subscriber['email'], $lang_code);
            }
        }
    }

    // Delete unchecked mailing lists
    if (!empty($user_list_ids)) {
        $lists_to_delete = db_get_field("SELECT list_id FROM ?:user_mailing_lists WHERE subscriber_id = ?i AND list_id NOT IN (?n)", $subscriber_id, $user_list_ids);
        if (!empty($lists_to_delete)) {
            db_query("DELETE FROM ?:user_mailing_lists WHERE subscriber_id = ?i AND list_id IN (?n)", $subscriber_id, $lists_to_delete);

            // Delete subscriber in the frontend if all lists are unchecked
            if (AREA == 'C') {
                $c = db_get_field("SELECT COUNT(*) FROM ?:user_mailing_lists WHERE subscriber_id = ?i", $subscriber_id);

                if (empty($c)) {
                    db_query("DELETE FROM ?:subscribers WHERE subscriber_id = ?i", $subscriber_id);
                }
            }
        }

    // Delete subscriber in the frontend area if all lists are unchecked
    } else {
        fn_delete_subscribers(array($subscriber_id), (AREA == 'C'));
    }
}

/**
 * POST hook when deleting company
 *
 * @param int $company_id Company identifier
 */
function fn_news_and_emails_delete_company($company_id)
{
    $news_ids = db_get_fields('SELECT news_id FROM ?:news WHERE company_id = ?i', $company_id);

    foreach ($news_ids as $news_id) {
        fn_delete_news($news_id);
    }
}

function fn_delete_subscribers($subscriber_ids, $delete_user = true)
{
    // Only Root can peform this action or owner
    if (!empty($subscriber_ids) && (!Registry::get('runtime.company_id') || AREA == 'C') ) {
        if ($delete_user == true) {
            db_query("DELETE FROM ?:subscribers WHERE subscriber_id IN (?n)", $subscriber_ids);
        }
        db_query("DELETE FROM ?:user_mailing_lists WHERE subscriber_id IN (?n)", $subscriber_ids);
    }
}

function fn_send_confirmation_email($subscriber_id, $list_id, $email, $lang_code = CART_LANGUAGE)
{
    $list = fn_get_mailing_list_data($list_id);
    if ($list['register_autoresponder']) {
        $autoresponder = fn_get_newsletter_data($list['register_autoresponder']);

        $body = $autoresponder['body_html'];

        $body = fn_render_newsletter($body, array('list_id' => $list_id, 'subscriber_id' => $subscriber_id, 'email' => $email));

        if (AREA == 'A') {
             fn_echo(__('sending_email_to', array(
                '[email]' => $email
            )) . '<br />');
        }

        fn_send_newsletter($email, $list, $autoresponder['newsletter'], $body, array(), $lang_code, $list['reply_to']);
    }
}

function fn_render_newsletter($body, $subscriber)
{
    // prepare placeholder values
    if (!empty($subscriber['list_id']) && !empty($subscriber['subscriber_id'])) {
        $values['%UNSUBSCRIBE_LINK'] = fn_generate_unsubscribe_link($subscriber['list_id'], $subscriber['subscriber_id']);
        $values['%ACTIVATION_LINK'] = fn_generate_activation_link($subscriber['list_id'], $subscriber['subscriber_id']);
    } else {
        $values['%UNSUBSCRIBE_LINK'] = $values['%ACTIVATION_LINK'] = empty($subscriber['user_id']) ? ('[' . __('link_message_for_test_letter') . ']') : '';
    }
    $values['%SUBSCRIBER_EMAIL'] = $subscriber['email'];
    $values['%COMPANY_NAME'] = Registry::get('settings.Company.company_name');
    $values['%COMPANY_ADDRESS'] = Registry::get('settings.Company.company_address');
    $values['%COMPANY_PHONE'] = Registry::get('settings.Company.company_phone');

    return strtr($body, $values);

}

function fn_news_and_emails_get_block_locations(&$locations)
{
    $locations['news'] = 'news_id';

    return true;
}

//
// Generate navigation
//
function fn_newsletters_generate_sections($section)
{
    Registry::set('navigation.dynamic.sections', array (
        'N' => array (
            'title' => __('newsletters'),
            'href' => 'newsletters.manage?type=' . NEWSLETTER_TYPE_NEWSLETTER,
        ),
        'T' => array (
            'title' => __('templates'),
            'href' => 'newsletters.manage?type=' . NEWSLETTER_TYPE_TEMPLATE,
        ),
        'A' => array (
            'title' => __('autoresponders'),
            'href' => 'newsletters.manage?type=' . NEWSLETTER_TYPE_AUTORESPONDER,
        ),
        'C' => array (
            'title' => __('campaigns'),
            'href' => 'newsletters.campaigns',
        ),
        'mailing_lists' => array (
            'title' => __('mailing_lists'),
            'href' => 'mailing_lists.manage',
        ),
        'subscribers' => array (
            'title' => __('subscribers'),
            'href' => 'subscribers.manage',
        ),
    ));
    Registry::set('navigation.dynamic.active_section', $section);

    return true;
}

function fn_news_and_emails_sitemap_link_object(&$link, &$object, &$value)
{
    if ($object == 'news') {
        $link = 'news.view?news_id=' . $value;
    }
}

function fn_news_and_emails_customer_search_objects(&$schema, &$objects)
{
    if (AREA == 'A') {
        $objects['news'] = 'Y';
    }
}

function fn_news_and_emails_import_after_process_data(&$primary_object_id, &$object, &$pattern, &$options, &$processed_data, &$processing_groups, &$skip_db_processing_record)
{
    if (empty($object['list_id'])) {
        fn_set_notification('W', __('warning'), __('warning_subscribers_import'));
        $skip_db_processing_record = true;
    }
}

function fn_news_and_emails_generate_rss_feed(&$items_data, &$additional_data, &$block_data, &$lang_code)
{
    if (!empty($block_data['content']['filling']) && $block_data['content']['filling'] == 'news') {
        $params = array (
            'sort_by' => 'timestamp',
            'period' => 'A',
            'limit' => !empty($block_data['properties']['max_item']) ? $block_data['properties']['max_item'] : 3,
        );

        list($news, ) = fn_get_news($params, 0, $lang_code);

        $additional_data['title'] = !empty($block_data['properties']['feed_title']) ? $block_data['properties']['feed_title'] : __('news') . '::' . __('page_title', '', $lang_code);
        $additional_data['description'] = !empty($block_data['properties']['feed_description']) ? $block_data['properties']['feed_description'] : $additional_data['title'];
        $additional_data['link'] = fn_url('news.list', 'C', 'http', $lang_code);
        $additional_data['language'] = $lang_code;
        $additional_data['lastBuildDate'] = $news[0]['date']; //we can use first element because news sorting by data

        foreach ($news as $key => $data) {
            $items_data[$key] = array (
                'title' => $data['news'],
                'link' => fn_url('news.view?news_id=' . $data['news_id'], 'C', 'http', $lang_code, true),
                'pubDate' => fn_format_rss_time($data['date']),
                'description' => $data['description'],
            );
        }
    }
}

function fn_news_and_emails_get_predefined_statuses(&$type, &$statuses)
{
    if ($type == 'news') {
        $statuses['news'] = array(
            'A' => __('active'),
            'D' => __('disabled'),
            'S' => __('sent')
        );
    }
}

function fn_get_shared_companies($mailing_lists)
{
    if (!empty($mailing_lists)) {
        foreach ($mailing_lists as $list_id => $list_data) {
            $shared_for_companies = db_get_fields("SELECT share_company_id FROM ?:ult_objects_sharing WHERE share_object_type = 'mailing_lists' AND share_object_id = ?i", $list_id);
            $mailing_lists[$list_id]['shared_for_companies'] = array(0 => __('ult_shared_with'));
            if (!empty($shared_for_companies)) {
                foreach ($shared_for_companies as $company_id) {
                    $mailing_lists[$list_id]['shared_for_companies'][] = fn_get_company_name($company_id);
                }
            }
        }
    }

    return $mailing_lists;
}

if (fn_allowed_for('ULTIMATE')) {
    function fn_news_and_emails_ult_check_store_permission($params, &$object_type, &$object_name, &$table, &$key, &$key_id)
    {
        if (Registry::get('runtime.controller') == 'news' && !empty($params['news_id'])) {
            $key = 'news_id';
            $key_id = $params[$key];
            $table = 'news';
            $object_name = fn_get_news_name($key_id, DESCR_SL);
            $object_type = __('news');

        }
    }
}

/**
* Deletes news by its ID
*
* @param int $news_id - News Identifier
*/
function fn_delete_news($news_id)
{
    $news_deleted = false;

    if (!empty($news_id)) {
        if (fn_check_company_id('news', 'news_id', $news_id)) {

            // Log news deletion
            fn_log_event('news', 'delete', array(
                'news_id' => $news_id,
            ));

            Block::instance()->removeDynamicObjectData('news', $news_id);

            $affected_rows = db_query("DELETE FROM ?:news WHERE news_id = ?i", $news_id);

            db_query("DELETE FROM ?:news_descriptions WHERE news_id = ?i", $news_id);

            if ($affected_rows != 0) {
                $news_deleted = true;
            } else {
                fn_set_notification('E', __('error'), __('object_not_found', array('[object]' => __('news'))),'','404');
            }

            fn_set_hook('delete_news', $news_id);

        } else {

            fn_company_access_denied_notification();
        }
    }

    return $news_deleted;
}

/* 
 *  SEO functionality, implement its own hooks
 */
function fn_seo_get_news_data(&$fields, &$join, &$condition, &$lang_code)
{
    $fields[] = '?:seo_names.name as seo_name';
    $fields[] = '?:seo_names.path as seo_path';
    $join .= db_quote(
        " LEFT JOIN ?:seo_names ON ?:seo_names.object_id = ?:news.news_id ?p",
        fn_get_seo_join_condition('n', '?:news.company_id', $lang_code)
    );
}

function fn_seo_get_news_data_post(&$news, &$lang_code)
{
    if (empty($news['seo_name']) && !empty($news['news_id'])) {
        $news['seo_name'] = fn_seo_get_name('n', $news['news_id'], '', null, $lang_code);
    }

    return true;
}

function fn_seo_get_news(&$params, &$fields, &$join, &$condition, &$sorting, &$limit, &$lang_code)
{
    if (isset($params['compact']) && $params['compact'] == 'Y') {
        $condition .= db_quote(' OR ?:seo_names.name LIKE ?s', '%' . preg_replace('/-[a-zA-Z]{1,3}$/i', '', str_ireplace(SEO_FILENAME_EXTENSION, '', $params['q'])) . '%');
    }

    $fields[] = '?:seo_names.name as seo_name';
    $fields[] = '?:seo_names.path as seo_path';

    $join .= db_quote(
        " LEFT JOIN ?:seo_names ON ?:seo_names.object_id = ?:news.news_id ?p",
        fn_get_seo_join_condition('n', '?:news.company_id', $lang_code)
    );
}

function fn_seo_get_news_post(&$news, &$lang_code)
{
    if (AREA == 'C') {
        foreach ($news as $k => $n) {
            fn_seo_cache_name('n', $n['news_id'], $n, isset($n['company_id']) ? $n['company_id'] : '', $lang_code);
        }
    }

    return true;
}

function fn_seo_update_news(&$news_data, &$news_id, &$lang_code)
{
    if (!empty($news_data['news']) && !empty($news_id)) {
        if (Registry::get('runtime.company_id')) {
            $news_data['company_id'] = Registry::get('runtime.company_id');
        }

        fn_seo_update_object($news_data, $news_id, 'n', $lang_code);
    }
}

function fn_seo_delete_news(&$news_id)
{
    return fn_delete_seo_name($news_id, 'n');
}
