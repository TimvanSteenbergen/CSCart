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

abstract class ABackend
{
    /*
     * Service support flags:
     * manual_sync - if service unsubscriptions should be synced manually
     * import - if service supports subscribers import
     */
    protected $support = array(
        'manual_sync' => false,
        'import' => false
    );

    /**
     * Subscribes email
     * @param array $data subscriber data
     */
    public function subscribe($data)
    {
        return true;
    }

    /**
     * Unsubscribes email
     * @param string $email 
     */
    public function unsubscribe($email)
    {
        return true;
    }    

    /**
     * Adds callback url when user subscribes using service form (webhook)
     * @param mixed $list_id list ID
     * @param string $url callback url
     */
    public function subscribeCallback($list_id, $url)
    {
        return true;
    }    

    /**
     * Adds callback url when user unsubscribes using service form (webhook)
     * @param mixed $list_id list ID
     * @param string $url callback url
     */
    public function unsubscribeCallback($list_id, $url)
    {
        return true;
    }    

    /**
     * Processes webhook data
     * @param array $data webhook data
     */
    public function processWebHook($data)
    {
        return true;
    }    

    /**
     * Batch email subscription
     * @param array $data emails (with name, timestamp, etc)
     */
    public function batchSubscribe($data)
    {
        return true;
    }    

    /**
     * Batch email unsubscription
     * @param array $emails emails
     * @return type
     */
    public function batchUnsubscribe($emails)
    {
        return true;
    }    

    /**
     * Syncs unsubscribed users
     */
    public function sync()
    {
        return false;
    }    

    /**
     * Gets subscription lists
     * @return array lists
     */
    public function getLists()
    {
        return array();
    }    

    /**
     * Gets service options
     * @param string $option option name
     * @return mixed boolean true if option is supported, false - if not. Array with all options if $option parameter is ommited
     */
    public function supports($option = '')
    {
        if (!empty($option)) {
            return !empty($this->support[$option]);    
        }

        return $this->support;
    }
}
