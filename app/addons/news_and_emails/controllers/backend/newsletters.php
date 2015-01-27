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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

// dynamic pieces of content that admin can use in newsletters
$placeholders = array(
    NEWSLETTER_TYPE_NEWSLETTER => array(
        '%UNSUBSCRIBE_LINK' => 'unsubscribe_link',
        '%SUBSCRIBER_EMAIL' => 'subscriber_email',
        '%COMPANY_NAME' => 'company_name',
        '%COMPANY_ADDRESS' => 'company_address',
        '%COMPANY_PHONE' => 'company_phone'
     ),

     NEWSLETTER_TYPE_AUTORESPONDER => array(
         '%ACTIVATION_LINK' => 'activation_link',
        '%SUBSCRIBER_EMAIL' => 'subscriber_email',
        '%COMPANY_NAME' => 'company_name',
        '%COMPANY_ADDRESS' => 'company_address',
        '%COMPANY_PHONE' => 'company_phone'
     ),

     NEWSLETTER_TYPE_TEMPLATE => array(
         '%UNSUBSCRIBE_LINK' => 'unsubscribe_link',
         '%ACTIVATION_LINK' => 'activation_link',
        '%SUBSCRIBER_EMAIL' => 'subscriber_email',
        '%COMPANY_NAME' => 'company_name',
        '%COMPANY_ADDRESS' => 'company_address',
        '%COMPANY_PHONE' => 'company_phone'
     ),
 );

