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

namespace Twigmo\Core;

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

use Twigmo\Api\ApiData;

class Api
{
    /*
     * Get objects(users, orders etc.) list in accepted for api format
     *
     * @param string $object_type object type (for example 'users', 'orders')
     * $conditions associative array with the keys as fields
     * @param  array $fields list of fields included to each object, if empty - all fields are included
     * @return array objects list
     */
    public static function getApiSchemaData(
        $object_type,
        $conditions = array(),
        $fields = array(),
        $options = array(),
        $sortings = array()
    ) {
        $pattern = fn_get_schema('api', $object_type, 'php', false);

        // if pattern not found or data can not be fetched up from the db table
        if (empty($pattern) || empty($pattern['table'])) {
            return false;
        }

        $order_by = '';
        $table_fields = array();
        $joins = array();
        $schema_fields = array();
        $db_fields = array();
        $process_fields = array();
        $group_by = '';

        $options = self::getApiOptions($options);

        // Add retrieve conditions
        $_cond = array();

        if (!empty($conditions)) {
            $_cond[] = self::getCondition($conditions, $pattern['table']);
        }
        if (!empty($pattern['filter'])) {
            $_cond[] = self::getCondition(
                $pattern['filter'],
                $pattern['table']
            );
        }

        $condition = implode(' AND ', $_cond);

        if (!empty($pattern['sortings']) && !empty($sortings['sort_by'])) {
            $sort_key = $sortings['sort_by'];
            $sort_order =
                !empty($sortings['sort_order']) ?
                    $sortings['sort_order'] :
                    'asc';

            if (!empty($pattern['sortings'][$sort_key])) {
                $sort_by = $pattern['sortings'][$sort_key];
                $order_by = ' ORDER BY '
                . (is_array($sort_by) ?
                    implode(' ' . $sort_order . ', ', $sort_by) :
                    $sort_by
                )
                . ' ' . $sort_order;
            }
        }

        if (!empty($pattern['group_by'])) {
            $group_by = ' GROUP BY ' . $pattern['group_by'] . ' ';
        }

        if (!empty($pattern['references'])) {
            foreach ($pattern['references'] as $table => $data) {
                $ref = array();
                foreach ($data['fields'] as $k => $v) {
                    if (!empty($v['db_field'])) {
                        $_val = self::getFieldDbName($v, $pattern);
                    } elseif (!empty($v['param'])) {
                        $_val = "'" . $options[$v['param']] . "'";
                    } elseif (!empty($v['value'])) {
                        $_val = "'". $v['value']. "'";
                    } else {
                        continue;
                    }

                    $ref[] = "$table.$k = $_val"; // fixme
                }
                $joins[] =
                    $data['join_type']
                    . ' JOIN ?:'
                    . $table
                    . " as $table ON "
                    . implode(' AND ', $ref);
            }
        }

        foreach ($pattern['fields'] as $field_id => $field_info) {

            if (!self::isInSchema($field_id, $pattern, $fields)) {
                continue;
            }

            if (!empty($field_info['db_field'])) {
                $field = self::getFieldDbName($field_info, $pattern);
                $table_fields[] = $field;
                $db_fields[$field_id] = $field_info['db_field'];
                continue;
            }
            $condition_fields = array();

            if (!empty($field_info['process_get'])) {
                $process_get = $field_info['process_get'];
                $process_fields[$field_id] = $process_get;
                if (!empty($process_get['params'])) {
                    $condition_fields = $process_get['params'];
                }
            } elseif (!empty($field_info['schema'])) {
                $schema_info = $field_info['schema'];
                $schema_fields[$field_id] = $schema_info;
                if (!empty($schema_info['filter'])) {
                    $condition_fields = $schema_info['filter'];
                }
            }

            if (!empty($condition_fields)) {
                foreach ($condition_fields as $cond_info) {
                    if (!empty($cond_info['db_field'])) {
                        $field = self::getFieldDbName($cond_info, $pattern);
                        $table_fields[] = $field;
                    }
                }
            }
        }

        $query = 'SELECT '
        . implode(
            ', ',
            $table_fields
        )
        . ' FROM ?:'
        . $pattern['table']
        . ' as '
        . $pattern['table']
        .' '
        . implode(' ', $joins)
        . (!empty($condition) ? ' WHERE ' . $condition : '')
        . $group_by
        . $order_by;

        $step = 30; // define number of rows to get from database
        $iterator = 0; // start retrieving from
        $results = array();

        while ($data = db_get_array($query . " LIMIT $iterator, $step")) {
            $iterator += $step;
            foreach ($data as $k => $v) {
                $result = array();//fn_array_key_intersect($v, $pattern['fields']);

                // get schema fields
                foreach ($schema_fields as $api_field => $schema_info) {
                    $args = array();

                    if (!empty($schema_info['filter'])) {
                        $args = self::getSchemaParams($schema_info['filter'], $v);
                    }

                    if (!empty($args)) {
                        $sub_schema_data  = self::getApiSchemaData(
                            $schema_info['type'],
                            $args,
                            array(),
                            $options
                        );
                        if (!empty($sub_schema_data)) {
                            $result[$api_field] = $sub_schema_data;
                        }
                    }
                }

                foreach ($db_fields as $api_field => $db_field) {
                    $result[$api_field] = $v[$db_field];
                }

                // get process fields
                foreach ($process_fields as $api_field => $process_info) {
                    $args = array();

                    if (!empty($process_info['params'])) {
                        $args = self::getSchemaParams(
                            $process_info['params'],
                            $v,
                            $result,
                            $options
                        );
                    }

                    $field_value = call_user_func_array($process_info['func'], $args);
                    if ($field_value !== false) {
                        $result[$api_field] = $field_value;
                    }
                }

                if (!empty($pattern['hash_fields'])) {
                    $result['hash'] = self::getApiHash(
                        $result,
                        $pattern['hash_fields']
                    );
                }

                $results[] = $result;
            }
        }
        if (empty($results)) {
            return array();
        }

        return array($pattern['object_name'] => $results);
    }

