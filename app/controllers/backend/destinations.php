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

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$_REQUEST['destination_id'] = empty($_REQUEST['destination_id']) ? 0 : $_REQUEST['destination_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $suffix = '';
    //
    // Update destination elements
    //
    if ($mode == 'update') {

        $destination_id = fn_update_destination($_REQUEST['destination_data'], $_REQUEST['destination_id'], DESCR_SL);

        $suffix = ".update?destination_id=$destination_id";
    }

    //
    // Delete selected destinations
    //
    if ($mode == 'm_delete') {

        if (!empty($_REQUEST['destination_ids'])) {
            fn_delete_destinations($_REQUEST['destination_ids']);
        }

        $suffix = ".manage";
    }

    return array(CONTROLLER_STATUS_OK, "destinations$suffix");
}

// ----------------------------- GET routines -------------------------------------------------

// Destiantion data
if ($mode == 'update') {

    $destination = db_get_row("SELECT a.destination_id, a.status, destination, a.localization FROM ?:destinations as a LEFT JOIN ?:destination_descriptions as b ON b.destination_id = a.destination_id AND b.lang_code = ?s WHERE a.destination_id = ?i", DESCR_SL, $_REQUEST['destination_id']);

    if (empty($destination)) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

    $destination_data = array();

    $destination_data['states'] = db_get_hash_single_array("SELECT a.state_id, CONCAT(d.country, ': ', b.state) as state FROM ?:states as a LEFT JOIN ?:state_descriptions as b ON b.state_id = a.state_id AND b.lang_code = ?s LEFT JOIN ?:destination_elements as c ON c.element_type = 'S' AND c.element = a.state_id LEFT JOIN ?:country_descriptions as d ON d.code = a.country_code AND d.lang_code = ?s WHERE c.destination_id = ?i", array('state_id', 'state'), DESCR_SL, DESCR_SL, $_REQUEST['destination_id']);

    $destination_data['countries'] = db_get_hash_single_array("SELECT a.code, b.country FROM ?:countries as a LEFT JOIN ?:country_descriptions as b ON b.code = a.code AND b.lang_code = ?s LEFT JOIN ?:destination_elements as c ON c.element_type = 'C' AND c.element = a.code WHERE c.destination_id = ?i", array('code', 'country'), DESCR_SL, $_REQUEST['destination_id']);

    $destination_data['zipcodes'] = db_get_hash_single_array("SELECT element_id, element FROM ?:destination_elements WHERE element_type = 'Z' AND destination_id = ?i", array('element_id', 'element'), $_REQUEST['destination_id']);
    $destination_data['zipcodes'] = implode("\n", $destination_data['zipcodes']);

    $destination_data['cities'] = db_get_hash_single_array("SELECT element_id, element FROM ?:destination_elements WHERE element_type = 'T' AND destination_id = ?i", array('element_id', 'element'), $_REQUEST['destination_id']);
    $destination_data['cities'] = implode("\n", $destination_data['cities']);

    $destination_data['addresses'] = db_get_hash_single_array("SELECT element_id, element FROM ?:destination_elements WHERE element_type = 'A' AND destination_id = ?i", array('element_id', 'element'), $_REQUEST['destination_id']);
    $destination_data['addresses'] = implode("\n", $destination_data['addresses']);

    Registry::get('view')->assign('destination_data', $destination_data);
    Registry::get('view')->assign('destination', $destination);

    Registry::get('view')->assign('states', fn_destination_get_states(CART_LANGUAGE));
    Registry::get('view')->assign('countries', fn_get_simple_countries(true, CART_LANGUAGE));

// Add destination
} elseif ($mode == 'add') {

    Registry::get('view')->assign('states', fn_destination_get_states(CART_LANGUAGE));
    Registry::get('view')->assign('countries', fn_get_simple_countries(true, CART_LANGUAGE));

// Destinations list
} elseif ($mode == 'manage') {

    $destinations = fn_get_destinations(DESCR_SL);
    Registry::get('view')->assign('destinations', $destinations);

} elseif ($mode == 'delete') {

    if (!empty($_REQUEST['destination_id'])) {
        fn_delete_destinations((array) $_REQUEST['destination_id']);
    }

    return array(CONTROLLER_STATUS_REDIRECT, "destinations.manage");
}

