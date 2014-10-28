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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ($mode == 'update_question') {
        fn_polls_update_question($_REQUEST['question_data'], $_REQUEST['item_id'], $_REQUEST['page_id']);

        $suffix = ".update?page_id=" . $_REQUEST['page_id'];

        return array(CONTROLLER_STATUS_OK, "pages$suffix");
    }

    return;
}

if ($mode == 'delete_question') {
    if (!empty($_REQUEST['item_id'])) {
        $p_id = db_get_field("SELECT parent_id FROM ?:poll_items WHERE item_id = ?i", $_REQUEST['item_id']);
        db_query("DELETE FROM ?:poll_items WHERE item_id = ?i", $_REQUEST['item_id']);
        db_query("DELETE FROM ?:poll_descriptions WHERE object_id = ?i AND type = 'I'", $_REQUEST['item_id']);
        db_query("DELETE FROM ?:polls_answers WHERE item_id = ?i", $_REQUEST['item_id']);

        $count = db_get_field("SELECT COUNT(*) FROM ?:poll_items WHERE parent_id = ?i", $p_id);
        if (empty($count)) {
            db_query("DELETE FROM ?:polls_votes WHERE page_id = ?i", $p_id);

            return array(CONTROLLER_STATUS_OK, "pages.update?page_id=" . $p_id);
        }
    }
    exit;

} elseif ($mode == 'delete_vote') {
    if (!empty($_REQUEST['vote_id'])) {
        $p_id = db_get_field("SELECT page_id FROM ?:polls_votes WHERE vote_id = ?i", $_REQUEST['vote_id']);
        db_query("DELETE FROM ?:polls_votes WHERE vote_id = ?i", $_REQUEST['vote_id']);
        db_query("DELETE FROM ?:polls_answers WHERE vote_id = ?i", $_REQUEST['vote_id']);

        return array(CONTROLLER_STATUS_OK, "pages.update?page_id=$p_id&selected_section=poll_statistics");
    }

    return;

} elseif ($mode == 'poll_reports') {

    if ($_REQUEST['report'] == 'votes') {

        list($votes, $search) = fn_polls_get_votes($_REQUEST, Registry::get('addons.polls.polls_votes_on_page'));

        Registry::get('view')->assign('votes', $votes);
        Registry::get('view')->assign('search', $search);
        Registry::get('view')->display('addons/polls/views/pages/components/votes.tpl');

    } elseif ($_REQUEST['report'] == 'answers') {

        list($comments, $search) = fn_polls_get_comments($_REQUEST, Registry::get('addons.polls.polls_comments_on_page'));
        Registry::get('view')->assign('comments', $comments);
        Registry::get('view')->assign('search', $search);
        Registry::get('view')->display('addons/polls/views/pages/components/comments.tpl');

    }

    exit;

} elseif ($mode == 'add') {

    if (!empty($_REQUEST['page_type']) && $_REQUEST['page_type'] == PAGE_TYPE_POLL) {

        Registry::set('navigation.tabs.poll', array (
            'title' => __('poll'),
            'js' => true
        ));
    }

} elseif ($mode == 'update') {
    $page_data = Registry::get('view')->getTemplateVars('page_data');

    if ($page_data['page_type'] == PAGE_TYPE_POLL) {

        Registry::set('navigation.tabs.poll', array (
            'title' => __('poll'),
            'js' => true
        ));

        Registry::set('navigation.tabs.poll_questions', array (
            'title' => __('questions'),
            'js' => true
        ));

        Registry::set('navigation.tabs.poll_statistics', array (
            'title' => __('poll_statistics'),
            'js' => true
        ));

        $questions = db_get_array("SELECT q.*, d.description FROM ?:poll_items as q LEFT JOIN ?:poll_descriptions as d ON d.object_id = q.item_id AND d.type = 'I' AND d.lang_code = ?s WHERE q.parent_id = ?i AND q.type IN ('Q', 'M', 'T')", DESCR_SL, $_REQUEST['page_id']);

        Registry::get('view')->assign('questions', $questions);
    }

} elseif ($mode == 'update_question') {
    $question_data = db_get_row("SELECT q.*, d.description FROM ?:poll_items as q LEFT JOIN ?:poll_descriptions as d ON d.object_id = q.item_id AND d.type = 'I' AND d.lang_code = ?s WHERE q.item_id = ?i", DESCR_SL, $_REQUEST['item_id']);

    $question_data['answers'] = db_get_array("SELECT q.*, d.description FROM ?:poll_items as q LEFT JOIN ?:poll_descriptions as d ON d.object_id = q.item_id AND d.type = 'I' AND d.lang_code = ?s WHERE q.parent_id = ?i ORDER BY q.position", DESCR_SL, $_REQUEST['item_id']);

    Registry::get('view')->assign('question_data', $question_data);

    $page_data = fn_get_page_data($question_data['page_id']);
    Registry::get('view')->assign('page_data', $page_data);

} elseif ($mode == 'picker' && !empty($_REQUEST['picker_for']) && $_REQUEST['picker_for'] == 'polls') {

    Registry::get('view')->assign('button_names', array (
        'but_close_text' => __('add_polls_and_close'),
        'but_text' => __('add_polls')
    ));
}