    public static function getApiOptions($options)
    {
        $def_options = array (
            'lang_code' => CART_LANGUAGE,
        );

        return array_merge($def_options, $options);
    }

    public static function isInSchema($field_id, $schema, $fields)
    {
        return empty($fields)
        || in_array($field_id, $fields)
        || (!empty($schema['key']) && in_array($field_id, $schema['key']));
    }

    public static function getSchemaParams(
        $params,
        $db_data,
        $api_data = array(),
        $options = array()
    ) {
        $args = array();

        if (is_array($params)) {
            foreach ($params as $k => $v) {
                if (!empty($v['db_field']) && ($v['db_field'] == '*')) {
                    $args[$k] = $db_data;
                } elseif (!empty($v['db_field']) && isset($db_data[$v['db_field']])) {
                    $args[$k] = $db_data[$v['db_field']];
                } elseif (!empty($v['api_field']) && isset($api_data[$v['api_field']])) {
                    $args[$k] = $api_data[$v['api_field']];
                } elseif (!empty($v['value'])) {
                    $args[$k] = $v['value'];
                } elseif (!empty($v['param']) && isset($options[$v['param']])) {
                    $args[$k] = $options[$v['param']];
                } else {
                    $args[$k] = '';
                }
            }
        }

        return $args;
    }

    public static function getCondition($conditions, $table)
    {
        $_cond = array();
        if (!empty($conditions)) {
            foreach ($conditions as $field => $value) {
                if (is_array($value)) {
                    $_val = implode("','", $value);
                } else {
                    $_val = $value;
                }
                $_cond[] = $table . ".$field IN ('$_val')";
            }
        }

        return implode(' AND ', $_cond);
    }

