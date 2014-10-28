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

namespace Tygh\Backend\EmailSync;

use Tygh\Registry;

class Madmimi extends ABackend
{
    private $mc;
    private $list_id = 0;

    protected $support = array(
        'manual_sync' => true,
        'import' => true
    );

    public function __construct()
    {
        Registry::get('class_loader')->addClassMap(array(
            'MadMimi' => Registry::get('config.dir.addons') . 'email_marketing/lib/madmimi/MadMimi.class.php',
            'Spyc' => Registry::get('config.dir.addons') . 'email_marketing/lib/madmimi/Spyc.class.php',
        ));

        $this->mm = new \MadMimi(Registry::get('addons.email_marketing.em_madmimi_username'), Registry::get('addons.email_marketing.em_madmimi_api_key'));
        $this->list_id = Registry::get('addons.email_marketing.em_madmimi_list');
    }

    public function subscribe($data)
    {
        $res = $this->mm->AddUser(array(
            'email' => $data['email'], 
            'firstName' => !empty($data['name']) ? $data['name'] : '', 
            'add_list' => $this->list_id
        ));

        return true;
    }

    public function unsubscribe($email)
    {
        $res = $this->mm->RemoveUser($email, $this->list_id);

        return true;
    }

    public function subscribeCallback($list_id, $url)
    {
        return false;
    }

    public function unsubscribeCallback($list_id, $url)
    {
        return false;
    }

    public function processWebHook($data)
    {
        return array();
    }

    public function batchSubscribe($data)
    {
        $res = array();
        foreach ($data as $user) {
            $res[] = array(
                'email' => $user['email'],
                'firstName' => $user['name'],
                'add_list' => $this->list_id
            );
        }
        $csv = $this->buildCSV(array('email', 'firstName', 'add_list'), $res);
        $this->mm->Import($csv);

        return true;
    }

    public function batchUnsubscribe($emails)
    {
        // MadMimi does not support batch unsubscription, so...
        foreach ($emails as $email) {
            $this->unsubscribe($email);
        }

        return true;
    }

    public function getLists()
    {
        $result = array();

        $lists = $this->mm->Lists();
        if (!empty($lists)) {
            $xml = @simplexml_load_string($lists);

            if (is_object($xml)) {
                foreach ($xml->list as $list) {
                    $result[(string)$list['name']] = (string)$list['name'];
                }
            }
        }

        return $result;
    }

    public function import()
    {
        $request = $this->mm->DoRequest('/audience_lists/' . $this->list_id . '/members.json', $this->mm->default_options());
        $subscribers = array();

        do {

            if (!empty($request) && $data = json_decode($request, true)) {
                foreach ($data['audience'] as $member) {
                    $subscribers[] = array(
                        'email' => $member['columns']['email'],
                        'name' => $member['columns']['first_name'],
                        'timestamp' => strtotime($member['columns']['created_at']['data'])
                    );
                }


                if ($data['page'] < $data['total_pages']) {
                    $options = array('page' => ++$data['page']) + $this->mm->default_options();
                    $request = $this->mm->DoRequest('/audience_lists/' . $this->list_id . '/members.json', $options);
                } else {
                    $request = '';
                }
            }
            
        } while (!empty($request));
    
        return $subscribers;
    }

    public function sync()
    {
        $emails = array();
        $unsubscribed = $this->mm->SuppressedSince(Registry::get('addons.email_marketing.em_lastsync'));

        if (!empty($unsubscribed)) {
            $list = fn_explode("\n", $unsubscribed);

            foreach ($list as $item) {
                if (trim($item)) {
                    $emails[] = trim(substr($item, strrpos($item, ' ')));
                }
            }
        }

        return $emails;
    }

    private function buildCSV($keys, $data)
    {
        $csv = '';
        foreach ($keys as $value) {
            $value = $this->mm->escape_for_csv($value);
            $csv .= $value . ',';
        }
        $csv = substr($csv, 0, -1);
        $csv .= "\n";
        foreach ($data as $arr) {
            foreach ($keys as $key) {
                $value = $this->mm->escape_for_csv($arr[$key]);
                $csv .= $value . ',';
            }
            $csv = substr($csv, 0, -1);
            $csv .= "\n";
        }
        
        return $csv;
    }
}
