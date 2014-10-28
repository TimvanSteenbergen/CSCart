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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ($mode == 'poll_submit') {
        if (empty($_REQUEST['page_id'])) {
            return array(CONTROLLER_STATUS_NO_PAGE);
        }

        $condition = " AND (" . fn_find_array_in_set($_SESSION['auth']['usergroup_ids'], '?:pages.usergroup_ids', true) . ")";

        $poll_data = db_get_row("SELECT * FROM ?:pages INNER JOIN ?:page_descriptions ON ?:pages.page_id = ?:page_descriptions.page_id WHERE ?:pages.page_id = ?i AND ?:page_descriptions.lang_code = ?s ?p", $_REQUEST['page_id'], CART_LANGUAGE, $condition);
        if (empty($poll_data) || $poll_data['status'] == 'D' || $poll_data['use_avail_period'] == 'Y' && ($poll_data['avail_from_timestamp'] > TIME || $poll_data['avail_till_timestamp'] < TIME)) {
            return array(CONTROLLER_STATUS_REDIRECT);
        }

        $ip = fn_get_ip();

        if (db_get_field('SELECT vote_id FROM ?:polls_votes WHERE page_id = ?i AND ip_address = ?s', $_REQUEST['page_id'], $ip['host'])) {
            return array(CONTROLLER_STATUS_REDIRECT);
        }

        $prefix = isset($_REQUEST['obj_prefix']) ? $_REQUEST['obj_prefix'] : '';

        if (fn_image_verification('use_for_polls', $_REQUEST) == false) {
            return array(CONTROLLER_STATUS_REDIRECT);
        }

        if (!empty($_REQUEST['answer'])) {
            $answer = $_REQUEST['answer'];
        } else {
            $answer = array ();
        }

        if (!empty($_REQUEST['answer_text'])) {
            $answer_text = $_REQUEST['answer_text'];
        } else {
            $answer_text = array ();
        }

        if (!empty($_REQUEST['answer_more'])) {
            $answer_more = $_REQUEST['answer_more'];
        } else {
            $answer_more = array ();
        }

        $poll = fn_get_poll_data($_REQUEST['page_id']);

        $error = false;

        foreach ($poll['questions'] as $key => $entry) {
            if ($entry['required'] == 'N') {
                continue;
            }

            if ($entry['type'] == 'T' && empty($answer_text[$key])) {
                $error = true;
                break;
            } elseif ($entry['type'] == 'M' && (!isset($answer[$key]) || !is_array($answer[$key]))) {
                $error = true;
                break;
            } elseif ($entry['type'] == 'Q' && empty($answer[$key])) {
                $error = true;
                break;
            }
        }

        if ($error) {
            fn_set_notification('E', __('error'), __('required_not_answered'));

            return array(CONTROLLER_STATUS_REDIRECT);
        }

        $data = array (
            'page_id' => $_REQUEST['page_id'],
            'ip_address' => $ip['host'],
            'user_id' => empty($auth['user_id']) ? 0 : $auth['user_id'],
            'time' => TIME,
            'type' => 'E'
        );

        $vote_id = db_query('INSERT INTO ?:polls_votes ?e', $data);

        if ($vote_id) {
            $filled = 0;

            foreach ($poll['questions'] as $key => $entry) {
                if ($entry['type'] == 'T') {
                    if (!empty($answer_text[$key])) {
                        fn_polls_insert_answer($vote_id, $key, 0, !empty($answer_text[$key]) ? $answer_text[$key] : '');
                        $filled++;
                    }

                } elseif ($entry['type'] == 'M' && isset($answer[$key]) && is_array($answer[$key])) {
                    foreach ($answer[$key] as $answer_id => $rec) {
                        if ($rec != 'Y') {
                            continue;
                        }

                        fn_polls_insert_answer($vote_id, $key, $answer_id, !empty($answer_more[$key][$answer_id]) ? $answer_more[$key][$answer_id] : '');
                    }
                    $filled++;

                } elseif ($entry['type'] == 'Q') {
                    if (!empty($answer[$key])) {
                        $answer_id = $answer[$key];
                        fn_polls_insert_answer($vote_id, $key, $answer_id, !empty($answer_more[$key][$answer_id]) ? $answer_more[$key][$answer_id] : '');
                        $filled++;
                    }
                }
            }

            if ($filled == count($poll['questions'])) {
                db_query("UPDATE ?:polls_votes SET type = 'C' WHERE vote_id = ?i", $vote_id);
            }

            if ($poll['show_results'] == 'N') {
                fn_set_notification('N', __('notice'), __('thanks_for_voting'));

                return array (CONTROLLER_STATUS_REDIRECT);
            }
        }

        return array (CONTROLLER_STATUS_OK);
    }
}

function fn_polls_insert_answer($vote_id, $item_id, $answer_id = 0, $comment = '')
{
    $data = array(
        'vote_id' => $vote_id,
        'item_id' => $item_id,
        'answer_id' => $answer_id,
        'comment' => $comment,
    );

    db_query('INSERT INTO ?:polls_answers ?e', $data);
}
