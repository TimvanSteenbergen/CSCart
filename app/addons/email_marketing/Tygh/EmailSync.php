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

namespace Tygh;

use Tygh\Registry;
use Tygh\Exceptions\EmailSyncException;

class EmailSync
{
    private $service;
    private $batch = array();
    private static $instance;

    public function __construct($service_name = '')
    {
        if (empty($service_name)) {
            $service_name = Registry::get('addons.email_marketing.em_service');
        }

        $class_prefix = 'Tygh\\Backend\\EmailSync\\';
        $class = $class_prefix . ucfirst($service_name);

        try {
            $this->service = new $class();
        } catch (EmailSyncException $e) {
            $class = $class_prefix . 'Dummy';
            $this->service = new $class();
        }
    }

    public function getLists()
    {
        return $this->service->getLists();
    }

    public function subscribe($data)
    {
        return $this->service->subscribe($data);
    }

    public function unsubscribe($email)
    {
        return $this->service->unsubscribe($email);
    }

    public function subscribeCallback($list_id)
    {
        return $this->service->subscribeCallback($list_id, $this->getUrl());
    }

    public function unsubscribeCallback($list_id)
    {
        return $this->service->unsubscribeCallback($list_id, $this->getUrl());
    }

    public function processWebHook($data)
    {
        $data = $this->service->processWebHook($data);

        if ($data['action'] == 'subscribe') {

            fn_em_update_subscriber($data, 0, false);

        } elseif ($data['action'] == 'unsubscribe') {

            $subscriber_id = db_get_field("SELECT subscriber_id FROM ?:em_subscribers WHERE email = ?s", $data['email']);
            if (!empty($subscriber_id)) {
                fn_em_delete_subscribers(array($subscriber_id), false);
            }

        } elseif ($data['action'] == 'update') {

            $subscriber_id = db_get_field("SELECT subscriber_id FROM ?:em_subscribers WHERE email = ?s", $data['old_email']);
            if (!empty($subscriber_id)) {
                fn_em_update_subscriber($data, $subscriber_id, false);
            }

        } elseif ($data['action'] == 'email_update') {

            $subscriber_id = db_get_field("SELECT subscriber_id FROM ?:em_subscribers WHERE email = ?s", $data['old_email']);
            if (!empty($subscriber_id)) {
                fn_em_update_subscriber(array(
                    'email' => $data['new_email']
                ), $subscriber_id, false);
            }

        }
    }

    public function batchAdd($data)
    {
        $this->batch[] = $data;
    }

    public function batchSubscribe()
    {
        $result = $this->service->batchSubscribe($this->batch);
        $this->batch = array();

        return $result;
    }

    public function batchUnsubscribe($emails)
    {
        return $this->service->batchUnsubscribe($emails);
    }

    public function manualSync()
    {
        return $this->service->manualSync();
    }

    public function sync()
    {
        $emails = $this->service->sync();
        fn_em_delete_subscribers_by_email($emails);
    }

    public function supports()
    {
        return $this->service->supports();
    }

    public function import()
    {
        $subscribers = $this->service->import();
        if (!empty($subscribers)) {
            foreach ($subscribers as $subscriber) {
                $subscriber['status'] = 'A';
                fn_em_update_subscriber($subscriber, 0, false);
            }
        }

        return true;
    }

    public static function instance($service_name = '')
    {
        if (empty(self::$instance[$service_name])) {
            self::$instance[$service_name] = new self($service_name);
        }

        return self::$instance[$service_name];
    }

    private function getUrl()
    {
        return fn_url('em_subscribers_webhook.process?token=' . Registry::get('addons.email_marketing.em_token'), 'A', 'http');
    }
}
