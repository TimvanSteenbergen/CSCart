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

use Tygh\Api\Request;
use Tygh\Api\Response;
use Tygh\Api\FormatManager;
use Tygh\Api\AEntity;
use Tygh\Registry;

class Api
{
    const CURRENT_VERSION = 2.0;

    /**
     * Key of resource name in _REQUEST
     *
     * @const REST_PATH_PARAM_NAME
     */
    const DEFAULT_REQUEST_FORMAT = 'text/plain';

    /**
     * Key of resource name in _REQUEST
     *
     * @const REST_PATH_PARAM_NAME
     */
    const DEFAULT_RESPONSE_FORMAT = 'application/json';

    /**
     * Key of resource name in _REQUEST
     *
     * @const REST_PATH_PARAM_NAME
     */
    const REST_RESOURCE_PARAM_NAME = '_d';

    /**
     * Length of API keys
     *
     * @const API_KEY_LENGTH
     */
    const API_KEY_LENGTH = 32;

    /**
     * Auth data
     * (user => 'user name', api_key => 'API KEY')
     *
     * @var array $auth
     */
    protected $auth = array();

    /**
     * Current area
     *
     * @var array $area
     */
    protected $area = null;

    /**
     * Current request data
     *
     * @var Request
     */
    protected $request = null;

    /**
     * @var array
     */
    protected $user_data = array();

    protected $called_version = '1.0';

    protected $fake_entities = array(
        'version' => array(
            'index' => 'getVersion',
        ),
    );

    /**
     * Creates API instance
     *
     * @param  array $formats
     * @return Api
     */
    public function __construct($formats = array('json', 'text'))
    {
        FormatManager::initiate($formats);
        $this->request = new Request();

        if (!$this->protocolValidator()) {
            $response = new Response(Response::STATUS_FORBIDDEN, 'The API is only accessible over HTTPS');
            $response->send();
        }

        $this->defineArea();

        if ($this->area == 'C' && !Registry::get('config.tweaks.api_allow_customer')) {
            $response = new Response(Response::STATUS_UNAUTHORIZED);
            $response->send();
        }
    }

    /**
     * Handles request.
     * Method gets request from entities and send it
     *
     * @param null|Request $request Request object if empty will be created and filled from current HTTP request automatically
     */
    public function handleRequest($request = null)
    {
        if ($request instanceof Request) {
            $this->request = $request;
        }

        $authorized = $this->authenticate();

        /**
         * Rewrite default API behavior
         *
         * @param object $this       Api instance
         * @param bool   $authorized Authorization flag
         */
        fn_set_hook('api_handle_request', $this, $authorized);

        if (!$authorized && $this->area == 'A') {
            $response = new Response(Response::STATUS_UNAUTHORIZED);
        } else {

            $content_type = $this->request->getContentType();
            $accept_type = $this->request->getAcceptType();
            $method = $this->request->getMethod();

            if (($method == "PUT" || $method == "POST") && !FormatManager::instance()->isMimeTypeSupported($content_type)) {
                $response = new Response(Response::STATUS_UNSUPPORTED_MEDIA_TYPE);
            } elseif (($method == "GET" || $method == "HEAD") && !FormatManager::instance()->isMimeTypeSupported($accept_type)) {
                $response = new Response(Response::STATUS_METHOD_NOT_ACCEPTABLE);
            } elseif ($this->request->getError()) {
                $response = new Response(Response::STATUS_BAD_REQUEST, $this->request->getError(), $accept_type);
            } else {
                $controller_result = $this->getResponse($this->request->getResource());

                if (is_a($controller_result, '\\Tygh\\Api\\Response')) {
                    $response = $controller_result;
                } else {
                    $response = new Response(Response::STATUS_INTERNAL_SERVER_ERROR);
                }
            }
        }

        $response->send();
    }

