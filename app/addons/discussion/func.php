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

use Tygh\Enum\ProductTracking;
use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_get_discussion_company_condition($field)
{
    if (fn_allowed_for('ULTIMATE')) {
        return fn_get_company_condition($field);
    }

    return '';
}

function fn_get_discussion($object_id, $object_type, $get_posts = false, $params = array())
{
    static $cache = array();
    static $customer_companies = null;

    $_cache_key = $object_id . '_' . $object_type;

    if (empty($cache[$_cache_key])) {
        $discussion = db_get_row(
            "SELECT thread_id, type, object_type FROM ?:discussion WHERE object_id = ?i AND object_type = ?s ?p",
            $object_id, $object_type, fn_get_discussion_company_condition('?:discussion.company_id')
        );

        if (empty($discussion) && $object_type == 'M') {
            $company_discussion_type = Registry::ifGet('addons.discussion.company_discussion_type', 'D');
            if ($company_discussion_type != 'D') {
                $discussion = array('object_type' => 'M', 'object_id' => $object_id, 'type' => $company_discussion_type);

                if (fn_allowed_for('ULTIMATE') && Registry::get('runtime.company_id')) {
                    $discussion['company_id'] = Registry::get('runtime.company_id');
                }

                $discussion['thread_id'] = db_query('INSERT INTO ?:discussion ?e', $discussion);
            }
        }

        if (!empty($discussion) && AREA == 'C' && $object_type == 'M' && Registry::ifGet('addons.discussion.company_only_buyers', 'Y') == 'Y') {
            if (empty($_SESSION['auth']['user_id'])) {
                $discussion['disable_adding'] = true;
            } else {
                if ($customer_companies === null) {
                    $customer_companies = db_get_hash_single_array(
                        'SELECT company_id FROM ?:orders WHERE user_id = ?i',
                        array('company_id', 'company_id'), $_SESSION['auth']['user_id']
                    );
                }
                if (empty($customer_companies[$object_id])) {
                    $discussion['disable_adding'] = true;
                }
            }
        }

        fn_set_hook('get_discussion', $object_id, $object_type, $discussion);

        $cache[$_cache_key] = $discussion;
    }

    if (!empty($cache[$_cache_key]) && !isset($cache[$_cache_key]['posts']) && $get_posts == true) {
        $params['thread_id'] = $cache[$_cache_key]['thread_id'];
        $params['avail_only'] = (AREA == 'C'); // FIXME

        $discussion_object_types = fn_get_discussion_objects();

        list($cache[$_cache_key]['posts'], $cache[$_cache_key]['search']) = fn_get_discussion_posts($params, Registry::get('addons.discussion.' . $discussion_object_types[$cache[$_cache_key]['object_type']] . '_posts_per_page'));

        $cache[$_cache_key]['average_rating'] = fn_get_average_rating($cache[$_cache_key]);
    }

    $saved_post_data = fn_restore_post_data('post_data');
    if (!empty($saved_post_data)) {
        $cache[$_cache_key]['post_data'] = $saved_post_data;
    }

    return !empty($cache[$_cache_key]) ? $cache[$_cache_key] : false;
}

function fn_get_discussion_posts($params, $items_per_page = 0)
{
    // Set default values to input params
    $default_params = array (
        'page' => 1,
        'thread_id' => 0,
        'avail_only' => false,
        'random' => false,
        'items_per_page' => $items_per_page
    );

    $params = array_merge($default_params, $params);

    $thread_data = db_get_row(
        "SELECT thread_id, type, object_type, object_id FROM ?:discussion WHERE thread_id = ?i ?p",
        $params['thread_id'], fn_get_discussion_company_condition('?:discussion.company_id')
    );

    if ($thread_data['type'] == 'D') {
        return array(array(), $params);
    }

    $join = $fields = '';

    if ($thread_data['type'] == 'C' || $thread_data['type'] == 'B') {
        $join .= " LEFT JOIN ?:discussion_messages ON ?:discussion_messages.post_id = ?:discussion_posts.post_id ";
        $fields .= ", ?:discussion_messages.message";
    }

    if ($thread_data['type'] == 'R' || $thread_data['type'] == 'B') {
        $join .= " LEFT JOIN ?:discussion_rating ON ?:discussion_rating.post_id = ?:discussion_posts.post_id ";
        $fields .= ", ?:discussion_rating.rating_value";
    }

    $thread_condition = fn_generate_thread_condition($thread_data);

    if ($params['avail_only'] == true) {
        $thread_condition .= " AND ?:discussion_posts.status = 'A'";
    }

    $limit = '';

    if (!empty($params['limit'])) {
        $limit = db_quote("LIMIT ?i", $params['limit']);

    } elseif (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(*) FROM ?:discussion_posts WHERE $thread_condition", $params['thread_id']);
        $limit = db_paginate($params['page'], $params['items_per_page']);
    }

    $order_by = empty($params['random']) ? '?:discussion_posts.timestamp DESC' : 'RAND()';

    $posts = db_get_array(
        "SELECT ?:discussion_posts.* $fields FROM ?:discussion_posts $join "
        . "WHERE $thread_condition ORDER BY ?p $limit",
        $order_by
    );

    return array($posts, $params);
}

