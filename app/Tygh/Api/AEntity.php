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

namespace Tygh\Api;

abstract class AEntity
{
    /**
     * User auth data
     *
     * @var array
     */
    protected $auth = array();

    /**
     * Current area
     *
     * @var array
     */
    protected $area = null;

    /**
     * Parent entity data
     *
     * @var array
     */
    protected $parent = array();

    /**
     * Parent entity name
     *
     * @var string
     */
    protected $parent_name = array();

    /**
     * Handles REST GET request. Must return Api_Response with list of entities
     * or one entity data if id specified
     *
     * @param  mixed    $id
     * @param  array    $params
     * @return Response
     */
    abstract public function index($id = '', $params = array());

    /**
     * Handles REST POST request. Must create resource and return Api_Response
     * with STATUS_CREATED on success.
     *
     * @param  array    $params POST data
     * @return Response
     */
    abstract public function create($params);

    /**
     * Handles REST PUT request. Must update resource and return Api_Response
     * with STATUS_OK on success.
     *
     * @param  int      $id
     * @param  array    $params POST data
     * @return Response
     */
    abstract public function update($id, $params);

    /**
     * Handles REST DELETE request. Must create resource and return Api_Response
     * with STATUS_NO_CONTENT on success.
     *
     * @param  int      $id
     * @return Response
     */
    abstract public function delete($id);

    /**
     * Generic construct
     *
     * @param  array   $auth User auth data @see fn_fill_auth
     * @return AEntity object
     */
    public function __construct($auth = array(), $area = '')
    {
        $this->auth = $auth;
        $this->area = $area;
    }

    /**
     * Returns true if authenticated user have permissions to use this method
     *
     * @param  string $method_name
     * @param  string $area
     * @return bool
     */
    public function isAccessable($method_name)
    {
        if ($this->area == 'C') {
            $privileges = $this->privilegesCustomer();
        } else {
            $privileges = $this->privileges();
        }

        $is_accessable = false;
        if (isset($privileges[$method_name])) {
            if (is_bool($privileges[$method_name])) {
                $is_accessable = $privileges[$method_name];
            } else {
                if ($this->auth) {
                    $is_accessable = fn_check_user_access($this->auth['user_id'], $privileges[$method_name]);
                }
            }
        }

        return $is_accessable;
    }

    /**
     * Returns list of privileges wat can be enabled for user
     *
     * @return array List of entyties
     */
    public function privileges()
    {
        return array();
    }

    /**
     * Returns list of customer privileges wat can be enabled for user
     *
     * @return array List of entyties
     */
    public function privilegesCustomer()
    {
        return array();
    }

    /**
     * Returns true if identifier valid
     *
     * @param  int  $id Identifier entity
     * @return bool True on success, false otherwise
     */
    public function isValidIdentifier($id)
    {
        return is_numeric($id);
    }

    /**
     * Returns true if entity is parent of $entity_name
     *
     * @param  string $entity_name Entity name
     * @return bool   True on ssuccess, false otherwise
     */
    public function isParentOf($entity_name)
    {
        $entities = $this->childEntities();

        return in_array($entity_name, $entities);
    }

    /**
     * Returns list of child entities
     *
     * @return array List of entities
     */
    public function childEntities()
    {
        return array();
    }

    /**
     * Gets value by key from array
     *
     * @param  array  $array   Array to search value
     * @param  string $key     Array ey name
     * @param  mixed  $default Default value will be returned if $array[$key] is not set
     * @return mixed  $array[$key] if it isset, false $default
     */
    protected function safeGet($array, $key, $default)
    {
        return isset($array[$key]) ? $array[$key] : $default;
    }

    /**
     * Sets parent data
     *
     * @param array $data
     */
    public function setParentData($data)
    {
        $this->parent = $data;
    }

    /**
     * Sets parent name
     *
     * @param array $name
     */
    public function setParentName($name)
    {
        $this->parent_name = $name;
    }

    /**
     * Returns parent data
     *
     * @return array
     */
    public function getParentData()
    {
        return $this->parent;
    }

    /**
     * Returns parent name
     *
     * @return array
     */
    public function getParentName()
    {
        return $this->parent_name;
    }

}