    public static function getFieldDbName($field_info, $pattern)
    {
        if (!empty($field_info['query_field'])) {
            return $field_info['query_field'];
        }

        $table = !empty($field_info['table']) ? $field_info['table'] : $pattern['table'];
        $field =
            !empty($field_info['table_field']) ?
                $field_info['table_field'] . ' as ' . $field_info['db_field'] :
                $field_info['db_field'];

        return $table . '.' . $field;
    }

    public static function getAsApiObject(
        $object_type,
        $object,
        $fields = array(),
        $options = array()
    ) {
        $schema = fn_get_schema('api', $object_type, 'php', false);

        return self::convertToSchema($schema, $object, $fields, $options);
    }

    public static function convertToSchema(
        $schema,
        $object,
        $fields,
        $options = array()
    ) {
        $api_data = array();
        $options = self::getApiOptions($options);
        foreach ($schema['fields'] as $field_id => $field_info) {

            if (!self::isInSchema($field_id, $schema, $fields)) {
                continue;
            }

            if (!empty($field_info['db_field']) && isset($object[$field_info['db_field']])) {
                $api_data[$field_id] = $object[$field_info['db_field']];

            } elseif (!empty($field_info['process_get'])) {

                 if (is_callable($field_info['process_get']['func'], true)) {
                    $args = array();

                    if (!empty($field_info['process_get']['params'])) {
                        $args = self::getSchemaParams(
                            $field_info['process_get']['params'],
                            $object,
                            $api_data,
                            $options
                        );
                    }
                    $api_data[$field_id] = call_user_func_array(
                        $field_info['process_get']['func'],
                        $args
                    );
                }
            } elseif (!empty($field_info['schema'])) {
                $sub_object_type = $field_info['schema']['type'];
                $sub_object_name =
                    !empty($field_info['schema']['name'])
                    ? $field_info['schema']['name']
                    : $sub_object_type;

                if (!empty($object[$sub_object_name])) {
                    if (!empty($field_info['schema']['is_single'])) {
                        $sub_schema_data  = self::getAsApiObject(
                            $sub_object_type,
                            $object[$sub_object_name]
                        );
                    } else {
                        $sub_schema_data  = self::getAsList(
                            $sub_object_type,
                            ApiData::getObjects($object[$sub_object_name])
                        );
                    }
                    if (!empty($sub_schema_data)) {
                        $api_data[$field_id] = $sub_schema_data;
                    }
                }

            } elseif (!empty($field_info['name']) && isset($object[$field_info['name']])) {
                $api_data[$field_id] = $object[$field_info['name']];
            }
        }

        return $api_data;
    }

    public static function getAsList(
        $object_type,
        $objects,
        $fields = array()
    ) {
        if (empty($objects)) {
            return array();
        }

        $schema = fn_get_schema('api', $object_type, 'php', false);
        if (empty($schema)) {
            return array();
        }

        $api_objects = array();

        foreach ($objects as $object) {
            $api_objects[] = self::convertToSchema(
                $schema,
                $object,
                $fields
            );
        }

        return array($schema['object_name'] => $api_objects);
    }

    public static function parseApiObject($object, $object_type)
    {
        $schema = fn_get_schema('api', $object_type, 'php', false);

        if (empty($schema)) {
            return false;
        }

        return self::convertFromSchema($schema, $object);
    }

