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

require_once 'rsa/rsa.php';

abstract class AbstractTwigmoConnector
{
    const KEY_LEN = 1024;
    const SIGNATURE_NAME = 'signature';

    const STATUS_OK = 'ok';
    const STATUS_ERROR = 'error';


    protected $_my_private_key;
    protected $_my_public_key;
    protected $_his_public_key;
    protected $_url;
    protected $_is_crypted = true;
    private $_rsa;

    public function __construct($settings)
    {
        if (!empty($settings['my_public_key'])) {
            $this->setMyPublicKey($settings['my_public_key']);
            $this->setMyPrivateKey($settings['my_private_key']);
        }
        if (!empty($settings['his_public_key'])) {
            $this->setHisPublicKey($settings['his_public_key']);
        }
        if (!empty($settings['url'])) {
            $this->setUrl($settings['url']);
        }
        $this->_rsa = new Crypt_RSA();
    }


    public static function createKeys($length = self::KEY_LEN)
    {
        $rsa = new Crypt_RSA();
        return $rsa->createKey($length); // array('publickey' => '...', 'privatekey' => '...')
    }


    public function send($action, $data, $meta = array(), $method = 'POST')
    {
        if (!empty($this->_default_meta)) {
            $meta = array_merge($this->_default_meta, $meta);
        }
        $sended_data = array(
            'data' => $this->prepareData($data, $meta),
            'action' => $action,
            'access_id' => empty($meta['access_id']) ? '' : $meta['access_id']
        );
        $response = $this->_httpRequest($this->_url, $sended_data, $method);

        if (!empty($response)) {
            $response = $this->parseResponse($response);
        }
        return  $response;
    }


    // Parse response
    public function parseResponse($response)
    {
        $bad_response_result = array(
            'meta' => array(
                'status' => self::STATUS_ERROR,
                'response' => $response
            )
        );

        if (!is_string($response)) {
            // Something goes wrong and we just returning response
            return $bad_response_result;
        }

        // Assume that this is a coded base64 string
        $data = base64_decode($response);

        // Decode response
        if ($this->_is_crypted) {
            $this->_rsa->loadKey($this->_my_private_key);
            $data = @$this->_rsa->decrypt($data);
        }

        if (!is_string($data)) {
            return $bad_response_result;
        }

        $data = unserialize($data);

        if (!isset($data['meta'])) {
            return $bad_response_result;
        }

        // We got the message without a signature
        if (!empty($this->_his_public_key) && empty($data['meta'][self::SIGNATURE_NAME])) {
            return $bad_response_result;
        }

        // Check signature
        if (!empty($this->_his_public_key)) {
            $this->_rsa->loadKey($this->_his_public_key);
            if (!$this->_rsa->verify($data['data'], $data['meta'][self::SIGNATURE_NAME])) {
                return $bad_response_result;
            }
        }

        $data['data'] = unserialize($data['data']);
        if (empty($data['meta']['status'])) {
            $data['meta']['status'] = self::STATUS_OK;
        }

        if (!empty($data['meta']['notifications']) && method_exists($this, '_setNotifications')) {
            $this->_setNotifications($data['meta']['notifications']);
        }

        return $data;
    }


    // Prepare data for sending or for response
    public function prepareData($data, $meta = array())
    {
        $data = serialize($data);
        $rsa = $this->_rsa;
        $rsa->loadKey($this->_my_private_key);
        $meta[self::SIGNATURE_NAME] = $rsa->sign($data);
        $result = array('data' => $data, 'meta' => $meta);
        $result = serialize($result);
        if ($this->_his_public_key && $this->_is_crypted) {
            $rsa->loadKey($this->_his_public_key);
            $result = $rsa->encrypt($result);
        }
        return base64_encode($result);
    }


    public static function responseIsOk($response)
    {
        if (!is_array($response) or !isset($response['meta']['status']) or $response['meta']['status'] != self::STATUS_OK) {
            return false;
        }
        return true;
    }


    public function respond($data = array(), $meta = array())
    {
        if (empty($meta['notifications'])) {
            $meta['notifications'] = array();
        }
        $meta['notifications'] = array_merge($meta['notifications'], fn_get_notifications());
        echo $this->prepareData($data, $meta);
        die();
    }



    public function onError($message = '', $title_lang_var = 'error', $type = 'E', $add_support_link = true)
    {
        if ($message) {
            if ($add_support_link) {
                $message .= ' ' . $this->_getLangVar('twgsvc.contact_support');
            }
            fn_set_notification($type, $this->_getLangVar($title_lang_var), $message);
        }
        $this->respond(array(), array('status' => 'error'));
    }


    public function setMyPrivateKey($key)
    {
        $this->_my_private_key = $key;
    }


    public function setMyPublicKey($key)
    {
        $this->_my_public_key = $key;
    }


    public function getMyPublicKey()
    {
        return $this->_my_public_key;
    }


    public function getHisPublicKey()
    {
        return $this->_his_public_key;
    }


    public function setHisPublicKey($key)
    {
        $this->_his_public_key = $key;
    }


    public function setIsCrypted($is_crypted)
    {
        $this->_is_crypted = $is_crypted;
    }


    public function setUrl($url)
    {
        $this->_url = $url;
    }


    /**
     * Build path
     * args - directories and the last - file (if needed)
     * example1: getPath('dir1', 'dir2', 'file.txt')  => 'dir1/dir2/file.txt'
     * example2: getPath('dir1', 'dir2')              => 'dir1/dir2'
     */
    public static function getPath()
    {
        return implode(func_get_args(), '/');
    }


    public static function getAccessIdPath($access_id)
    {
        $first_dir_len = 2;
        return self::getPath(substr($access_id, 0, $first_dir_len), substr($access_id, $first_dir_len));
    }
}