function fn_generate_thread_condition($thread_data)
{
    $thread_condition = '';

    if (AREA == 'C') {
        if ($thread_data['object_type'] == 'P') {
            $thread_condition = fn_generate_thread_condition_by_setting('product_share_discussion', $thread_data);
        } elseif ($thread_data['object_type'] == 'A') {
            $thread_condition = fn_generate_thread_condition_by_setting('page_share_discussion', $thread_data);
        } elseif ($thread_data['object_type'] == 'E') {
            $thread_condition = fn_generate_thread_condition_by_setting('testimonials_from_all_stores', $thread_data);
        } elseif ($thread_data['object_type'] == 'N') {
            $thread_condition = fn_generate_thread_condition_by_setting('news_share_discussion', $thread_data);
        }
    }

    if (empty($thread_condition)) {
        $thread_condition = db_quote("?:discussion_posts.thread_id = ?i", $thread_data['thread_id']);
    }

    return $thread_condition;
}

function fn_generate_thread_condition_by_setting($setting_name, $thread_data)
{
    if (!empty($thread_data['object_type']) && isset($thread_data['object_id'])) {
        if (Registry::ifGet('addons.discussion.' . $setting_name, 'N') == 'Y') {
            return  db_quote(
                "?:discussion_posts.thread_id IN (?a)",
                db_get_fields(
                    "SELECT thread_id FROM ?:discussion WHERE object_type = ?s AND object_id = ?i",
                    $thread_data['object_type'], $thread_data['object_id']
                )
            );
        }
    }

    return '';
}

function fn_delete_discussion($object_id, $object_type)
{
    $thread_id = db_get_field("SELECT thread_id FROM ?:discussion WHERE object_id IN (?n) AND object_type = ?s", $object_id, $object_type);

    if (!empty($thread_id)) {
        db_query("DELETE FROM ?:discussion_messages WHERE thread_id = ?i", $thread_id);
        db_query("DELETE FROM ?:discussion_posts WHERE thread_id = ?i", $thread_id);
        db_query("DELETE FROM ?:discussion_rating WHERE thread_id = ?i", $thread_id);
        db_query("DELETE FROM ?:discussion WHERE thread_id = ?i", $thread_id);

        return true;
    }

    return false;
}

function fn_discussion_update_product_post(&$product_data, &$product_id)
{
    if (empty($product_data['discussion_type'])) {
        return false;
    }
    if (empty($product_data['company_id'])) {
        $product_company_id = db_get_field('SELECT company_id FROM ?:products WHERE product_id = ?i', $product_id);
        if (!empty($product_company_id)) {
            $product_data['company_id'] = $product_company_id;
        } else {
            if (Registry::get('runtime.company_id')) {
                $product_company_id = $product_data['company_id'] = Registry::get('runtime.company_id');
            }
        }
    }

    $discussion = array(
        'object_type' => 'P',
        'object_id' => $product_id,
        'type' => $product_data['discussion_type'],
        'company_id' => $product_data['company_id']
    );

    fn_update_discussion($discussion);
}

function fn_discussion_delete_product_post(&$product_id)
{
    return fn_delete_discussion($product_id, 'P');
}

function fn_discussion_update_category_post(&$category_data, &$category_id)
{
    if (empty($category_data['discussion_type'])) {
        return false;
    }

    $discussion = array(
        'object_type' => 'C',
        'object_id' => $category_id,
        'type' => $category_data['discussion_type']
    );

    fn_update_discussion($discussion);
}

function fn_discussion_delete_category_after(&$category_id)
{
    return fn_delete_discussion($category_id, 'C');
}