if ($_SERVER['REQUEST_METHOD']	== 'POST') {

    fn_trusted_vars('newsletter_data');

    $suffix = '.manage';
    //
    // Delete newsletters
    //
    if ($mode == 'm_delete') {
        if (!empty($_REQUEST['newsletter_ids'])) {
            foreach ($_REQUEST['newsletter_ids'] as $v) {
                fn_delete_newsletter($v);
            }
        }
    }

    //
    // Update newsletters
    //
    if ($mode == 'update') {
        $newsletter_id = fn_update_newsletter($_REQUEST['newsletter_data'], $_REQUEST['newsletter_id'], DESCR_SL);

        return array(CONTROLLER_STATUS_OK, "newsletters.update?newsletter_id=" . $newsletter_id);
    }

    //
    // Send newsletter
    //
    if ($mode == 'send') {
        $newsletter_id = fn_update_newsletter($_REQUEST['newsletter_data'], $_REQUEST['newsletter_id'], DESCR_SL);

        if (!empty($_REQUEST['newsletter_data']['mailing_lists']) || !empty($_REQUEST['newsletter_data']['users']) || !empty($_REQUEST['newsletter_data']['abandoned_days'])) {
            $list_recipients = array();
            if (!empty($_REQUEST['newsletter_data']['mailing_lists'])) {
                $list_recipients = db_get_array("SELECT * FROM ?:subscribers AS s LEFT JOIN ?:user_mailing_lists AS u ON s.subscriber_id=u.subscriber_id LEFT JOIN ?:mailing_lists AS m ON u.list_id = m.list_id WHERE u.list_id IN(?n) AND u.confirmed='1' GROUP BY s.subscriber_id", $_REQUEST['newsletter_data']['mailing_lists']);
            }

            $user_recipients = array();
            if (!empty($_REQUEST['newsletter_data']['users'])) {
                $users = fn_explode(',', $_REQUEST['newsletter_data']['users']);
                $user_recipients = db_get_array("SELECT user_id, email, lang_code FROM ?:users WHERE user_id IN (?n)", $users);
                foreach ($user_recipients as $k => $v) {
                    // populate user array with sensible defaults
                    $user_recipients[$k]['from_name'] = '';
                    $user_recipients[$k]['reply_to'] = '';
                    $user_recipients[$k]['users_list'] = 'Y';
                }
            }

            $abandoned_recipients = array();
            if (!empty($_REQUEST['newsletter_data']['abandoned_days'])) {
                $time = time() - (intval($_REQUEST['newsletter_data']['abandoned_days']) * 24 * 60 * 60); // X days * 24 hours * 60 mins * 60 secs;
                $condition = db_quote("AND ?:user_session_products.timestamp <= ?i", $time);
                if ($_REQUEST['newsletter_data']['abandoned_type'] == 'cart') {
                    $condition .= db_quote(' AND ?:user_session_products.type = ?s', 'C');
                } elseif ($_REQUEST['newsletter_data']['abandoned_type'] == 'wishlist') {
                    $condition .= db_quote(' AND ?:user_session_products.type = ?s', 'W');
                }

                if (fn_allowed_for("ULTIMATE")) {
                    if (!empty($_REQUEST['newsletter_data']['abandoned_company_id'])) {
                        $condition .= db_quote(" AND ?:user_session_products.company_id = ?i", $_REQUEST['newsletter_data']['abandoned_company_id']);
                    }
                }

                $abandoned_recipients = db_get_array("SELECT ?:users.user_id, ?:users.email, ?:users.lang_code FROM ?:users LEFT JOIN ?:user_session_products ON (?:users.user_id = ?:user_session_products.user_id) WHERE 1 $condition  GROUP BY ?:users.user_id");
                if (!empty($abandoned_recipients)) {
                    foreach ($abandoned_recipients as $k => $v) {
                        // populate user array with sensible defaults
                        $abandoned_recipients[$k]['from_name'] = '';
                        $abandoned_recipients[$k]['reply_to'] = '';
                        $abandoned_recipients[$k]['users_list'] = 'Y';
                    }
                }
            }

            $recipients = array_merge($list_recipients, $user_recipients, $abandoned_recipients);

            if (!empty($recipients)) {
                // Set status to 'sent'
                $send_ids = isset($_REQUEST['send_ids']) ? $_REQUEST['send_ids'] : array($newsletter_id);
                foreach ($send_ids as $n_id) {
                    db_query("UPDATE ?:newsletters SET status = 'S', sent_date = ?i WHERE newsletter_id = ?i", TIME, $n_id);
                }

                $data = array(
                    'send_ids' => $send_ids,
                    'recipients' => $recipients,
                );

                $key = md5(uniqid(rand()));

                if (fn_set_storage_data('newsletters_batch_' . $key, serialize($data))) {
                    return array(CONTROLLER_STATUS_OK, "newsletters.batch_send?key=$key");
                }
            } else {
                fn_set_notification('W', __('warning'), __('warning_newsletter_no_recipients'));
            }
        } else {
            fn_set_notification('W', __('warning'), __('warning_newsletter_no_recipients'));
        }

        return array(CONTROLLER_STATUS_OK, "newsletters.update?newsletter_id=$newsletter_id");

    }

    // send newsletter to test email
    if ($mode == 'test_send') {

        $test_email = $_REQUEST['test_email'];
        if (fn_validate_email($test_email)) {

            $user['list_id'] = 0;
            $user['subscriber_id'] = 0;
            $user['email'] = $test_email;
            $newsletter = $_REQUEST['newsletter_data'];

            if (isset($newsletter['campaign_id'])) {
                $newsletter['body_html'] = fn_rewrite_links($newsletter['body_html'], $_REQUEST['newsletter_id'], $newsletter['campaign_id']);
            }
            $first_newsletter = fn_render_newsletter($newsletter['body_html'], $user);

            if (!empty($first_newsletter)) {
                $result = fn_send_newsletter($test_email, array(), $newsletter['newsletter'], $first_newsletter, array(), DESCR_SL, '', true);
            }

            if ((!empty($first_newsletter) && $result)) {
                fn_set_notification('N', __('notice'), __('text_newsletter_sent'));
            }
        } else {
            if (empty($test_email)) {
                fn_set_notification('W', __('warning'), __('email_cannot_be_empty'));
            } else {
                fn_set_notification('W', __('warning'), __('error_invalid_emails', array(
                    '[emails]' => $test_email
                )));
            }
        }

        if (defined('AJAX_REQUEST')) {
            exit;
        }

        return array(CONTROLLER_STATUS_OK, "newsletters.update?newsletter_id=$_REQUEST[newsletter_id]");
    }

    // preview html version of newsletter
    if ($mode == 'preview_html') {
        $user['list_id'] = 0;
        $user['subscriber_id'] = 0;
        $user['email'] = 'sample@sample.com';
        $body = fn_render_newsletter($_REQUEST['newsletter_data']['body_html'], $user);
        Registry::get('view')->assign('body', $body);
        Registry::get('view')->display('addons/news_and_emails/views/newsletters/components/preview_popup.tpl');
        exit();
    }

    if ($mode == 'm_update_campaigns') {

        if (!empty($_REQUEST['campaigns'])) {
            $c_ids = array();
            foreach ($_REQUEST['campaigns'] as $k => $data) {
                db_query("UPDATE ?:newsletter_campaigns SET ?u WHERE campaign_id = ?i", $data, $k);

                $data['object'] = $data['name'];
                $_where = array(
                    'object_id' => $k,
                    'object_holder' => 'newsletter_campaigns',
                    'lang_code' => DESCR_SL
                );

                db_query("UPDATE ?:common_descriptions SET ?u WHERE ?w", $data, $_where);
            }
        }

        $suffix = '.campaigns';
    }

    if ($mode == 'add_campaign') {
        $data = $_REQUEST['campaign_data'];
        if (!empty($data['name'])) {
            $data['campaign_id'] = $data['object_id'] = db_query("INSERT INTO ?:newsletter_campaigns ?e", $data);
            $data['object'] = $data['name'];
            $data['object_holder'] = 'newsletter_campaigns';

            foreach (fn_get_translation_languages() as $data['lang_code'] => $_v) {
                db_query("REPLACE INTO ?:common_descriptions ?e", $data);
            }
        }

        $suffix = '.campaigns';
    }

    if ($mode == 'm_delete_campaigns') {
        if (!empty($_REQUEST['campaign_ids'])) {
            fn_delete_campaigns($_REQUEST['campaign_ids']);
        }

        $suffix = '.campaigns';
    }

    return array(CONTROLLER_STATUS_OK, "newsletters" . $suffix);
}

