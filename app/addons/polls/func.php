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

use Tygh\BlockManager\Block;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

/**
 * Update poll
 *
 * @param array $page_data page data
 * @param int $page_id ID of the page, poll attached to
 * @param string $lang_code language code
 * @return bool always true
 */
function fn_polls_update_page_post(&$page_data, &$page_id, &$lang_code)
{
    if (empty($page_id) || empty($page_data['page_type']) || $page_data['page_type'] != PAGE_TYPE_POLL) {
        return false;
    }

    $exists = db_get_field('SELECT COUNT(*) FROM ?:polls WHERE page_id = ?i', $page_id) ? true : false;

    $data = $page_data['poll_data'];

    $types = array (
        'H' => 'header',
        'F' => 'footer',
        'R' => 'results'
    );

    if (empty($exists)) {
        $data['page_id'] = $page_id;
        db_query('INSERT INTO ?:polls ?e', $data);

        foreach ($types as $type => $elm) {
            $_data = array (
                'description' => $data[$elm],
                'object_id' => $page_id,
                'type' => $type,
                'page_id' => $page_id
            );

            foreach (fn_get_translation_languages() as $_data['lang_code'] => $v) {
                db_query("INSERT INTO ?:poll_descriptions ?e", $_data);
            }
        }

    } else {
        db_query('UPDATE ?:polls SET ?u WHERE page_id = ?i', $data, $page_id);

        foreach ($types as $type => $elm) {
            $_data = array (
                'description' => $data[$elm],
            );

            db_query('UPDATE ?:poll_descriptions SET ?u WHERE object_id = ?i AND lang_code = ?s AND type = ?s', $_data, $page_id, $lang_code, $type);
        }
    }

    return true;
}

/**
 * Delete poll
 *
 * @param int $page_id ID of the page, poll attached to
 * @return bool true if poll was deleted, false - otherwise
 */
function fn_polls_delete_page(&$page_id)
{
    if (!empty($page_id)) {

        $item_ids = db_get_fields("SELECT item_id FROM ?:poll_items WHERE page_id = ?i", $page_id);

        db_query("DELETE FROM ?:polls_answers WHERE item_id IN (?n)", $item_ids);

        db_query("DELETE FROM ?:poll_items WHERE page_id = ?i", $page_id);
        db_query("DELETE FROM ?:poll_descriptions WHERE page_id = ?i", $page_id);
        db_query("DELETE FROM ?:polls WHERE page_id = ?i", $page_id);
        db_query("DELETE FROM ?:polls_votes WHERE page_id = ?i", $page_id);

        Block::instance()->removeDynamicObjectData('polls', $page_id);

        return true;
    }

    return false;
}

/**
 * Clone poll
 *
 * @param int $page_id ID of the page, poll attached to
 * @param int $clone_id ID of the page, poll will be clonned and attached to
 * @return bool always true
 */
function fn_polls_clone_page(&$page_id, &$clone_id)
{
    $poll = db_get_row('SELECT * FROM ?:polls WHERE page_id = ?i', $page_id);
    $poll['page_id'] = $clone_id;

    db_query('INSERT INTO ?:polls ?e', $poll);

    $descriptions = db_get_array('SELECT * FROM ?:poll_descriptions WHERE object_id = ?i AND type IN (?a)', $page_id, array ('H', 'F', 'R'));
    foreach ($descriptions as $array) {
        $array['object_id'] = $clone_id;
        $array['page_id'] = $clone_id;

        db_query('INSERT INTO ?:poll_descriptions ?e', $array);
    }

    $questions = db_get_array('SELECT * FROM ?:poll_items WHERE parent_id = ?i AND type IN (?a)', $page_id, array ('Q', 'M', 'T'));

    foreach ($questions as $question) {
        $question_id = $question['item_id'];
        unset($question['item_id']);
        $question['page_id'] = $clone_id;
        $question['parent_id'] = $clone_id;

        $new_question_id = db_query('INSERT INTO ?:poll_items ?e', $question);

        $descriptions = db_get_array("SELECT * FROM ?:poll_descriptions WHERE object_id = ?i AND type = 'I'", $question_id);
        foreach ($descriptions as $array) {
            $array['object_id'] = $new_question_id;
            $array['page_id'] = $clone_id;

            db_query('INSERT INTO ?:poll_descriptions ?e', $array);
        }

        $answers = db_get_array('SELECT * FROM ?:poll_items WHERE parent_id = ?i AND type IN (?a)', $question_id, array ('A', 'O'));

        foreach ($answers as $answer) {
            $answer_id = $answer['item_id'];
            unset($answer['item_id']);
            $answer['page_id'] = $clone_id;
            $answer['parent_id'] = $new_question_id;

            $new_answer_id = db_query('INSERT INTO ?:poll_items ?e', $answer);

            $descriptions = db_get_array("SELECT * FROM ?:poll_descriptions WHERE object_id = ?i AND type = 'I'", $answer_id);
            foreach ($descriptions as $array) {
                $array['object_id'] = $new_answer_id;
                $array['page_id'] = $clone_id;

                db_query('INSERT INTO ?:poll_descriptions ?e', $array);
            }

        }
    }

    return true;
}

