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

namespace Tygh\Backend\Database;

interface IBackend
{
    /**
     * Connects to database server
     * @param  string  $user     user name
     * @param  string  $passwd   password
     * @param  string  $host     server host name
     * @param  string  $database database name
     * @return boolean true on success, false - otherwise
     */
    public function connect($user, $passwd, $host, $database);

    /**
     * Disconnects from the database
     */
    public function disconnect();

    /**
     * Changes current database
     * @param  string  $database database name
     * @return boolean true on success, false - otherwise
     */
    public function changeDb($database);

    /**
     * Queries database
     * @param  string $query SQL query
     * @return query  result
     */
    public function query($query);

    /**
     * Fetches row from query result set
     * @param  mixed  $result result set
     * @param  string $type   fetch type - 'assoc' or 'indexed'
     * @return array  fetched data
     */
    public function fetchRow($result, $type = 'assoc');

    /**
     * Frees result set
     * @param mixed $result result set
     */
    public function freeResult($result);

    /**
     * Return number of rows affected by query
     * @param  mixed $result result set
     * @return int   number of rows
     */
    public function affectedRows($result);

    /**
     * Returns last value of auto increment column
     * @return int value
     */
    public function insertId();

    /**
     * Gets last error code
     * @return int error code
     */
    public function errorCode();

    /**
     * Gets last error description
     * @return string error description
     */
    public function error();

    /**
     * Escapes value
     * @param  mixed  $value value to escape
     * @return string escaped value
     */
    public function escape($value);

    /**
     * Executes Command after when connecting to MySQL server
     * @param string $command Command to execute
     */
    public function initCommand($command);
}