function fn_discussion_delete_order(&$order_id)
{
    return fn_delete_discussion($order_id, 'O');
}

function fn_discussion_update_page_post(&$page_data, &$page_id)
{
    if (empty($page_data['discussion_type'])) {
        return false;
    }

    $discussion = array(
        'object_type' => 'A',
        'object_id' => $page_id,
        'type' => $page_data['discussion_type'],
        'for_all_companies' => 1
    );

    fn_update_discussion($discussion);
}

function fn_discussion_delete_page(&$page_id)
{
    return fn_delete_discussion($page_id, 'A');
}

function fn_discussion_update_news(&$news_data, &$news_id)
{
    if (empty($news_data['discussion_type'])) {
        return false;
    }

    $discussion = array(
        'object_type' => 'N',
        'object_id' => $news_id,
        'type' => $news_data['discussion_type'],
        'for_all_companies' => 1
    );

    fn_update_discussion($discussion);
}

// FIX-EVENTS
function fn_discussion_delete_news(&$news_id)
{
    return fn_delete_discussion($news_id, 'N');
}

function fn_discussion_update_event(&$event_data, &$event_id)
{
    if (empty($event_data['discussion_type'])) {
        return false;
    }

    $discussion = array(
        'object_type' => 'G',
        'object_id' => $event_id,
        'type' => $event_data['discussion_type']
    );

    fn_update_discussion($discussion);
}

// FIX-EVENTS
function fn_discussion_delete_event(&$event_id)
{
    return fn_delete_discussion($event_id, 'G');
}

//
// Get average rating
//
function fn_get_discussion_rating($rating_value)
{
    static $cache = array();

    if (!isset($cache[$rating_value])) {
        $cache[$rating_value] = array();
        $cache[$rating_value]['full'] = floor($rating_value);
        $cache[$rating_value]['part'] = $rating_value - $cache[$rating_value]['full'];
        $cache[$rating_value]['empty'] = 5 - $cache[$rating_value]['full'] - (($cache[$rating_value]['part'] == 0) ? 0 : 1);

        if (!empty($cache[$rating_value]['part'])) {
            if ($cache[$rating_value]['part'] <= 0.25) {
                $cache[$rating_value]['part'] = 1;
            } elseif ($cache[$rating_value]['part'] <= 0.5) {
                $cache[$rating_value]['part'] = 2;
            } elseif ($cache[$rating_value]['part'] <= 0.75) {
                $cache[$rating_value]['part'] = 3;
            } elseif ($cache[$rating_value]['part'] <= 0.99) {
                $cache[$rating_value]['part'] = 4;
            }
        }
    }

    return $cache[$rating_value];
}

//
// Get thread average rating
//
function fn_get_average_rating($discussion)
{
    if (empty($discussion) || ($discussion['type'] != 'R' && $discussion['type'] != 'B')) {
        return false;
    }

    $rating = db_get_field("SELECT AVG(a.rating_value) as val FROM ?:discussion_rating as a LEFT JOIN ?:discussion_posts as b ON a.post_id = b.post_id WHERE a.thread_id = ?i AND b.status = 'A' AND a.rating_value > ?i", $discussion['thread_id'], 0);

    $rating = number_format($rating, 1);

    return intval($rating) == $rating ? intval($rating) : $rating;
}