/**
 * Get poll data
 *
 * @param int $page_id ID of the page, poll attached to
 * @param string $lang_code language code to get descriptions for
 * @return mixed array with poll data if exists, false otherwise
 */
function fn_get_poll_data($page_id, $lang_code = CART_LANGUAGE)
{
    $poll = db_get_row("SELECT page_id, start_date, end_date, show_results FROM ?:polls WHERE page_id = ?i", $page_id);

    if (empty($poll)) {
        return false;
    }

    $descriptions = db_get_hash_single_array("SELECT type, description FROM ?:poll_descriptions WHERE object_id = ?i AND lang_code = ?s AND type IN ('H', 'F', 'R')", array('type', 'description'), $page_id, $lang_code);

    if (!empty($descriptions)) {
        $poll['header'] = $descriptions['H'];
        $poll['footer'] = $descriptions['F'];
        $poll['results'] = $descriptions['R'];
    }

    // Get questions and answers
    $poll['questions'] = db_get_hash_array("SELECT ?:poll_items.item_id, ?:poll_items.type, ?:poll_items.position, ?:poll_descriptions.description, ?:poll_items.required FROM ?:poll_items LEFT JOIN ?:poll_descriptions ON ?:poll_items.item_id = ?:poll_descriptions.object_id AND ?:poll_descriptions.type = 'I' AND ?:poll_descriptions.lang_code = ?s WHERE ?:poll_items.parent_id = ?i AND ?:poll_items.type IN ('Q','M', 'T') ORDER BY ?:poll_items.position", 'item_id', $lang_code, $page_id);

    $poll['has_required_questions'] = false;
    foreach ($poll['questions'] as $question_id => $entry) {
        $poll['questions'][$question_id]['answers'] = db_get_hash_array("SELECT ?:poll_items.item_id, ?:poll_items.type, ?:poll_items.position, ?:poll_descriptions.description FROM ?:poll_items LEFT JOIN ?:poll_descriptions ON ?:poll_items.item_id = ?:poll_descriptions.object_id AND ?:poll_descriptions.type = 'I' AND ?:poll_descriptions.lang_code = ?s WHERE ?:poll_items.parent_id = ?i AND ?:poll_items.type IN ('A', 'O') ORDER BY ?:poll_items.position", 'item_id', $lang_code, $question_id);

        if ($entry['required'] == 'Y') {
            $poll['has_required_questions'] = true;
        }

        // Check if answer has comments
        if ($entry['type'] == 'T') {
            $count = db_get_field("SELECT COUNT(item_id) FROM ?:polls_answers WHERE item_id = ?i AND answer_id = 0", $question_id);
            $poll['questions'][$question_id]['has_comments'] = $count ? true : false;

        } else {
            foreach ($poll['questions'][$question_id]['answers'] as $k => $rec) {
                if ($rec['type'] == 'O') {
                    $count = db_get_field("SELECT count(item_id) FROM ?:polls_answers WHERE item_id = ?i AND answer_id = ?i AND comment != ''", $question_id, $k);
                    $poll['questions'][$question_id]['answers'][$k]['has_comments'] = $count ? true : false;
                } else {
                    $poll['questions'][$question_id]['answers'][$k]['has_comments'] = false;
                }
            }
        }
    }

    // Check if poll completed by the current user
    $ip = fn_get_ip();
    $poll['completed'] = db_get_field("SELECT vote_id FROM ?:polls_votes WHERE page_id = ?i AND ip_address = ?s", $page_id, $ip['host']);

    if (!empty($poll['completed']) || AREA == 'A') {
        fn_polls_get_results($poll);
    }

    return $poll;
}

