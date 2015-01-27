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

namespace Tygh\Backend\Cdn;

use Tygh\Registry;
use Tygh\Settings;

abstract class ABackend
{
    protected $_options = array();

    /**
     * Constructor
     * @param array $options options
     */
    public function __construct($options)
    {
        $this->_options = $options;
    }

    /**
     * Gets option
     * @param  string $key option ket
     * @return mixed  option value
     */
    public function getOption($key)
    {
        return isset($this->_options[$key]) ? $this->_options[$key] : null;
    }

    /**
     * Gets options list
     * @return array options list
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Creates distribution
     * @param  string $host    host name for origin pull requests
     * @param  array  $options connection/authentication options
     * @return mixed  array with Id, host, CNAME and status when success, boolean false otherwise
     */
    public function createDistribution($host, $options)
    {
        return false;
    }

    /**
     * Updates distribution config
     * @param  string $host    host name for origin pull requests
     * @param  array  $options connection/authentication options
     * @return mixed  array with Id, host, CNAME and status when new distribution is created or array with cname field is updated, boolean false on error
     */
    public function updateDistribution($host, $options)
    {
        return false;
    }

    /**
     * Deletes distribution
     * @return boolean true on success, false otherwise
     */
    public function deleteDistribution()
    {
        return false;
    }

    /**
     * Checks if CDN active
     * @return boolean true if active, false - otherwise
     */
    public function isActive()
    {
        return false;
    }

    /**
     * Gets CDN host name
     * @return string CDN host name
     */
    public function getHost()
    {
        $host = $this->getOption('cname') ? $this->getOption('cname') : $this->getOption('host');

        return !empty($host) ? $host . Registry::get('config.http_path') : '';
    }

    /**
     * Saves CDN options
     * @param array   $data      CDN options
     * @param boolean $overwrite if set to true, $data overwrites current options
     */
    public function save($data, $overwrite = false)
    {
        if ($overwrite == true) {
            $options = $data;
        } else {
            $options = $this->getOptions();
            $options = fn_array_merge($options, $data);
        }

        Settings::instance()->updateValue('cdn', serialize($options));

        $this->_options = $options;
    }

    /**
     * Disables CDN
     */
    public function disable()
    {
        $options = $this->getOptions();
        unset($options['id'], $options['host'], $options['is_active']);

        $this->save($options, true);
    }

}
