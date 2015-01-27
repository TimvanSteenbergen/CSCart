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

namespace Tygh\Backend\Session;
use Tygh\Debugger;

use Tygh\Session;

class Redis extends ABackend
{
    private $r;

    /**
     * Init backend
     *
     * @param array $config global configuration params
     * @param array $params additional params passed from Session class
     *
     * @return bool true if backend was init correctly, false otherwise
     */
    public function __construct($config, $params = array())
    {
        parent::__construct($config, $params);

        $this->r = new \Redis();
        $this->config = fn_array_merge(array(
            'redis_server' => $config['session_redis_server'],
            'saas_uid' => !empty($config['saas_uid']) ? $config['saas_uid'] : null,
        ), $this->config);

        Debugger::checkpoint('Session: before redis connect');
        if ($this->r->connect($this->config['redis_server']) == true) {
            $this->r->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
            Debugger::checkpoint('Session: after redis connect');

            return true;
        }

        return false;
    }

    /**
     * Read session data
     *
     * @param string $sess_id session ID
     *
     * @return mixed session data if exist, false otherwise
     */
    public function read($sess_id)
    {
        $session = $this->r->hGetAll($this->_id($sess_id));

        if (empty($session) || $session['expiry'] < TIME) {

            if (!empty($session)) {
                // the session did not have time to get in "stored_sessions" and got out of date, it is necessary to return only settings
                $this->delete($sess_id);
                $session = Session::decode($session['data']);

                return Session::encode(array ('settings' => !empty($session['settings']) ? $session['settings'] : array()));
            }

            $stored_data = db_get_field('SELECT data FROM ?:stored_sessions WHERE session_id = ?s', $sess_id);

            if (!empty($stored_data)) {

                db_query('DELETE FROM ?:stored_sessions WHERE session_id = ?s', $sess_id);

                $current = array();
                $_stored = Session::decode($stored_data);
                $_current['settings'] = !empty($_stored['settings']) ? $_stored['settings'] : array();

                return Session::encode($_current);
            }

        } else {
            return $session['data'];
        }

        return false;
    }

    /**
     * Write session data
     *
     * @param string $sess_id session ID
     * @param array  $data    session data
     *
     * @return boolean always true
     */
    public function write($sess_id, $data)
    {
        $this->r->hmSet($this->_id($sess_id), $data);
        $this->r->setTimeout($this->_id($sess_id), $this->config['ttl'] + SECONDS_IN_HOUR); // increase alive time to allow garbage collector move session to stored sessions storage

        $this->r->set($this->_id($sess_id, 'online:'), 1);
        $this->r->setTimeout($this->_id($sess_id, 'online:'), $this->config['ttl_online']);

        return true;
    }

    /**
     * Update session ID
     *
     * @param string $old_id old session ID
     * @param array  $new_id new session ID
     *
     * @return boolean always true
     */
    public function regenerate($old_id, $new_id)
    {
        $this->r->rename($this->_id($old_id), $this->_id($new_id));
        $this->r->rename($this->_id($old_id, 'online:'), $this->_id($new_id, 'online:'));
        db_query('UPDATE ?:stored_sessions SET session_id = ?s WHERE session_id = ?s', $new_id, $old_id);

        return true;
    }

    /**
     * Delete session data
     *
     * @param string $sess_id session ID
     *
     * @return boolean always true
     */
    public function delete($sess_id)
    {
        $this->r->del($this->_id($sess_id));

        return true;
    }

    /**
     * Garbage collector (do nothing as redis takes care about deletion of expired keys)
     *
     * @param int $max_lifetime session lifetime
     *
     * @return boolean always true
     */
    public function gc($max_lifetime)
    {
        // Move expired sessions to sessions storage
        $session_ids = array_map(function($key) {
            return substr($key, strrpos($key, ':') + 1);
        }, $this->r->keys($this->_id('*')));

        if (!empty($session_ids)) {
            foreach ($session_ids as $sess_id) {
                $session = $this->r->hGetAll($this->_id($sess_id));
                if ($session['expiry'] < TIME) {
                    db_query('REPLACE INTO ?:stored_sessions ?e', array(
                        'session_id' => $sess_id,
                        'data' => $session['data'],
                        'expiry' => $session['expiry']
                    ));
                    fn_log_user_logout($session, Session::decode($session['data']));
                    $this->delete($sess_id);
                }
            }
        }

        // Cleanup sessions storage
        db_query('DELETE FROM ?:stored_sessions WHERE expiry < ?i', TIME - $this->config['ttl_storage']);

        return true;
    }

    /**
     * Gets sessions that were used less than number of seconds, defined in SESSION_ONLINE constant
     * @param  string $area session area
     * @return array  list of session IDs
     */
    public function getOnline($area)
    {
        $keys = $this->r->keys($this->_id('*_' . $area, 'online:'));

        return array_map(function($key) {
            return substr($key, strrpos($key, ':') + 1);
        }, $keys);
    }

    /**
     * Generate prefix for session id to separate sessions with same ID but from different stores
     *
     * @param string $sess_id session ID
     * @param string $prefix  key prefix
     *
     * @return string prefixed session ID
     */
    private function _id($sess_id, $prefix = '')
    {
        return $prefix . 'session:' . (!empty($this->config['saas_uid']) ? $this->config['saas_uid'] . ':' : '') . $sess_id;
    }
}