/**
 * Get poll for current page
 *
 * @param array $page_data data of the current page
 * @param string $lang_code language code to get descriptions for
 * @return boolean always true
 */
function fn_polls_get_page_data(&$page_data, &$lang_code)
{
    $page_data['poll'] = fn_get_poll_data($page_data['page_id'], $lang_code);

    return true;
}

/**
 * Get poll results
 *
 * @param array $poll poll data
 * @return boolean true if results exists, false otherwise
 */
function fn_polls_get_results(&$poll)
{
    if (empty($poll['questions'])) {
        return false;
    }

    $total_voted = db_get_field("SELECT COUNT(vote_id) FROM ?:polls_votes WHERE page_id = ?i", $poll['page_id']);

    if (!$total_voted) {
        return false;
    }

    $total_completed = db_get_field("SELECT COUNT(vote_id) FROM ?:polls_votes WHERE page_id = ?i AND type = 'C'", $poll['page_id']);

    $first_submit = db_get_field("SELECT MIN(time) FROM ?:polls_votes WHERE page_id = ?i", $poll['page_id']);
    $last_submit = db_get_field("SELECT MAX(time) FROM ?:polls_votes WHERE page_id = ?i", $poll['page_id']);

    $poll['summary'] = array(
        'total' => $total_voted,
        'completed' => $total_completed,
        'first' => $first_submit,
        'last' => $last_submit
    );

    $results = array();

    foreach ($poll['questions'] as $key => $entry) {
        if ($entry['type'] == 'T') {
            $count = db_get_field("SELECT COUNT(answer_id) FROM ?:polls_answers WHERE item_id = ?i", $key);
            $poll['questions'][$key]['results'] = array(
                'count' => $count,
                'total' => $total_voted,
                'ratio' => $total_voted ? sprintf("%.2f", $count / $total_voted * 100) : '0.00',
                'votes' => $count,
                'votes_ratio' => $total_voted ? sprintf("%.2f", $count / $total_voted * 100) : '0.00'
            );
        } elseif ($entry['type'] == 'M') {
            $total = db_get_field("SELECT COUNT(DISTINCT(vote_id)) FROM ?:polls_answers WHERE item_id = ?i", $key);

            if ($total) {
                $votes = 0;
                $cur_ratio = 0;
                foreach ($entry['answers'] as $k => $rec) {

                    $count = db_get_field("SELECT COUNT(answer_id) FROM ?:polls_answers WHERE item_id = ?i AND answer_id = ?i", $key, $k);
                    $votes += $total;

                    $ratio = $count / $total * 100;
                    if ($cur_ratio < $ratio) {
                        $cur_ratio = $ratio;
                        $max_ratio_ids = array();
                        $max_ratio_ids[$cur_ratio] = array();
                        $max_ratio_ids[$cur_ratio][$k] = $k;
                    } elseif ($cur_ratio != 0 && $cur_ratio == $ratio) {
                        $max_ratio_ids[$cur_ratio][$k] = $k;
                    }

                    $poll['questions'][$key]['answers'][$k]['results'] = array(
                        'count' => $count,
                        'total' => $total,
                        'ratio' => $total ? sprintf("%.2f", $ratio) : '0.00'
                    );
                }

                if (isset($max_ratio_ids)) {
                    foreach ($max_ratio_ids[$cur_ratio] as $id) {
                        $poll['questions'][$key]['answers'][$id]['results']['max_ratio'] = "Y";
                    }
                }

                $poll['questions'][$key]['results'] = array (
                    'votes' => $votes,
                    'votes_ratio' => $total_voted ? sprintf("%.2f", $votes / $total_voted * 100) : '0.00'
                );
            }
        } elseif ($entry['type'] == 'Q') {
            $total = db_get_field("SELECT COUNT(answer_id) FROM ?:polls_answers WHERE item_id = ?i", $key);

            if ($total) {
                $num = 1;
                $sum = 0;
                $cur_ratio = 0;

                foreach ($entry['answers'] as $k => $rec) {
                    $count = db_get_field("SELECT COUNT(answer_id) FROM ?:polls_answers WHERE item_id = ?i AND answer_id = ?i", $key, $k);

                    $ratio = $count / $total * 100;
                    if ($cur_ratio < $ratio) {
                        $cur_ratio = $ratio;
                        $max_ratio_ids = array();
                        $max_ratio_ids[$cur_ratio] = array();
                        $max_ratio_ids[$cur_ratio][$k] = $k;
                    } elseif ($cur_ratio != 0 && $cur_ratio == $ratio) {
                        $max_ratio_ids[$cur_ratio][$k] = $k;
                    }

                    $poll['questions'][$key]['answers'][$k]['results'] = array(
                        'count' => $count,
                        'total' => $total,
                        'ratio' => $total ? sprintf("%.2f", $ratio) : '0.00'
                    );

                    if ($num == count($entry['answers'])) {
                        $poll['questions'][$key]['answers'][$k]['results']['ratio'] = $sum ? sprintf("%.2f", 100 - $sum) : '100.00';
                    } else {
                        $sum += $poll['questions'][$key]['answers'][$k]['results']['ratio'];
                        $num++;
                    }
                }

                if (isset($max_ratio_ids)) {
                    foreach ($max_ratio_ids[$cur_ratio] as $id) {
                        $poll['questions'][$key]['answers'][$id]['results']['max_ratio'] = "Y";
                    }
                }

                $poll['questions'][$key]['results'] = array (
                    'votes' => $total,
                    'votes_ratio' => $total_voted ? sprintf("%.2f", $total / $total_voted * 100) : '0.00'
                );
            }
        }
    }

    return true;
}

