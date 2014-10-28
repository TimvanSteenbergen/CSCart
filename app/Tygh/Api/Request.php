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

use Tygh\Api;

class Request
{
    /**
     * Current resource name
     * @var string $resource
     */
    protected $resource = '';

    /**
     * Current request method
     *
     * @var string $method
     */
    protected $method = '';

    /**
     * Current request data
     *
     * @var array $data
     */
    protected $data = array();

    /**
     * Current request headers
     *
     * @var string $headers
     */
    protected $headers = array();

    protected $content_type = '';

    protected $accept_type = '';

    /**
     * uth data (user => 'user name', api_key => 'API KEY')
     *
     * @var string $_auth
     */
    protected $auth = array();

    protected $error = array();

    public function getResource()
    {
        return $this->resource;
    }

    public function getAuthData()
    {
        return $this->auth;
    }

    public function getHeaders()
    {
        if ($this->headers['Accept'] == '*/*') {
            $this->headers['Accept'] = Api::DEFAULT_RESPONSE_FORMAT;
        }

        return $this->headers;
    }

    public function getContentType()
    {
        return $this->content_type;
    }

    public function getAcceptType()
    {
        return $this->accept_type;
    }

    public function getData()
    {
        if (!$this->data) {
            $this->data = $this->getDataFromRequestBody();
        }

        // Unset REST resource name param
        if (isset($this->data[Api::REST_RESOURCE_PARAM_NAME])) {
            unset($this->data[Api::REST_RESOURCE_PARAM_NAME]);
        }

        return $this->data;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getError()
    {
        return $this->error;
    }

    /**
     * Creates API instance
     *
     * @param string $resource Resource name, if empty will be filled from current HTTP request
     * @param string $method   Request method, if empty will be filled from current HTTP request
     * @param array  $headers  Array of headers, if empty will be filled from current HTTP request
     * @param array  $data     Request data, if empty will be filled from current HTTP request
     * @param array  $auth     Auth data (user => 'user name', api_key => 'API KEY')
     */
    public function __construct($resource = "", $method = "",  $headers = array(), $data = array(), $auth = array())
    {
        if (empty($resource)) {
            $this->resource = $this->getResourceNameFromRequest();
        } else {
            $this->resource = $resource;
        }

        if (empty($method)) {
            $this->method = $this->getMethodFromRequestHeaders();
        } else {
            $this->method = $method;
        }

        if (empty($headers)) {
            $this->headers = $this->getHeadersFromRequestHeaders();
        } else {
            $this->headers = $headers;
        }

        if ($this->headers) {
            $this->content_type = $this->getContentTypeFromHeader($this->headers['Content-Type']);
            $this->accept_type = $this->getAcceptTypeFromHeader($this->headers['Accept']);
        }

        if ($data) {
            $this->data = $data;
        }

        if (empty($auth)) {
            $this->auth = $this->getAuthFromRequest();
        } else {
            $this->auth = $auth;
        }
    }

    /**
     * Gets resource name from current http request
     *
     * @return string Resource name
     */
    protected function getResourceNameFromRequest()
    {
        return !empty($_REQUEST[Api::REST_RESOURCE_PARAM_NAME]) ? $_REQUEST[Api::REST_RESOURCE_PARAM_NAME] : '';
    }

    /**
     * Gets equest method name (GET|POST|PUT|DELETE) from current http request
     *
     * @return string Request method name
     */
    protected function getMethodFromRequestHeaders()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Gets content type from current http request
     *
     * @return string Content type
     */
    protected function getHeadersFromRequestHeaders()
    {
        return array(
            'Content-Type' => !empty($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : Api::DEFAULT_REQUEST_FORMAT,
            'Accept'  => !empty($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : Api::DEFAULT_RESPONSE_FORMAT,
        );

    }

    protected function getContentTypeFromHeader($header_content_type)
    {
        $content_type = '';

        if (!empty($header_content_type)) {
            if ($pos_semicolon = strpos($header_content_type, ';')) {
                $content_type = substr($header_content_type, 0, $pos_semicolon);
            } else {
                $content_type = $header_content_type;
            }
        }

        return $content_type;
    }

    protected function getAcceptTypeFromHeader($header_accept)
    {
        $accept_type = '';

        if (!empty($header_accept)) {
            $accept_type = $this->getAvailableContentType(array_keys($this->parseHeaderAccept($header_accept)));
        }

        return $accept_type;
    }

    /**
     * Get the first matching one from the list of the client-requested data types
     *
     * @param array - Data types, sorted by priority
     * @return string Available data type
     */
    protected function getAvailableContentType($mime_types)
    {
        foreach ($mime_types as $type) {
            if (FormatManager::instance()->isMimeTypeSupported($type)) {
                return $type;
            }
            if ($type == '*/*') {
                return Api::DEFAULT_RESPONSE_FORMAT;
            }
        }

        return '';
    }

    /**
     * Splits header Accept line into a data type array
     *
     * @param  string $header Header to parse
     * @return array  Data type array, sorted by priority
     */
    protected function parseHeaderAccept($header)
    {
        if (!$header) {
            return array();
        }

        $types = array();
        $groups = array();
        foreach (explode(',', $header) as $type) {
            // get data type priority
            if (preg_match('/;\s*(q=.*$)/', $type, $match)) {
                $q    = substr(trim($match[1]), 2);
                $type = trim(substr($type, 0, -strlen($match[0])));
            } else {
                $q = 1;
            }

            $groups[$q][] = $type;
        }

        krsort($groups);

        foreach ($groups as $q => $items) {
            $q = (float) $q;

            if (0 < $q) {
                foreach ($items as $type) {
                    $types[trim($type)] = $q;
                }
            }
        }

        return $types;
    }

    /**
     * Gets request data from current http request
     *
     * @return string Content type
     */
    protected function getDataFromRequestBody()
    {
        $params = array();

        $method = $this->getMethodFromRequestHeaders();
        $content_type = $this->getContentType();

        if ($method == "PUT" || $method == "DELETE" || $method == "POST") {
            $params = file_get_contents('php://input');

            if (!empty($content_type)) {
                list($params, $this->error) = FormatManager::instance()->decode($params, $content_type);
            }
        } elseif ($method == "GET") {
            $params = $_GET;
        }

        return $params;
    }

    /**
     * Gets content type from current http request
     *
     * @return string Content type
     */
    protected function getAuthFromRequest()
    {
        $auth = array();

        if (!empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW'])) {
            $auth['user'] = $_SERVER['PHP_AUTH_USER'];
            $auth['api_key'] = $_SERVER['PHP_AUTH_PW'];
        }

        return $auth;
    }

}