function fn_get_discussion_object_data($object_id, $object_type, $lang_code = CART_LANGUAGE)
{
    $data = array();

    // product
    if ($object_type == 'P') {
        $data['description'] = db_get_field("SELECT product FROM ?:product_descriptions WHERE product_id = ?i AND lang_code = ?s", $object_id, $lang_code);
        if (AREA == 'A') {
            $data['url'] = "products.update?product_id=$object_id&selected_section=discussion";
        } else {
            $data['url'] = "products.view?product_id=$object_id";
        }
    } elseif ($object_type == 'C') { // category
        $data['description'] = db_get_field("SELECT category FROM ?:category_descriptions WHERE category_id = ?i AND lang_code = ?s", $object_id, $lang_code);
        if (AREA == 'A') {
            $data['url'] = "categories.update?category_id=$object_id&selected_section=discussion";
        } else {
            $data['url'] = "categories.view?category_id=$object_id";
        }

    } elseif ($object_type == 'M') { // company
        $data['description'] = fn_get_company_name($object_id);
        if (AREA == 'A') {
            $data['url'] = "companies.update?company_id=$object_id&selected_section=discussion";
        } else {
            $data['url'] = "companies.view?company_id=$object_id";
        }

    // order
    } elseif ($object_type == 'O') {
        $data['description'] = '#'.$object_id;
        if (AREA == 'A') {
            $data['url'] = "orders.details?order_id=$object_id&selected_section=discussion";
        } else {
            $data['url'] = "orders.details?order_id=$object_id";
        }

    // page
    } elseif ($object_type == 'A') {
        $data['description'] = db_get_field("SELECT page FROM ?:page_descriptions WHERE page_id = ?i AND lang_code = ?s", $object_id, $lang_code);

        if (AREA == 'A') {
            $data['url'] = "pages.update?page_id=$object_id&selected_section=discussion";
        } else {
            $data['url'] = "pages.view?page_id=$object_id";
        }

    // Site layout/testimonials
    } elseif ($object_type == 'E') {
        $data['description'] = __('discussion_title_home_page');
        if (AREA == 'A') {
            $data['url'] = "discussion.update?discussion_type=E";
        } else {
            $data['url'] = '';
        }
    }

    fn_set_hook('get_discussion_object_data', $data, $object_id, $object_type);

    return $data;
}

function fn_get_discussion_objects()
{
    static $discussion_object_types = array(
        'P' => 'product',
        'C' => 'category',
        'A' => 'page',
        'O' => 'order',
        'E' => 'home_page',
    );

    if (fn_allowed_for('MULTIVENDOR')) {
        $discussion_object_types['M'] = 'company';
    }

    fn_set_hook('get_discussion_objects', $discussion_object_types);

    return $discussion_object_types;

}

//
// Clone discussion
//
function fn_clone_discussion($object_id, $new_object_id, $object_type)
{

    // Clone attachment
    $data = db_get_row("SELECT * FROM ?:discussion WHERE object_id = ?i AND object_type = ?s", $object_id, $object_type);

    if (empty($data)) {
        return false;
    }

    $old_thread_id = $data['thread_id'];
    $data['object_id'] = $new_object_id;
    unset($data['thread_id']);
    $thread_id = db_query("REPLACE INTO ?:discussion ?e", $data);

    // Clone posts
    $data = db_get_array("SELECT * FROM ?:discussion_posts WHERE thread_id = ?i", $old_thread_id);
    foreach ($data as $v) {
        $old_post_id = $v['post_id'];
        $v['thread_id'] = $thread_id;
        unset($v['post_id']);
        $post_id = db_query("INSERT INTO ?:discussion_posts ?e", $v);

        $message = db_get_row("SELECT * FROM ?:discussion_messages WHERE post_id = ?i", $old_post_id);
        $message['post_id'] = $post_id;
        $message['thread_id'] = $thread_id;
        db_query("INSERT INTO ?:discussion_messages ?e", $message);

        $rating = db_get_row("SELECT * FROM ?:discussion_rating WHERE post_id = ?i", $old_post_id);
        $rating['post_id'] = $post_id;
        $rating['thread_id'] = $thread_id;
        db_query("INSERT INTO ?:discussion_rating ?e", $rating);
    }

    return true;
}

function fn_discussion_clone_product(&$product_id, &$to_product_id)
{
    fn_clone_discussion($product_id, $to_product_id, 'P');
}