    public static function convertFromSchema($schema, $api_data)
    {
        $object = array();
        $is_complete = true;
        foreach ($schema['fields'] as $field => $field_info) {
            if (isset($api_data[$field])) {
                if (!empty($field_info['db_field'])) {
                    $object[$field_info['db_field']] = $api_data[$field];
                } elseif (!empty($field_info['process_put'])) {
                    $process_info = $field_info['process_put'];
                    $field_id =
                        !empty($process_info['name']) ? $process_info['name'] : $field;

                    if (!empty($process_info['func'])) {
                        $args = array();
                        if (!empty($process_info['params'])) {
                            foreach ($process_info['params'] as $param) {
                                $args = self::getSchemaParams(
                                    $process_info['params'],
                                    array(),
                                    $api_data
                                );
                            }
                        }

                        if (!empty($process_info['is_extract'])) {
                            $object = array_merge(
                                $object,
                                call_user_func_array($process_info['func'], $args)
                            );
                        } else {
                            $object[$field_id] = call_user_func_array(
                                $process_info['func'],
                                $args
                            );
                        }
                    } else {
                        $object[$field_id] = $api_data[$field];
                    }
                } elseif (!empty($field_info['schema'])) {
                    $field_schema = $field_info['schema'];
                    $field_id = !empty($field_schema['name']) ? $field_schema['name'] : $field;

                    if (!empty($field_schema['is_single'])) {
                        $object[$field_id] = self::parseApiObject(
                            $api_data[$field],
                            $field_schema['type']
                        );
                    } else {
                        $object[$field_id] = self::parseApiList(
                            $api_data[$field],
                            $field_schema['type']
                        );
                    }
                } elseif (!empty($field_info['name'])) {
                    $object[$field_info['name']] = $api_data[$field];
                }
            } elseif (!empty($field_info['required']) && $field_info['required'] == true) {
                $is_complete = false;
            }
        }
        $object['is_complete_data'] = $is_complete;

        return $object;
    }

    /*
     * Parse data received by api
     *
     * @param  string $data        json or xml document with data
     * @param  string $object_type object type
     * @param  string $format      format of the document xml or json
     * @return array  parsed data
     */
    public static function parseApiList($data, $object_type)
    {
        $schema = fn_get_schema('api', $object_type, 'php', false);

        if (empty($schema)) {
            return false;
        }

        if (isset($data[$schema['object_name']])) {
            $sc_data = ApiData::getObjects($data[$schema['object_name']]);
        } else {
            $sc_data = ApiData::getObjects($data);
        }

        $objects = array();

        foreach ($sc_data as $k => $v) {
            $objects[] = self::convertFromSchema($schema, $v);
        }

        return $objects;
    }

    public static function getApiHash($data, $hash_fields)
    {
        $hash_data = array();
        foreach ($hash_fields as $field_id) {
            if (!empty($data[$field_id])) {
                if (is_array($data[$field_id])) {
                    if (!empty($data[$field_id]['hash'])) {
                        $hash_data[$field_id] = $data[$field_id]['hash'];
                    } else {
                        $hash_data[$field_id] = md5(serialize($data[$field_id]));
                    }
                } else {
                    $hash_data[$field_id] = $data[$field_id];
                }
            } else {
                $hash_data[$field_id] = '';
            }
        }

        return md5(serialize($hash_data));
    }

    // settings schema funcs
    public static function apiSettingsGetSelectedVariant($value, $variants)
    {
        if (empty($variants)) {
            return false;
        }

        foreach ($variants['settings_variant'] as $k => $v) {
            if ($v['variant_name'] == $value) {
                return $v['variant_id'];
            }
        }

        return '';
    }

    public static function apiSettingsGetVariants(
        $section_id,
        $subsection_id,
        $option_name,
        $option_id,
        $lang_code
    ) {
        if (!Registry::get('schema_settings_api_variants')) {
            fn_get_schema('settings', 'variants', 'php', false, true);
            Registry::set('schema_settings_api_variants', true);
        }

        $func = 'fn_settings_variants_'
         . strtolower($section_id)
         . '_'
         . ($subsection_id != '' ? $subsection_id . '_' : '')
         . $option_name;

        if (function_exists($func)) {
            $variants = $func();

            if (empty($variants)) {
                return false;
            }

            $i = 1;
            $result = array();
            foreach ($variants as $k => $v) {
                $result[] = array (
                    'option_id' => $option_id,
                    'variant_id' => $i,
                    'variant_name' => $k,
                    'description' => $v,
                    'position' => $i
                );

                $i++;
            }

            return self::getAsList('settings_variants', $result);
        } else {
            $args = array (
                'option_id' => $option_id
            );
            $options = self::getApiOptions();

            $sub_schema_data  = self::getApiSchemaData(
                'settings_variants',
                $args,
                array(),
                $options
            );

            if (!empty($sub_schema_data)) {
                return $sub_schema_data;
            }
        }

        return false;
    }