/**
 * Get poll comments
 *
 * @param array $params array of search parameters
 * @return array comments
 */
function fn_polls_get_comments($params, $items_per_page = 0)
{
    $default_values = array (
        'answer_id' => 0,
        'page' => 1,
        'items_per_page' => $items_per_page
    );

    if (empty($params['poll_page_id']) || empty($params['item_id'])) {
        return array();
    }

    $params = array_merge($default_values, $params);

    $limit = '';
    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(answer_id) FROM ?:polls_answers WHERE item_id = ?i AND answer_id = ?i AND comment != ''", $params['item_id'], $params['answer_id']);
        $limit = db_paginate($params['page'], $params['items_per_page']);
    }

    $comments = db_get_hash_array("SELECT ?:polls_answers.answer_id, ?:polls_answers.comment, ?:polls_votes.time, ?:polls_votes.vote_id FROM ?:polls_answers LEFT JOIN ?:polls_votes USING(vote_id) WHERE ?:polls_answers.item_id = ?i AND ?:polls_answers.answer_id = ?i AND ?:polls_answers.comment != '' ORDER BY answer_id DESC " . $limit, 'vote_id', $params['item_id'], $params['answer_id']);

    return array($comments, $params);
}

/**
 * Get poll votes
 *
 * @param array $params array of search parameters
 * @param int $items_per_page votes per page
 * @return boolean true if results exists, false otherwise
 */