function fn_get_rating_list($object_type, $parent_object_id = '')
{

    $object2parent_links = array(
        'P' => array(	//	for product
            'table' => '?:categories',
            'field' => 'category_id',
            'join' => array('?:products_categories' => "?:discussion.object_id=?:products_categories.product_id AND ?:products_categories.link_type='M'",
                            '?:categories' => "?:products_categories.category_id=?:categories.category_id"),
        )/*,
        'A' => array(	// for page
            'table' => '?:topics',
            'field' => 'topic_id',
            'join' => array('?:pages_topics' => "?:discussion.object_id=?:pages_topics.page_id AND ?:pages_topics.link_type='M'",
            '?:topics' => "?:pages_topics.topic_id=?:topics.topic_id"),
        )*/
    );

    $query = db_quote(" object_type = ?s AND ?:discussion.type IN ('R', 'B') AND !(?:discussion_rating.rating_value IS NULL) ", $object_type);
    $join = array();
    if (isset($object2parent_links[$object_type]) && !empty($parent_object_id)) {
        $path = db_get_field("SELECT id_path FROM {$object2parent_links[$object_type]['table']} WHERE {$object2parent_links[$object_type]['field']} = ?i", $parent_object_id);
        $parent_object_ids = db_get_fields("SELECT {$object2parent_links[$object_type]['field']} FROM {$object2parent_links[$object_type]['table']} WHERE id_path LIKE ?l", "$path/%");
        $parent_object_ids[] = $parent_object_id;
        $query .= " AND {$object2parent_links[$object_type]['table']}.{$object2parent_links[$object_type]['field']} IN ('" . implode("','", $parent_object_ids) . "') AND {$object2parent_links[$object_type]['table']}.status='A'";
        $join = $object2parent_links[$object_type]['join'];
    }

    if ($object_type == 'P') {
        // Adding condition for the "Show out of stock products" setting
        if (Registry::get('settings.General.inventory_tracking') == 'Y' && Registry::get('settings.General.show_out_of_stock_products') == 'N' && AREA == 'C') {
            $join["?:product_options_inventory AS inventory"] =  "inventory.product_id=?:discussion.object_id";
            $join['?:products'] = "?:products.product_id=?:discussion.object_id";
            $query .= db_quote(
                " AND IF(?:products.tracking=?s, inventory.amount>0, ?:products.amount>0)",
                ProductTracking::TRACK_WITH_OPTIONS
            );
        }
    }

    $join_conditions = '';
    foreach ($join as $table => $j_cond) {
        $join_conditions .= " LEFT JOIN $table ON $j_cond ";
    }

    return db_get_hash_array(
        "SELECT object_id, avg(rating_value) AS rating FROM ?:discussion "
        . "LEFT JOIN ?:discussion_rating ON ?:discussion.thread_id=?:discussion_rating.thread_id $join_conditions "
        . "WHERE ?p GROUP BY ?:discussion.thread_id ORDER BY rating DESC",
        'object_id', $query . fn_get_discussion_company_condition('?:discussion.company_id')
    );
}

function fn_is_accessible_discussion($data, &$auth)
{
    $access = false;

    if ($data['object_type'] == 'P') {//product
        $access = fn_get_product_data($data['object_id'], $auth, CART_LANGUAGE, $field_list = '?:products.product_id', false, false, false);

    } elseif ($data['object_type'] == 'C') {//category
        $access = fn_get_category_data($data['object_id'], '', $field_list = '?:categories.category_id', false);

    } elseif ($data['object_type'] == 'M') {//company
        $access = fn_get_company_data($data['object_id']);

    } elseif ($data['object_type'] == 'O') {//order
        if (!empty($auth['user_id'])) {
            $access = db_get_field("SELECT order_id FROM ?:orders WHERE order_id = ?i AND user_id = ?i", $data['object_id'], $auth['user_id']);
        } elseif (!empty($auth['order_ids'])) {
            $access = in_array($data['object_id'], $auth['order_ids']);
        }

    } elseif ($data['object_type'] == 'A') {// page
        $access = fn_get_page_data($data['object_id'], CART_LANGUAGE);

    } elseif ($data['object_type'] == 'E') {// testimonials
        $access = true;
    }

    fn_set_hook('is_accessible_discussion', $data, $auth, $access);

    return !empty($access);
}

function fn_discussion_get_product_data(&$product_id, &$field_list, &$join)
{
    $field_list .= ", ?:discussion.type as discussion_type";
    $join .= " LEFT JOIN ?:discussion ON ?:discussion.object_id = ?:products.product_id AND ?:discussion.object_type = 'P'";

    if (fn_allowed_for('ULTIMATE') && Registry::ifGet('addons.discussion.product_share_discussion', 'N') == 'N' && Registry::get('runtime.company_id')) {
        $join .= " AND ?:discussion.company_id = " . Registry::get('runtime.company_id');
    }

    return true;
}

function fn_update_discussion($data)
{
    if (!empty($data['for_all_companies'])) {
        if (isset($data['thread_id'])) {
            unset($data['thread_id']);
        }

        foreach (fn_get_all_companies_ids() as $company) {
            $data['company_id'] = $company;
            db_replace_into('discussion', $data);
        }
    } else {
        if (fn_allowed_for('ULTIMATE') && Registry::get('runtime.company_id')) {
            $data['company_id'] = Registry::get('runtime.company_id');
        }

        db_replace_into('discussion', $data);
    }

    return true;
}