if ($mode == 'batch_send' && !empty($_REQUEST['key'])) {
    $data = fn_get_storage_data('newsletters_batch_' . $_REQUEST['key']);

    if (!empty($data)) {
        $data = @unserialize($data);
    }

    if (is_array($data)) {
        // Ger newsletter data
        $newsletter_data = array();
        foreach ($data['send_ids'] as $newsletter_id) {
            $n = array();
            foreach (fn_get_translation_languages() as $lang_code => $v) {
                 $n[$lang_code] = fn_get_newsletter_data($newsletter_id, $lang_code);
                 $n[$lang_code]['body_html'] = fn_rewrite_links($n[$lang_code]['body_html'], $newsletter_id, $n[$lang_code]['campaign_id']);
            }

            $newsletter_data[] = $n;
        }

        foreach (array_splice($data['recipients'], 0, Registry::get('addons.news_and_emails.newsletters_per_pass')) as $subscriber) {
            foreach ($newsletter_data as $newsletter) {
                $body = fn_render_newsletter($newsletter[$subscriber['lang_code']]['body_html'], $subscriber);

                fn_echo(__('sending_email_to', array(
                    '[email]' => $subscriber['email']
                )) . '<br />');

                if (!empty($newsletter[$subscriber['lang_code']]['newsletter_multiple'])) {
                    $subjects = explode("\n", $newsletter[$subscriber['lang_code']]['newsletter_multiple']);
                    $newsletter[$subscriber['lang_code']]['newsletter'] = trim($subjects[rand(0, count($subjects) - 1)]);
                }
                fn_send_newsletter($subscriber['email'], $subscriber, $newsletter[$subscriber['lang_code']]['newsletter'], $body, array(), $subscriber['lang_code'], $subscriber['reply_to']);
            }
        }

        if (!empty($data['recipients'])) {

            fn_set_storage_data('newsletters_batch_' . $_REQUEST['key'], serialize($data));

            return array(CONTROLLER_STATUS_OK, "newsletters.batch_send?key=" . $_REQUEST['key']);
        } else {

            fn_set_storage_data('newsletters_batch_' . $_REQUEST['key']);

            fn_set_notification('N', __('notice'), __('text_newsletter_sent'));

            $suffix = sizeof($data['send_ids']) == 1 ? ".update?newsletter_id=" . array_pop($data['send_ids']) : '.manage';

            return array(CONTROLLER_STATUS_OK, "newsletters$suffix");
        }
    }

    fn_set_notification('W', __('warning'), __('warning_newsletter_no_recipients'));

    return array(CONTROLLER_STATUS_OK, "newsletters.manage");

// return template body
} elseif ($mode == 'render') {
    if (defined('AJAX_REQUEST')) {
        $template_id = !empty($_REQUEST['template_id']) ? intval($_REQUEST['template_id']) : 0;
        if ($template_id) {
            $template = fn_get_newsletter_data($template_id, DESCR_SL);
            Registry::get('ajax')->assign('template', $template['body_html']);
        }

        exit();
    }

// newsletter update page
} elseif ($mode == 'update') {
    $newsletter_id = !empty($_REQUEST['newsletter_id']) ? intval($_REQUEST['newsletter_id']) : 0;

    $newsletter_data = fn_get_newsletter_data($newsletter_id, DESCR_SL);

    if (empty($newsletter_data)) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

    $campaigns = db_get_hash_array("SELECT * FROM ?:newsletter_campaigns AS n LEFT JOIN ?:common_descriptions AS d ON n.campaign_id = d.object_id AND d.lang_code = ?s WHERE d.object_holder = 'newsletter_campaigns' AND n.status = 'A'", 'campaign_id', DESCR_SL);

    Registry::get('view')->assign('newsletter_campaigns', $campaigns);

    $links = db_get_array("SELECT * FROM ?:newsletter_links WHERE newsletter_id=?i", $newsletter_id);
    Registry::get('view')->assign('newsletter_links', $links);

    Registry::get('view')->assign('newsletter', $newsletter_data);

    list($newsletter_templates) = fn_get_newsletters(array('type' => NEWSLETTER_TYPE_TEMPLATE, 'only_available' => false), 0, DESCR_SL);
    Registry::get('view')->assign('newsletter_templates', $newsletter_templates);
    Registry::get('view')->assign('newsletter_type', $newsletter_data['type']);
    Registry::get('view')->assign('placeholders', $placeholders[$newsletter_data['type']]);

    $mailing_lists = db_get_hash_array("SELECT * FROM ?:mailing_lists AS m INNER JOIN ?:common_descriptions AS d ON m.list_id = d.object_id WHERE d.object_holder = 'mailing_lists' AND d.lang_code = ?s", 'list_id', DESCR_SL);
    if (fn_allowed_for('ULTIMATE')) {
        $mailing_lists = fn_get_shared_companies($mailing_lists);
    }
    Registry::get('view')->assign('mailing_lists', $mailing_lists);

    Registry::get('view')->assign('newsletter_users', db_get_fields("SELECT user_id FROM ?:users WHERE user_id IN(?n) ", explode(',', $newsletter_data['users'])));

// newsletter creation page
} elseif ($mode == 'add') {

    $newsletter_type = !empty($_REQUEST['type']) ? $_REQUEST['type'] : NEWSLETTER_TYPE_NEWSLETTER;

    $campaigns = db_get_array("SELECT * FROM ?:newsletter_campaigns AS n INNER JOIN ?:common_descriptions AS d ON n.campaign_id = d.object_id AND d.lang_code = ?s WHERE d.object_holder='newsletter_campaigns'", DESCR_SL);
    Registry::get('view')->assign('newsletter_campaigns', $campaigns);

    list($newsletter_templates) = fn_get_newsletters(array('type' => NEWSLETTER_TYPE_TEMPLATE, 'only_available' => false), 0, DESCR_SL);
    Registry::get('view')->assign('newsletter_templates', $newsletter_templates);
    Registry::get('view')->assign('newsletter_type', $newsletter_type);
    Registry::get('view')->assign('placeholders', $placeholders[$newsletter_type]);

    list($mailing_lists) = fn_get_mailing_lists(array('only_available' => false));
    if (fn_allowed_for('ULTIMATE')) {
        $mailing_lists = fn_get_shared_companies($mailing_lists);
    }
    Registry::get('view')->assign('mailing_lists', $mailing_lists);

// newsletter creation page
} elseif ($mode == 'preview_popup') {
    Registry::get('view')->display('addons/news_and_emails/views/newsletters/components/preview_popup.tpl');
    exit();

// newsletter manage page
} elseif ($mode == 'manage') {
    // do we list newsletters or templates or autoresponders?
    $newsletter_type = !empty($_REQUEST['type']) ? $_REQUEST['type'] : NEWSLETTER_TYPE_NEWSLETTER;
    // Use pagination for a newsletters
    $params = array(
        'type' => $newsletter_type,
        'only_available' => false
    );

    $items_per_page = 0;
    if ($newsletter_type == NEWSLETTER_TYPE_NEWSLETTER) {
        $params = fn_array_merge($params, $_REQUEST);
        $items_per_page = Registry::get('settings.Appearance.admin_elements_per_page');
    }

    list($newsletters, $search) = fn_get_newsletters($params, $items_per_page, DESCR_SL);
    list($mailing_lists) = fn_get_mailing_lists();

    foreach ($newsletters as $newsletter_id => $data) {
        if (!empty($data['mailing_lists'])) {
            $lists = array();
            foreach ($data['mailing_lists'] as $mailing_list_id) {
                $lists[] = $mailing_lists[$mailing_list_id]['object'];
            }
            $newsletters[$newsletter_id]['mailing_list_names'] = implode(', ', $lists);
        }
    }

    Registry::get('view')->assign('newsletter_type', $newsletter_type);
    Registry::get('view')->assign('mailing_lists', $mailing_lists);
    Registry::get('view')->assign('newsletters', $newsletters);
    Registry::get('view')->assign('search', $search);

    fn_newsletters_generate_sections($newsletter_type);

} elseif ($mode == 'campaigns') {

    list($campaigns, $search) = fn_get_campaigns($_REQUEST, Registry::get('settings.Appearance.admin_elements_per_page'));
    Registry::get('view')->assign('campaigns', $campaigns);
    Registry::get('view')->assign('search', $search);

    fn_newsletters_generate_sections('C');

} elseif ($mode == 'campaign_stats') {

    $campaign = db_get_row("SELECT c.*, d.* FROM ?:newsletter_campaigns AS c INNER JOIN ?:common_descriptions AS d ON c.campaign_id=d.object_id LEFT JOIN ?:newsletters ON c.campaign_id=?:newsletters.campaign_id WHERE d.object_holder='newsletter_campaigns' AND c.campaign_id = ?i AND d.lang_code = ?s", $_REQUEST['campaign_id'], DESCR_SL);
    $stats = db_get_array("SELECT n.*, d.*, SUM(e.clicks) AS clicks FROM ?:newsletters AS n INNER JOIN ?:newsletter_descriptions AS d ON n.newsletter_id=d.newsletter_id LEFT JOIN ?:newsletter_links AS e ON n.newsletter_id = e.newsletter_id AND e.campaign_id = n.campaign_id WHERE n.campaign_id=?i AND d.lang_code = ?s GROUP BY e.newsletter_id", $_REQUEST['campaign_id'], DESCR_SL);
    Registry::get('view')->assign('campaign', $campaign);
    Registry::get('view')->assign('campaign_stats', $stats);

} elseif ($mode == 'delete') {
    if (!empty($_REQUEST['newsletter_id'])) {
        fn_delete_newsletter($_REQUEST['newsletter_id']);
    }

    return array(CONTROLLER_STATUS_REDIRECT, "newsletters.manage");

} elseif ($mode == 'delete_campaign') {
    if (!empty($_REQUEST['campaign_id'])) {
        fn_delete_campaigns((array) $_REQUEST['campaign_id']);
    }

    return array(CONTROLLER_STATUS_REDIRECT, "newsletters.campaigns");
}

