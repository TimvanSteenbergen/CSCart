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

use Tygh\Exceptions\ClassNotFoundException;

class Cloudfront extends ABackend
{
    /**
     * @var \AmazonCF
     */
    private $_cf;

    /**
     * Creates distribution
     * @param  string $host    host name for origin pull requests
     * @param  array  $options connection/authentication options
     * @return mixed  array with Id, host, CNAME and status when success, boolean false otherwise
     */
    public function createDistribution($host, $options = array())
    {
        $params = array(
            'Enabled' => true,
            'OriginProtocolPolicy' => 'http-only'
        );

        if (!empty($options['cname'])) {
            $params['CNAME'] = $options['cname'];
        }

        $res = $this->_cf($options)->create_distribution('http://' . $host, 'TYGHCDN-' . $host . '-' . time(), $params);

        if ($res->isOk()) {

            $cname = !empty($res->body->DistributionConfig->CNAME) ? (string) $res->body->DistributionConfig->CNAME : '';

            return array(
                'host' => (string) $res->body->DomainName,
                'id' => (string) $res->body->Id,
                'cname' => $cname,
                'is_active' => $this->_isActive($res)
            );
        } else {
            fn_set_notification('E', __('error'), (string) $res->body->Error->Message);
        }

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
        if ($this->getOption('key') != $options['key'] || $this->getOption('secret') != $options['secret']) {
            $this->deleteDistribution();

            return $this->createDistribution($host, $options);
        }

        $updated = $this->updateConfig(array(
            'CNAME' => $options['cname']
        ));

        if ($updated) {
            return array(
                'cname' => $options['cname']
            );
        }

        return false;
    }

    /**
     * Deletes distribution
     * @return boolean true on success, false otherwise
     */
    public function deleteDistribution()
    {
        return $this->updateConfig(array(
            'Enabled' => false
        ));
    }

    /**
     * Checks if CDN active
     * @return boolean true if active, false - otherwise
     */
    public function isActive()
    {
        $res = $this->_cf()->get_distribution_info($this->getOption('id'));
        if ($res->isOk()) {
            return $this->_isActive($res);
        }

        return false;
    }

    /**
     * Updates distribution config
     * @param  array   $data data to update
     * @return boolean true
     */
    private function updateConfig($data)
    {
        $existing_xml = $this->_cf()->get_distribution_config($this->getOption('id'));

        if ($existing_xml->isOK()) {

            $updated_xml = $this->_cf()->update_config_xml($existing_xml, $data);

            $etag = $this->_cf()->get_distribution_config($this->getOption('id'))->header['etag'];

            $response = $this->_cf()->set_distribution_config($this->getOption('id'), $updated_xml, $etag);
        }

        return true;
    }

    /**
     * Gets status from response and checks if distribution is deployed
     * @param  type $res
     * @return type
     */
    private function _isActive($res)
    {
        return (string) $res->body->Status == \AmazonCloudFront::STATE_DEPLOYED;
    }

    /**
     * Gets CloudFront object
     *
     * @param  array     $options connection options
     * @return \AmazonCF CloudFront object
     */
    private function _cf($options = array())
    {
        if (empty($this->_cf) || !empty($options)) {

            // This is workaround to composer autoloader
            if (!class_exists('CFLoader')) {
                throw new ClassNotFoundException('CloudFront: autoload failed');
            }

            $key = !empty($options['key']) ? $options['key'] : $this->getOption('key');
            $secret = !empty($options['secret']) ? $options['secret'] : $this->getOption('secret');

            \CFCredentials::set(array(
                '@default' => array(
                    'key' => $key,
                    'secret' => $secret
                )
            ));

            $this->_cf = new \AmazonCloudFront();
            $this->_cf->use_ssl = false;
        }

        return $this->_cf;
    }
}
