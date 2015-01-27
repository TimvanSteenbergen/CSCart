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

namespace Twigmo\Core\Functions;

use Tygh\Http;
use Tygh\Registry;
use Twigmo\Core\TwigmoConnector;

class UserAgent {

    public static function processUa($ua)
    {
        $result = 'unknown';
        if (!file_exists(TWIGMO_UA_RULES_FILE)) {
            return $result;
        }
        $rules = unserialize(fn_get_contents(TWIGMO_UA_RULES_FILE));
        if (!is_array($rules)) {
            return $result;
        }
        $ua_meta = self::getUaMeta($ua, $rules);
        // Save stat
        foreach ($ua_meta as $section => $value) {
            $where = array('section' => $section, 'value' => $value, 'month' => date('Y-m-1'));
            $count = db_get_field('SELECT count FROM ?:twigmo_ua_stat WHERE ?w', $where);
            if ($count) {
                db_query('UPDATE ?:twigmo_ua_stat SET count=count+1 WHERE ?w', $where);
            } else {
                $where['count'] = 1;
                db_query('INSERT INTO ?:twigmo_ua_stat ?e', $where);
            }
        }
        if ($ua_meta['device'] and in_array($ua_meta['device'], array('phone', 'tablet'))) {
            $result = $ua_meta['device'];
        }

        return $result;
    }

    public static function sendUaStat()
    {
        $access_id = TwigmoConnector::getAccessID('A');
        if (!$access_id) {
            return;
        }
        $query = db_quote('FROM ?:twigmo_ua_stat WHERE month<?s LIMIT ?i', date('Y-m-1'), 100);
        $needToSend = db_get_array('SELECT *, ?s as access_id ' . $query, $access_id);
        if (!count($needToSend)) {
            return;
        }
        $responce = Http::post(TWG_UA_RULES_STAT, array('stat' => serialize($needToSend)));
        if ($responce == 'ok') {
            db_query('DELETE ' . $query);
        }
    }

    public static function updateUaRules()
    {
        $update_needed = false;
        if (!file_exists(TWIGMO_UA_RULES_FILE)) {
            $update_needed = true;
        } else {
            $rules_serialized = fn_get_contents(TWIGMO_UA_RULES_FILE);
            $md5_on_twigmo = Http::get(TWG_CHECK_UA_UPDATES);
            if (md5($rules_serialized) != $md5_on_twigmo) {
                $update_needed = true;
            }
        }
        if (!$update_needed) {
            return;
        }
        $rules_on_twigmo = Http::get(TWG_UA_RULES);
        fn_twg_write_to_file(TWIGMO_UA_RULES_FILE, $rules_on_twigmo, false);
    }

    private static function getUaMeta($ua, $ruleSections)
    {
        $results = array();
        $ua = strtolower($ua);
        foreach ($ruleSections as $section => $rules) {
            $results[$section] = self::checkUaRule($rules['rules'], $ua, $results);
        }
        return $results;
    }

    private static function checkUaRule($rules, $ua, $results)
    {
        $result = '';
        foreach ($rules as $rule) {
            $checked_value = isset($rule['check']) ? $results[$rule['check']] : $ua;
            if (preg_match($rule['expression'], $checked_value) xor isset($rule['is_filter'])) {
                if (isset($rule['result'])) {
                    $result = $rule['result'];
                }
                if (isset($rule['rules'])) {
                    $subrelsResult = self::checkUaRule($rule['rules'], $ua, $results);
                    if ($subrelsResult) {
                        $result = $subrelsResult;
                    }
                }
            }
            if ($result) {
                break;
            }
        }

        return $result;
    }
}