function fn_delete_campaigns($campaign_ids)
{
    db_query("DELETE FROM ?:common_descriptions WHERE object_id IN (?n) AND object_holder = 'newsletter_campaigns'", $campaign_ids);
    db_query("DELETE FROM ?:newsletter_campaigns WHERE campaign_id IN (?n)", $campaign_ids);
    db_query("DELETE FROM ?:newsletter_links WHERE campaign_id IN (?n)", $campaign_ids);
    db_query("UPDATE ?:newsletters SET campaign_id = 0 WHERE campaign_id IN (?n)", $campaign_ids);
}

function fn_delete_newsletter($newsletter_id)
{
    db_query("DELETE FROM ?:newsletters WHERE newsletter_id = ?i", $newsletter_id);
    db_query("DELETE FROM ?:newsletter_descriptions WHERE newsletter_id = ?i", $newsletter_id);
}

function fn_get_campaigns($params, $items_per_page = 0, $lang_code = DESCR_SL)
{
    $default_params = array (
        'page' => 1,
        'items_per_page' => $items_per_page
    );

    $params = array_merge($default_params, $params);

    $limit = '';
    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(*) FROM ?:newsletter_campaigns");
        $limit = db_paginate($params['page'], $params['items_per_page']);
    }

    $campaigns = db_get_array("SELECT c.*, d.* FROM ?:newsletter_campaigns AS c INNER JOIN ?:common_descriptions AS d ON c.campaign_id = d.object_id AND lang_code = ?s LEFT JOIN ?:newsletters ON c.campaign_id=?:newsletters.campaign_id WHERE d.object_holder = 'newsletter_campaigns' $limit", $lang_code);

    return array($campaigns, $params);
}

