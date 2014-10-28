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

class Pdo implements IBackend
{
    const PDO_MYSQL_ATTR_INIT_COMMAND = 1002;

    private $_conn;
    private $_reconnects = 0;
    private $_max_reconnects = 3;
    private $_last_result;
    private $_skip_error_codes = array (
        1091, // column exists/does not exist during alter table
        1176, // key does not exist during alter table
        1050, // table already exist
        1060  // column exists
    );

    private $_connection_params = array();

    /**
     * Connects to database server
     * @param  string  $user     user name
     * @param  string  $passwd   password
     * @param  string  $host     server host name
     * @param  string  $database database name
     * @return boolean true on success, false - otherwise
     */
    public function connect($user, $passwd, $host, $database)
    {
        $this->_connection_params = array(
            'user' => $user,
            'passwd' => $passwd,
            'host' => $host,
            'database' => $database,
        );

        if (!$host || !$user) {
            return false;
        }

        @list($host, $port) = explode(':', $host);

        try {
            $this->_conn = new \PDO("mysql:host=$host;dbname=$database", $user, $passwd);
            $this->_reconnects = 0;
        } catch (\PDOException $e) {
            return false;
        }

        return !empty($this->_conn);
    }

    /**
     * Disconnects from the database
     */
    public function disconnect()
    {
        return $this->_conn = null;
    }

    /**
     * Changes current database
     * @param  string  $database database name
     * @return boolean true on success, false - otherwise
     */
    public function changeDb($database)
    {
        if ($this->_conn->exec('USE ' . $database) !== false) {
            return true;
        } else {
            if (($this->errorCode() == 2013 || $this->errorCode() == 2006) && $this->_reconnects < $this->_max_reconnects) {
                $this->disconnect();
                $this->connect($this->_connection_params['user'], $this->_connection_params['passwd'], $this->_connection_params['host'], $this->_connection_params['database']);
                $this->_reconnects++;

                return $this->changeDb($database);
            }
        }

        return false;
    }

    /**
     * Queries database
     * @param  string $query SQL query
     * @return query  result
     */
    public function query($query)
    {
        $result = $this->_conn->query($query);
        $this->_last_result = $result;

        if (empty($result)) {
            // Lost connection, try to reconnect
            if (($this->errorCode() == 2013 || $this->errorCode() == 2006) && $this->_reconnects < $this->_max_reconnects) {
                $this->disconnect();
                $this->connect($this->_connection_params['user'], $this->_connection_params['passwd'], $this->_connection_params['host'], $this->_connection_params['database']);
                $this->_reconnects++;

                return $this->query($query);

            // Assume that the table is broken
            // Try to repair
            } elseif (preg_match("/'(\S+)\.(MYI|MYD)/", $this->errorCode(), $matches)) {
                $this->_conn->query("REPAIR TABLE $matches[1]");
                $result = $this->query($query);
            }
        }

        // need to return true for insert/replace/update/delete query
        if (!empty($result) && preg_match("/^(INSERT|REPLACE|UPDATE|DELETE)/", $result->queryString)) {
            return true;
        }

        return $result;
    }

    /**
     * Fetches row from query result set
     * @param  mixed  $result result set
     * @param  string $type   fetch type - 'assoc' or 'indexed'
     * @return array  fetched data
     */
    public function fetchRow($result, $type = 'assoc')
    {
        if ($type == 'assoc') {
            return $result->fetch(\PDO::FETCH_ASSOC);
        } else {
            return $result->fetch(\PDO::FETCH_NUM);
        }
    }

    /**
     * Frees result set
     * @param mixed $result result set
     */
    public function freeResult($result)
    {
        return $result->closeCursor();
    }

    /**
     * Return number of rows affected by query
     * @param  mixed $result result set
     * @return int   number of rows
     */
    public function affectedRows($result)
    {
        if (is_object($result)) {
            return $result->rowCount();
        } elseif (is_object($this->_last_result)) {
            return $this->_last_result->rowCount();
        }

        return 0;
    }

    /**
     * Returns last value of auto increment column
     * @return int value
     */
    public function insertId()
    {
        return $this->_conn->lastInsertId();
    }

    /**
     * Gets last error code
     * @return int error code
     */
    public function errorCode()
    {
        $err = $this->_conn->errorInfo();

        return in_array($err[1], $this->_skip_error_codes) ? 0 : $err[1];
    }

    /**
     * Gets last error description
     * @return string error description
     */
    public function error()
    {
        $err = $this->_conn->errorInfo();

        return $err[2];
    }

    /**
     * Escapes value
     * @param  mixed  $value value to escape
     * @return string escaped value
     */
    public function escape($value)
    {
        return substr($this->_conn->quote($value), 1, -1);
    }

    /**
     * Executes Command after when connecting to MySQL server
     * @param string $command Command to execute
     */
    public function initCommand($command)
    {
        if (!empty($command)) {
            $this->query($command);
            //$this->_conn->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, $command);
            // FIXME: Workaround: Fatal error: Undefined class constant 'MYSQL_ATTR_INIT_COMMAND'
            // https://bugs.php.net/bug.php?id=47224
            // http://stackoverflow.com/questions/2424343/undefined-class-constant-mysql-attr-init-command-with-pdo
            // You should have extra extension to make it work or use 1002 instead

            $this->_conn->setAttribute(self::PDO_MYSQL_ATTR_INIT_COMMAND, $command);
        }
    }

}