function fn_polls_get_votes($params, $items_per_page = 0)
{
    $votes = array();

    $default_values = array (
        'item_id' => 0,
        'page' => 1,
        'completed' => 'N',
        'items_per_page' => $items_per_page
    );

    $fields = array(
        '?:polls_votes.vote_id',
        '?:polls_votes.type',
        '?:polls_votes.time',
        '?:polls_votes.ip_address',
        '?:polls_votes.user_id',
        '?:users.firstname',
        '?:users.lastname'
    );

    $params = array_merge($default_values, $params);

    if (empty($params['poll_page_id'])) {
        return array($votes, $params);
    }

    $condition = '1';
    $join = '';
    if ($params['completed'] == 'Y') {
        $condition .= db_quote(" AND ?:polls_votes.type = ?s", 'C');
    }

    if (!empty($params['anwer_id']) || !empty($params['item_id'])) {
        $join = db_quote(" LEFT JOIN ?:polls_answers ON ?:polls_answers.vote_id = ?:polls_votes.vote_id");
    }

    if (!empty($params['answer_id'])) {
        $condition .= db_quote(" AND ?:polls_answers.answer_id = ?i", $params['answer_id']);
    }

    if (!empty($params['item_id'])) {
        $condition .= db_quote(" AND ?:polls_answers.item_id = ?i", $params['item_id']);
    }

    if (!empty($params['poll_page_id'])) {
        $condition .= db_quote(" AND ?:polls_votes.page_id = ?i", $params['poll_page_id']);
    }

    $limit = '';
    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(DISTINCT(?:polls_votes.vote_id)) FROM ?:polls_votes ?p WHERE ?p", $join, $condition);
        $limit = db_paginate($params['page'], $params['items_per_page']);
    }

    $join .= " LEFT JOIN ?:users ON ?:users.user_id = ?:polls_votes.user_id";
    $votes = db_get_hash_array("SELECT " . implode(', ', $fields) . " FROM ?:polls_votes ?p WHERE ?p ORDER BY time DESC $limit", 'vote_id', $join, $condition);

    return array($votes, $params);
}

/**
 * Get polls
 *
 * @param array $params array of search parameters
 * @param string $lang_code language to get descriptions for
 * @return array array with found polls in first element and filtered parameters in second
 */
function fn_get_polls($params, $lang_code = CART_LANGUAGE)
{
    $auth = $_SESSION['auth'];

    $condition = '';

    if (!empty($params['item_ids'])) {
        $condition .= db_quote(" AND ?:pages.page_id IN (?n)", explode(',', $params['item_ids']));
    }

    if (AREA != 'A') {
        $condition .= " AND (" . fn_find_array_in_set($auth['usergroup_ids'], '?:pages.usergroup_ids', true) . ")";
    }

    $_data = db_get_array("SELECT ?:pages.page_id, ?:page_descriptions.page FROM ?:pages LEFT JOIN ?:page_descriptions ON ?:page_descriptions.page_id = ?:pages.page_id AND ?:page_descriptions.lang_code = ?s LEFT JOIN ?:polls ON ?:polls.page_id = ?:pages.page_id WHERE ?:pages.status = ?s AND ?:pages.page_type = ?s AND (?:pages.use_avail_period = ?s OR (?:pages.use_avail_period = ?s AND ?:pages.avail_from_timestamp <= ?i AND ?:pages.avail_till_timestamp >= ?i)) ?p ORDER BY position", $lang_code, 'A', PAGE_TYPE_POLL, 'N', 'Y', TIME, TIME, $condition);

    $polls = array();

    foreach ($_data as $k => $_poll) {
        $polls[$k] = fn_get_poll_data($_poll['page_id']);
        $polls[$k]['page'] = $_poll['page'];
    }

    return array ($polls, $params);
}

function fn_polls_page_object_by_type(&$types)
{
    $types[PAGE_TYPE_POLL] = array(
        'single' => 'poll',
        'name' => 'polls',
        'add_name' => 'add_poll',
        'edit_name' => 'editing_poll',
        'new_name' => 'new_poll',
    );
}

function fn_polls_remove_pages()
{
    $pages = db_get_fields("SELECT page_id FROM ?:pages WHERE page_type = ?s ", PAGE_TYPE_POLL);

    foreach ($pages as $page_id) {
        fn_delete_page($page_id, $recurse = true);
    }
}