function fn_discussion_get_products(&$params, &$fields, &$sortings, &$condition, &$join, &$sorting, &$group_by, &$lang_code, &$having)
{
    $fields[] = 'AVG(?:discussion_rating.rating_value) AS average_rating';
    $join .= db_quote(" LEFT JOIN ?:discussion ON ?:discussion.object_id = products.product_id AND ?:discussion.object_type = 'P'");

    if (fn_allowed_for('ULTIMATE') && Registry::ifGet('addons.discussion.product_share_discussion', 'N') == 'N' && Registry::get('runtime.company_id')) {
        $join .= " AND ?:discussion.company_id = " . Registry::get('runtime.company_id');
    }

    $join .= db_quote(" LEFT JOIN ?:discussion_posts ON ?:discussion_posts.thread_id = ?:discussion.thread_id AND ?:discussion_posts.status = 'A'");
    $join .= db_quote(" LEFT JOIN ?:discussion_rating ON ?:discussion.thread_id = ?:discussion_rating.thread_id AND ?:discussion_rating.post_id = ?:discussion_posts.post_id");

    if (!empty($params['rating'])) {
        $having[] = db_quote("average_rating > 0");
        $params['sort_by'] = 'rating';
        $params['sort_order'] = 'desc';
        $sortings['rating'] = 'average_rating';
    }

    return true;
}

function fn_discussion_get_categories(&$params, &$join, &$condition, &$fields, &$group_by, &$sortings)
{
    if (!empty($params['rating'])) {
        $fields[] = 'avg(?:discussion_rating.rating_value) AS rating';
        $join .= db_quote(" INNER JOIN ?:discussion ON ?:discussion.object_id = ?:categories.category_id AND ?:discussion.object_type = 'C'");
        $join .= db_quote(" INNER JOIN ?:discussion_rating ON ?:discussion.thread_id=?:discussion_rating.thread_id");
        $join .= db_quote(" INNER JOIN ?:discussion_posts ON ?:discussion_posts.post_id=?:discussion_rating.post_id AND ?:discussion_posts.status = 'A'");
        $group_by = 'GROUP BY ?:discussion_rating.thread_id';
        $sortings['rating'] = 'rating';
        $params['sort_by'] = 'rating';
        $params['sort_order'] = 'asc';
    }

    return true;
}

function fn_discussion_get_pages(&$params, &$join, &$conditions, &$fields, &$group_by, &$sortings)
{
    if (!empty($params['rating'])) {
        $fields[] = 'avg(?:discussion_rating.rating_value) AS rating';
        $join .= db_quote(" INNER JOIN ?:discussion ON ?:discussion.object_id = ?:pages.page_id AND ?:discussion.object_type = 'A'");

        if (fn_allowed_for('ULTIMATE') && Registry::ifGet('addons.discussion.page_share_discussion', 'N') == 'N' && Registry::get('runtime.company_id')) {
            $join .= " AND ?:discussion.company_id = " . Registry::get('runtime.company_id');
        }

        $join .= db_quote(" INNER JOIN ?:discussion_rating ON ?:discussion.thread_id=?:discussion_rating.thread_id");
        $join .= db_quote(" INNER JOIN ?:discussion_posts ON ?:discussion_posts.post_id=?:discussion_rating.post_id AND ?:discussion_posts.status = 'A'");
        $group_by = '?:discussion_rating.thread_id';
        $sortings['rating'] = 'rating';
        $params['sort_by'] = 'rating';
        $params['sort_order'] = 'desc';
    }

    return true;
}

function fn_discussion_get_companies(&$params, &$fields, &$sortings, &$condition, &$join, &$auth, &$lang_code, &$group_by)
{
    $fields[] = 'AVG(?:discussion_rating.rating_value) AS average_rating';
    $fields[] = "CONCAT(?:companies.company_id, '_', IF (?:discussion_rating.thread_id, ?:discussion_rating.thread_id, '0')) AS company_thread_ids";
    $join .= db_quote(" LEFT JOIN ?:discussion ON ?:discussion.object_id = ?:companies.company_id AND ?:discussion.object_type = 'M'");
    $join .= db_quote(" LEFT JOIN ?:discussion_posts ON ?:discussion_posts.thread_id = ?:discussion.thread_id AND ?:discussion_posts.status = 'A'");
    $join .= db_quote(" LEFT JOIN ?:discussion_rating ON ?:discussion.thread_id = ?:discussion_rating.thread_id AND ?:discussion_rating.post_id = ?:discussion_posts.post_id");
    $group_by = 'GROUP BY company_thread_ids';

    if (!empty($params['sort_by']) && $params['sort_by'] == 'rating') {
        $group_by .= ' HAVING average_rating > 0';
        $sortings['rating'] = 'average_rating';
    }
}