    // orders schema funcs
    public static function apiOrdersGetData(
        $order_id,
        $type,
        $object_type,
        $data = array(),
        $single = true
    ) {
        if (empty($data)) {
            $data = db_get_field(
                "SELECT data FROM ?:order_data WHERE order_id = ?i AND type = ?s",
                $order_id,
                $type
            );

            // Payment information
            if ($type == 'P') {
                $data = @unserialize(fn_decrypt_text($data));

            // Coupons, Taxes and Shipping information
            } elseif (strpos('CTL', $type) !== false) {
                $data = @unserialize($data);
            }

            if (empty($data)) {
                return array();
            }
        }

        if ($single) {
            return self::getAsApiObject($object_type, $data);
        }

        return self::getAsList($object_type, ApiData::getObjects($data));
    }

    public static function apiOrdersSetData($object_type, $object, $single = true)
    {
        if ($single) {
            return self::parseApiObject($object, $object_type);
        }

        return self::parseApiList($object, $object_type);
    }

    // users schema funcs
    public static function getStatesCount($country_code)
    {
        return db_get_field(
            "SELECT COUNT(a.state_id) FROM ?:states as a WHERE a.country_code = ?s",
            $country_code
        );
    }

    public static function getAddressPrefixByType($type)
    {
        if ($type == 'billing') {
            return 'b_';
        } elseif ($type == 'shipping') {
            return 's_';
        }

        return '';
    }

    public static function getAddressByType($profile_fields,
        $type,
        $lang_code = CART_LANGUAGE
    ) {
        $prefix = self::getAddressPrefixByType($type);
        $address = array();

        foreach ($profile_fields as $k => $v) {
            if (preg_match('/^' . $prefix . '([a-z]+)$/', $k, $matches)) {
                $address[$matches[1]] = $v;
            }
        }

        if (!empty($address['country'])) {
            $address['country_description'] = fn_get_country_name($address['country'], $lang_code);
            $address['states_count'] = self::getStatesCount($address['country']);
            if (!empty($address['state'])) {
                $address['state_description'] = fn_get_state_name($address['state'], $address['country'], $lang_code);
            }
        }

        return $address;
    }

    public static function getApiProfileAddresses($profile_fields, $lang_code = CART_LANGUAGE)
    {
        $address_types = array (
            'billing',
            'shipping'
        );

        $addresses = array();

        foreach ($address_types as $type) {
            $address = self::getAddressByType($profile_fields, $type, $lang_code);
            $address['type'] = $type;
            $address['address_id'] = $type . '_' . $profile_fields['profile_id'];

            $addresses[] = $address;
        }

        return self::getAsList('addresses', $addresses);
    }

    public static function parseApiProfilesData($profiles)
    {
        $profiles = self::parseApiList($profiles, 'profiles');

        $parsed = array();
        foreach ($profiles as $profile) {
            if (empty($profile['addresses'])) {
                continue;
            }

            foreach ($profile['addresses'] as $address) {
                $prefix = self::getAddressPrefixByType($address['type']);

                unset($address['type']);
                unset($address['address_id']);

                foreach ($address as $k => $v) {
                    $profile[$prefix . $k] = $v;
                }
            }

            unset($profile['addresses']);
            $parsed[] = $profile;
        }

        return $parsed;
    }

    public static function parseApiOrderAddress($address, $type)
    {
        $prefix = self::getAddressPrefixByType($type);
        unset($address['is_complete_data']);

        $result =  array();
        foreach ($address as $k => $v) {
            $result[$prefix . $k] = $v;
        }

        return $result;
    }
}