function fn_update_newsletter($newsletter_data, $newsletter_id = 0, $lang_code = DESCR_SL)
{
    if (empty($newsletter_data['mailing_lists'])) {
        $newsletter_data['mailing_lists'] = array();
    }

    if (empty($newsletter_id)) {
        if (empty($newsletter_data['newsletter'])) {
            return false;
        }

        $_data = $newsletter_data;
        $_data['mailing_lists'] = implode(',', $_data['mailing_lists']);

        $newsletter_id = db_query("INSERT INTO ?:newsletters ?e", $_data);

        if (empty($newsletter_id)) {
            return false;
        }

        //
        // Adding news description
        //
        $_data['newsletter_id'] = $newsletter_id;

        foreach (fn_get_translation_languages() as $_data['lang_code'] => $v) {
            db_query("INSERT INTO ?:newsletter_descriptions ?e", $_data);
        }

    } else {
        // we do not need empty title
        if (empty($newsletter_data['newsletter'])) {
            unset($newsletter_data['newsletter']);
        }

        if (empty($newsletter_data['users'])) {
            $newsletter_data['users'] = '';
        }

        $_data = $newsletter_data;
        $_data['mailing_lists'] = implode(',', $_data['mailing_lists']);

        db_query("UPDATE ?:newsletters SET ?u WHERE newsletter_id = ?i", $_data, $newsletter_id);

        // update news descriptions
        db_query("UPDATE ?:newsletter_descriptions SET ?u WHERE newsletter_id=?i AND lang_code=?s", $_data, $newsletter_id, $lang_code);
    }

    if (isset($newsletter_data['campaign_id'])) {
        // for link tracking (to count user clicks on links in our newsletters) we need to rewrite urls in the newsletter.
        fn_rewrite_links($newsletter_data['body_html'], $newsletter_id, $newsletter_data['campaign_id']);
    }

    fn_set_hook('update_newsletter', $newsletter_data, $newsletter_id);

    return $newsletter_id;
}