    public function protocolValidator()
    {
        if (!defined('HTTPS') && Registry::get('config.tweaks.api_https_only')) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get user data by request user and api key.
     *
     * @return array
     */
    public function getUserData()
    {
        fn_set_hook('api_get_user_data_pre', $this, $this->user_data);

        if (!$this->user_data) {
            $authData = $this->request->getAuthData();
            if ($authData) {
                $this->user_data = fn_get_api_user($authData['user'], $authData['api_key']);
                if (!$this->user_data) {
                    $response = new Response(Response::STATUS_UNAUTHORIZED);
                    $response->send();
                }
            }
        }

        return $this->user_data;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Define current area depends on user type.
     */
    protected function defineArea()
    {
        $user_data = $this->getUserData();

        $area = 'C';
        if ($user_data) {
            if ($user_data['user_type'] == 'A') {
                $area = 'A';
                fn_define('ACCOUNT_TYPE', 'admin');
            } elseif ($user_data['user_type'] == 'V') {
                $area = 'A';
                fn_define('ACCOUNT_TYPE', 'vendor');
            }
        }

        $this->area = $area;
        fn_define('AREA', $area);
    }

    /**
     * Tries to authenticate user
     *
     * @return bool True on success, false otherwise
     */
    protected function authenticate()
    {
        $user_data = $this->getUserData();

        $this->auth = fn_fill_auth($user_data);

        // Return value must be bool
        return !empty($this->auth['user_id']);
    }

    /**
     * Return response
     *
     * @param  string   $resource REST resource name (products/1, users, etc.)
     * @return Response Response
     */
    protected function getResponse($resource)
    {
        $response = null;

        if ($resource) {
            $entity_properties = $this->getEntityFromPath($resource);
            $response = $this->getResponseFromEntity($entity_properties);
        }

        return ($response != null) ? $response : new Response(Response::STATUS_NOT_FOUND);
    }

    /**
     * Creates entity object of resource, runs it method and return response
     *
     * @param  string   $entity_properties Properties of entity
     * @param  string   $parent_name       Parent entity name
     * @param  array    $parent_data       Parent entity data
     * @return Response Response or null
     */
    protected function getResponseFromEntity($entity_properties, $parent_name = null, $parent_data = null)
    {
        $response = null;

        $entity = $this->getObjectByEntity($entity_properties);

        /**
         * Fake entity can't have parent
         */
        if ($entity !== null || (isset($this->fake_entities[$entity_properties['name']]) && !$parent_data)) {

            if (!empty($parent_data['data'])) {
                $entity->setParentName($parent_name);
                $entity->setParentData($parent_data['data']);
            }

            if (!empty($entity_properties['id']) && !$entity->isValidIdentifier($entity_properties['id'])) {
                $response = null;

            } elseif (!empty($entity_properties['child_entity'])) {

                $parent_result = array('status' => Response::STATUS_FORBIDDEN);

                if ($this->checkAccess($entity, 'index')) {
                    $parent_result = $entity->index($entity_properties['id']);
                }

                if (Response::isSuccessStatus($parent_result['status'])) {
                    $name = $entity_properties['name'];
                    $entity_properties = $this->getEntityFromPath($entity_properties['child_entity']);

                    $response = $this->getResponseFromEntity($entity_properties, $name, $parent_result);
                } else {
                    $response = new Response($parent_result['status']);
                }
            } else {
                $response = $this->exec($entity, $entity_properties);
            }
        } else {
            $response = new Response(Response::STATUS_NOT_FOUND, __('object_not_found', array('[object]' => __('entity') . ' ' . $entity_properties['name'])), $this->request->getAcceptType());
        }

        return $response;
    }

    /**
    * Executes entity method
    *
    * @param  \Tygh\Api\AEntity   $entity            Entity object
    * @param  array    $entity_properties Properties of entity
    * @return Response Response
    */
    protected function exec($entity, $entity_properties)
    {
        $response = null;

        $accept_type = $this->request->getAcceptType();
        $http_method = $this->request->getMethod();
        $method_name = $this->getMethodName($http_method);

        $request_data = $this->request->getData();

        if ($this->request->getError()) {
            $response = new Response(Response::STATUS_BAD_REQUEST, $this->request->getError(), $accept_type);
        } elseif (!$method_name) {
            $response = new Response(Response::STATUS_METHOD_NOT_ALLOWED);
        } elseif (isset($this->fake_entities[$entity_properties['name']])) {
            $fake_entity = $this->fake_entities[$entity_properties['name']];
            if (is_array($fake_entity) && !empty($fake_entity[$method_name]) && method_exists($this, $fake_entity[$method_name])) {
                $result = $this->$fake_entity[$method_name]();
                $response = new Response($result['status'], $result['data']);
            } elseif (is_string($fake_entity) && method_exists($this, $fake_entity)) {
                $result = $this->$fake_entity();
                $response = new Response($result['status'], $result['data']);
            } else {
                $response = new Response(Response::STATUS_FORBIDDEN);
            }
        } elseif (!$this->checkAccess($entity, $method_name)) {
            $response = new Response(Response::STATUS_FORBIDDEN);
        } else {
            $reflection_method = new \ReflectionMethod($entity, $method_name);
            $accepted_params = $reflection_method->getParameters();
            $call_params = array();

            if (fn_allowed_for('ULTIMATE')) {
                if ($http_method == 'POST' || $http_method == 'PUT') {
                    fn_ult_parse_api_request($entity_properties['name'], $request_data);
                }
            }

            foreach ($accepted_params as $param) {
                $param_name = $param->getName();

                if ($param_name == 'id') {
                    $call_params[] = !empty($entity_properties['id']) ? $entity_properties['id'] : '';

                    if (empty($entity_properties['id']) && !$param->isOptional()) {
                        $response = new Response(Response::STATUS_METHOD_NOT_ALLOWED, __('api_need_id'), $accept_type);
                    }
                }

                if ($param_name == 'params') {
                    $call_params[] = $request_data;

                    if (empty($request_data) && !$param->isOptional()) {
                        $response = new Response(Response::STATUS_METHOD_NOT_ALLOWED, __('api_need_params'), $accept_type);
                    }
                }
            }

            if ($http_method != 'POST' || empty($entity_properties['id'])) {
                if ($response == null) {
                    $controller_result = $reflection_method->invokeArgs($entity, $call_params);

                    if (!empty($controller_result['status'])) {
                        $data = isset($controller_result['data']) ? $controller_result['data'] : array();
                        $response = new Response($controller_result['status'], $data, $accept_type);

                    } else {
                        $response = new Response(Response::STATUS_INTERNAL_SERVER_ERROR);
                    }
                }
            } else {
                $response = new Response(Response::STATUS_METHOD_NOT_ALLOWED, __('api_not_need_id'), $accept_type);
            }
        }

        return $response;
    }

    /**
     * Checks that current authetificated user can access to $entity and it $method_name
     *
     * @param  \Tygh\Api\AEntity $entity      Entity instance
     * @param  string            $method_name Entity method name
     * @return bool              True on success, false otherwise
     */
    protected function checkAccess($entity, $method_name)
    {
        $can_access = false;

        if ($entity instanceof AEntity && method_exists($entity, $method_name)) {
            $can_access = $entity->isAccessable($method_name);
        }

        return $can_access;
    }

    /**
     * Returns entity method name by request method name
     *
     * @param  string $http_method_name (GET|POST|PUT|DELETE)
     * @return string method name
     */
    public function getMethodName($http_method_name)
    {
        $method = '';

        if ($http_method_name == 'GET') {
            $method = 'index';
        } elseif ($http_method_name == 'POST') {
            $method = 'create';
        } elseif ($http_method_name == 'PUT') {
            $method = 'update';
        } elseif ($http_method_name == 'DELETE') {
            $method = 'delete';
        }

        return $method;
    }

    /**
     * Converts list of ReflectionParameter objects to array with params name
     *
     * @param  array $reflection_params List of ReflectionParameter obejcts
     * @return array List of params names
     */
    protected function reflectionParamsToArray($reflection_params)
    {
        $params = array();

        foreach ($reflection_params as $param) {
            $params[] = $param->getName();
        }

        return $params;
    }

    /**
     * Explodes entity properties from resource name
     *
     * @param  string $resource_name REST resource name
     * @return array  Entity properties data
     */
    public function getEntityFromPath($resource_name)
    {
        $result = array(
            "name" => "",
            "id" => "",
        );

        if (!preg_match("/\/{2,}/", $resource_name)) {
            $resource_name = preg_replace("/\/$/", "", $resource_name);
            $resource_name = explode("/", $resource_name);

            if (!empty($resource_name[0]) && is_numeric($resource_name[0])) {
                $this->called_version = array_shift($resource_name);
            }

            if (!empty($resource_name[0])) {
                $result['name'] = array_shift($resource_name);

                if (!empty($resource_name[0])) {
                    $result['id'] = array_shift($resource_name);
                }

                if (!empty($resource_name[0])) {
                    //$result['child_entity'] = $this->getEntityFromPath(implode("/", $resource_name));
                    $result['child_entity'] = implode("/", $resource_name);

                }
            }
        }

        return $result;
    }

    /**
     * Returns instance of Entity class by entity properties
     *
     * @param  array             $entity_properties Entity properties data @see Api::getEntityFromPath
     * @return \Tygh\Api\AEntity
     */
    protected function getObjectByEntity($entity_properties)
    {
        $version = ($this->called_version == self::CURRENT_VERSION ? '' : 'v' . str_replace('.', '', $this->called_version) . '\\');
        $class_name = "\\Tygh\\Api\\Entities\\" . $version . fn_camelize($entity_properties['name']);

        $entity = class_exists($class_name) ? new $class_name($this->auth, $this->area) : null;

        if (!$entity) {
            $class_name = "\\Tygh\\Api\\Entities\\" . fn_camelize($entity_properties['name']);
            if (class_exists($class_name)) {
                $entity = new $class_name($this->auth, $this->area);
            }
        }

        return $entity;
    }

    /**
     * Generates new API key
     *
     * @return string API key
     */
    public static function generateKey()
    {
        $length = Api::API_KEY_LENGTH;
        $key = "";

        for ($i = 1; $i <= $length; $i++) {
            $chr = rand(0, 1) ? (chr(rand(65, 90))) : (chr(rand(48, 57)));

            if (rand(0, 1)) {
                $chr = strtolower($chr);
            }

            $key .= $chr;
        }

        return $key;
    }

    /**
     * Set version of API
     *
     * @param string $version version of api
     */
    public function setVersion($version)
    {
        $this->called_version = $version;
    }

    /**
     * Get version of api
     *
     * @return string API version
     */
    protected function getVersion()
    {
        return array(
            'status' => Response::STATUS_OK,
            'data' => array('Version' => $this->called_version),
        );
    }
}