function fn_delete_destinations($destination_ids)
{
    foreach ($destination_ids as $dest_id) {
        if (!empty($dest_id) && $dest_id != 1) {
            db_query("DELETE FROM ?:destinations WHERE destination_id = ?i", $dest_id);
            db_query("DELETE FROM ?:destination_descriptions WHERE destination_id = ?i", $dest_id);
            db_query("DELETE FROM ?:destination_elements WHERE destination_id = ?i", $dest_id);
            db_query("DELETE FROM ?:shipping_rates WHERE destination_id = ?i", $dest_id);
            db_query("DELETE FROM ?:tax_rates WHERE destination_id = ?i", $dest_id);
        }
    }
}

function fn_update_destination($data, $destination_id, $lang_code = DESCR_SL)
{
    $data['localization'] = empty($data['localization']) ? '' : fn_implode_localizations($data['localization']);

    if (!empty($destination_id)) {
        db_query('UPDATE ?:destinations SET ?u WHERE destination_id = ?i', $data, $destination_id);
        db_query('UPDATE ?:destination_descriptions SET ?u WHERE destination_id = ?i AND lang_code = ?s', $data, $destination_id, $lang_code);
        db_query("DELETE FROM ?:destination_elements WHERE destination_id = ?i", $destination_id);
    } else {
        $destination_id = $data['destination_id'] = db_query("REPLACE INTO ?:destinations ?e", $data);

        foreach (fn_get_translation_languages() as $data['lang_code'] => $_v) {
            db_query("REPLACE INTO ?:destination_descriptions ?e", $data);
        }
    }

    $_data = array(
        'destination_id' => $destination_id
    );

    if (!empty($data['states'])) {
        $_data['element_type'] = 'S';
        foreach ($data['states'] as $key => $_data['element']) {
            db_query("INSERT INTO ?:destination_elements ?e", $_data);
        }
    }

    if (!empty($data['countries'])) {
        $_data['element_type'] = 'C';
        foreach ($data['countries'] as $key => $_data['element']) {
            db_query("INSERT INTO ?:destination_elements ?e", $_data);
        }
    }

    if (!empty($data['zipcodes'])) {
        $zipcodes = explode("\n", $data['zipcodes']);
        $_data['element_type'] = 'Z';
        foreach ($zipcodes as $key => $value) {
            $value = trim($value);
            if (!empty($value)) {
                $_data['element'] = $value;
                db_query("INSERT INTO ?:destination_elements ?e", $_data);
            }
        }
    }

    if (!empty($data['cities'])) {
        $cities = explode("\n", $data['cities']);
        $_data['element_type'] = 'T';
        foreach ($cities as $key => $value) {
            $value = trim($value);
            if (!empty($value)) {
                $_data['element'] = $value;
                db_query("INSERT INTO ?:destination_elements ?e", $_data);
            }
        }
    }

    if (!empty($data['addresses'])) {
        $addresses = explode("\n", $data['addresses']);
        $_data['element_type'] = 'A';
        foreach ($addresses as $key => $value) {
            $value = trim($value);
            if (!empty($value)) {
                $_data['element'] = $value;
                db_query("INSERT INTO ?:destination_elements ?e", $_data);
            }
        }
    }

    return $destination_id;
}

function fn_destination_get_states($lang_code)
{
    list($_states) = fn_get_states(array(), 0, $lang_code);
    $states = array();
    foreach ($_states as $_state) {
        $states[$_state['state_id']] = $_state['country'] . ': ' . $_state['state'];
    }

    return $states;

}