function fn_discussion_companies_sorting(&$sorting)
{
    if (in_array(Registry::get('addons.discussion.company_discussion_type'), array('B', 'R'))) {
        $sorting['rating'] = array('description' => __('rating'), 'default_order' => 'desc');
    }
}

function fn_discussion_delete_company(&$company_id)
{
    return fn_delete_discussion($company_id, 'M');
}

function fn_discussion_get_predefined_statuses(&$type, &$statuses)
{
    if ($type == 'discussion') {
        $statuses['discussion'] = array(
            'A' => __('approved'),
            'D' => __('disapproved')
        );
    }
}

/**
 * Delete post bu identifier
 *
 * @param int $post_id Post identifier
 * @return boolean Always true
 */
function fn_discussion_delete_post($post_id)
{
    db_query("DELETE FROM ?:discussion_messages WHERE post_id = ?i", $post_id);
    db_query("DELETE FROM ?:discussion_rating WHERE post_id = ?i", $post_id);
    db_query("DELETE FROM ?:discussion_posts WHERE post_id = ?i", $post_id);

    return true;
}

/**
 * Update multiple posts at once
 * @param array $posts posts data
 * @return boolean always true
 */
function fn_update_discussion_posts($posts)
{
    if (!empty($posts) && is_array($posts)) {
        $threads = db_get_hash_single_array("SELECT post_id, thread_id FROM ?:discussion_posts WHERE post_id IN (?n)", array('post_id', 'thread_id'), array_keys($posts));
        $messages_exist = db_get_fields("SELECT post_id FROM ?:discussion_messages WHERE post_id IN (?n)", array_keys($posts));
        $rating_exist = db_get_fields("SELECT post_id FROM ?:discussion_rating WHERE post_id IN (?n)", array_keys($posts));
        fn_delete_notification('company_access_denied');

        foreach ($posts as $p_id => $data) {
            db_query("UPDATE ?:discussion_posts SET ?u WHERE post_id = ?i", $data, $p_id);
            if (in_array($p_id, $messages_exist)) {
                db_query("UPDATE ?:discussion_messages SET ?u WHERE post_id = ?i", $data, $p_id);
            } else {
                $data['thread_id'] = $threads[$p_id];
                $data['post_id'] = $p_id;
                db_query("INSERT INTO ?:discussion_messages ?e", $data);
            }

            if (in_array($p_id, $rating_exist)) {
                db_query("UPDATE ?:discussion_rating SET ?u WHERE post_id = ?i", $data, $p_id);
            } else {
                $data['thread_id'] = $threads[$p_id];
                $data['post_id'] = $p_id;
                db_query("INSERT INTO ?:discussion_rating ?e", $data);
            }
        }
    }

    return true;
}

/**
 * Hook: add discussion to secure controllers list
 * @param array &$controllers secure controllers list
 */
function fn_discussion_init_secure_controllers(&$controllers)
{
     $controllers['discussion'] = 'passive';
}

/**
 * Gets available rating values with titles
 *
 * @return array Rating values list
 */
function fn_get_discussion_ratings()
{
    $rates = array (
        5 => __("excellent"),
        4 => __("very_good"),
        3 => __("average"),
        2 => __("fair"),
        1 => __("poor")
    );

    return $rates;
}

function fn_create_empty_thread($type, $company_id = null)
{
    $discussion = array(
        'type' => $type,
        'object_type' => 'E',
        'object_id' => 0,
    );

    if (is_null($company_id)) {
        if (fn_allowed_for('ULTIMATE')) {
            if (!Registry::get('runtime.company_id')) {
                $discussion['for_all_companies'] = 1;
            } else {
                $discussion['company_id'] = Registry::get('runtime.company_id');
            }
        }
    } else {
        $discussion['company_id'] = $company_id;
    }

    if (function_exists('fn_update_discussion')) {
        fn_update_discussion($discussion);
    }

    return true;
}

function fn_discussion_update_company(&$company_data, &$company_id, &$lang_code, &$action)
{
    if ($action == 'add') {
        $type = Registry::get('addons.discussion.home_page_testimonials');
        if ($type != 'D') {
            fn_create_empty_thread($type, $company_id);
        }
    }
}
