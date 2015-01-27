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

abstract class ABackend
{
    protected $config;

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
        $this->config = $params;

        return true;
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
        return false;
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
        return false;
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
        return false;
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
        return false;
    }
}
