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
use Tygh\Exceptions\EmailSyncException;

class Mailchimp extends ABackend
{
    private $mc;
    private $list_id = 0;

    protected $support = array(
        'manual_sync' => false,
        'import' => true
    );

    public function __construct()
    {
        Registry::get('class_loader')->add('Mailchimp', Registry::get('config.dir.addons') . 'email_marketing/lib/mailchimp');

        if (Registry::get('addons.email_marketing.em_mailchimp_api_key')) {
            try {
                $this->mc = new \Mailchimp(Registry::get('addons.email_marketing.em_mailchimp_api_key'));
            } catch (\Mailchimp_Error $e) {
                fn_set_notification('E', __('error'), $e->getMessage());
            }

            $this->list_id = Registry::get('addons.email_marketing.em_mailchimp_list');
        }

        if (!$this->mc) {
            throw new EmailSyncException();
        }
    }

    public function subscribe($data)
    {
        list($email, $_data) = $this->formSubscriber($data);
        $result = false;
        try {
            $res = $this->mc->lists->subscribe($this->list_id, array(
                'email' => $email
            ), $_data, 'html', false, true);

            $result = true;

        } catch (\Mailchimp_Error $e) {
            fn_set_notification('E', __('error'), $e->getMessage());
        }

        return $result;
    }

    public function unsubscribe($email)
    {
        $result = false;
        try {
            $res = $this->mc->lists->unsubscribe($this->list_id, array(
                'email' => $email
            ), true, false);

            $result = true;

        } catch (\Mailchimp_Error $e) {
            fn_set_notification('E', __('error'), $e->getMessage());
        }

        return $result;
    }

    public function subscribeCallback($list_id, $url)
    {
        $result = false;
        try {
            $this->mc->lists->webhookAdd($list_id, $url);
            $result = true;

        } catch (\Mailchimp_Error $e) {
            fn_set_notification('E', __('error'), $e->getMessage() . '<br/>' . $url);
        }

        return $result;
    }

    public function unsubscribeCallback($list_id, $url)
    {
        try {
            $this->mc->lists->webhookDel($list_id, $url);
        } catch (\Mailchimp_Error $e) {
            
        }

        return true;
    }

    public function processWebHook($data)
    {
        $result = array();

        if (!empty($data) && !empty($data['type'])) {
            if ($data['type'] == 'subscribe' || $data['type'] == 'profile') {
                $result = array(
                    'action' => $data['type'] == 'subscribe' ? 'subscribe' : 'update',
                    'email' => $data['data']['email'],
                    'name' => $data['data']['merges']['FNAME'],
                    'ip_address' => $data['data']['ip_opt'],
                    'timestamp' => time()
                );
            } elseif ($data['type'] == 'unsubscribe') {
                $result = array(
                    'action' => 'unsubscribe',
                    'email' => $data['data']['email']
                );
            } elseif ($data['type'] == 'upemail') {
                $result = array(
                    'action' => 'email_update',
                    'email' => $data['data']['old_email'],
                    'new_email' => $data['data']['new_email']
                );
            } elseif ($data['type'] == 'cleaned') {
                $result = array(
                    'action' => 'unsubscribe',
                    'email' => $data['data']['email']
                );
            }
        }

        return $result;
    }

    public function batchSubscribe($data)
    {
        $result = false;
        $batch = array();
        foreach ($data as $subscriber) {
            list($email, $merge_vars) = $this->formSubscriber($subscriber);
            $batch[] = array(
                'email' => array('email' => $email),
                'email_type' => 'html',
                'merge_vars' => $merge_vars
            );
        }

        try {
            $res = $this->mc->lists->batchSubscribe($this->list_id, $batch, false, true);
            $result = true;
        } catch (\Mailchimp_Error $e) {
            fn_set_notification('E', __('error'), $e->getMessage());
        }

        return $result;
    }

    public function batchUnsubscribe($emails)
    {
        $result = false;
        $batch = array();
        foreach ($emails as $email) {
            $batch[] = array(
                'email' => $email
            );
        }
        
        try {
            $res = $this->mc->lists->batchUnsubscribe($this->list_id, $batch, true, false);
            $result = true;
            
        } catch (\Mailchimp_Error $e) {
            fn_set_notification('E', __('error'), $e->getMessage());
        }

        return $result;
    }

    public function getLists()
    {
        $result = array();

        if ($this->mc) {
            try {
                $lists = $this->mc->lists->getList();
            } catch (\Mailchimp_Error $e) {
                fn_set_notification('E', __('error'), $e->getMessage());
            }
        }

        if (!empty($lists) && !empty($lists['data'])) {
            foreach ($lists['data'] as $list) {
                $result[$list['id']] = $list['name'];
            } 
        }

        return $result;
    }

    public function import()
    {
        try {
            $data = $this->mc->lists->members($this->list_id);
        } catch (\Mailchimp_Error $e) {
            fn_set_notification('E', __('error'), $e->getMessage());
        }

        $subscribers = array();

        do {
            if (!empty($data)) {
                foreach ($data['data'] as $member) {
                    $subscribers[] = array(
                        'email' => $member['email'],
                        'name' => $member['merges']['FNAME'],
                        'timestamp' => strtotime($member['timestamp_opt'])
                    );
                }

                if (sizeof($subscribers) < $data['total']) {
                    $data = $this->mc->lists->members($this->list_id, 'subscribed', array(
                        'start' => floor($data['total'] / sizeof($subscribers))
                    ));
                } else {
                    $data = array();
                }
            }
            
        } while (!empty($data));

        return $subscribers;
    }

    private function formSubscriber($data)
    {
        return array(
            $data['email'], 
            array(
                'FNAME' => !empty($data['name']) ? $data['name'] : null,
                'optin_ip' => $data['ip_address'],
                'optin_time' => strftime('%Y-%m-%d %H:%M:%S', $data['timestamp']),
                'mc_language' => $data['lang_code']
            )
        );
    }
}