function fn_polls_update_question($data, $item_id, $page_id, $lang_code = DESCR_SL)
{
    // question type
    $question_type = $data['type'];

    // Update questions
    if (!empty($item_id)) {
        db_query("UPDATE ?:poll_items SET ?u WHERE item_id = ?i", $data, $item_id);

        unset($data['type']);
        db_query("UPDATE ?:poll_descriptions SET ?u WHERE object_id = ?i AND type = 'I' AND lang_code = ?s", $data, $item_id, $lang_code);
    } else {
        $data['parent_id'] = $page_id;
        $data['page_id'] = $page_id;

        $item_id = $data['object_id'] = db_query("REPLACE INTO ?:poll_items ?e", $data);
        $data['type'] = 'I';
        foreach (fn_get_translation_languages() as $data['lang_code'] => $_v) {
            db_query("REPLACE INTO ?:poll_descriptions ?e", $data);
        }
    }

    // Update/delete answers
    if (!empty($data['answers'])) {
        foreach ($data['answers'] as $k => $v) {
            db_query("UPDATE ?:poll_items SET ?u WHERE item_id = ?i", $v, $k);

            unset($v['type']);
            db_query("UPDATE ?:poll_descriptions SET ?u WHERE object_id = ?i AND type = 'I' AND lang_code = ?s", $v, $k, $lang_code);
        }

        // Delete obsolete items
        $d_ids = db_get_fields("SELECT item_id FROM ?:poll_items WHERE item_id NOT IN (?n) AND parent_id = ?i", array_keys($data['answers']), $item_id);
        if (!empty($d_ids)) {
            db_query("DELETE FROM ?:poll_items WHERE item_id IN (?n)", $d_ids);
            db_query("DELETE FROM ?:poll_descriptions WHERE object_id IN (?n) AND type = 'I'", $d_ids);
        }
    } else {
        $d_ids = db_get_fields("SELECT item_id FROM ?:poll_items WHERE parent_id = ?i", $item_id);
        db_query("DELETE FROM ?:poll_items WHERE parent_id  = ?i", $item_id);
        db_query("DELETE FROM ?:poll_descriptions WHERE object_id IN (?n) AND type = 'I'", $d_ids);
    }

    // Add answers
    if (!empty($data['new_answers']) && $question_type != 'T') {
        foreach ($data['new_answers'] as $v) {
            if (empty($v['description'])) {
                continue;
            }
            $v['page_id'] = $page_id;
            $v['parent_id'] = $item_id;
            $v['object_id'] = db_query("REPLACE INTO ?:poll_items ?e", $v);

            $v['type'] = 'I';
            foreach (fn_get_translation_languages() as $v['lang_code'] => $_v) {
                db_query("REPLACE INTO ?:poll_descriptions ?e", $v);
            }
        }
    }

    return $item_id;
}
