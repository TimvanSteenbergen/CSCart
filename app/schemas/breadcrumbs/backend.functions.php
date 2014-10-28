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

/**
 * Gets product main category products link
 *
 * @param int $product_id Product identifier
 * @return array Breadcrumb link data
 */
function fn_br_get_product_main_category_link($product_id)
{
    $result = array();

    $category_id = db_get_field('SELECT category_id FROM ?:products_categories WHERE product_id = ?i and link_type = ?s', $product_id, 'M');

    if (!empty($category_id)) {
        $result = array(
            'title' => __('category') . ': ' . fn_get_category_name($category_id),
            'link' => "products.manage.reset_view?cid=" . $category_id
        );
    }

    return  $result;
}

/**
 * Gets pages manage url
 *
 * @param string $page_type Page type
 * @return string Pages manage url
 */
function fn_br_get_pages_manage_url($page_type)
{
    $url = 'pages.manage?' . (!empty($page_type) ? 'page_type=' . $page_type :  'get_tree=multi_level');

    return $url;
}

/**
 * Gets report description
 *
 * @param int $report_id Report identifier
 * @param string $lang_code 2 letters language code
 * @return string Report description
 */
function fn_br_get_report_description($report_id, $lang_code = CART_LANGUAGE)
{
    $description = db_get_field("SELECT description FROM ?:sales_reports_descriptions WHERE report_id = ?i AND lang_code = ?s", $report_id, $lang_code);

    return $description;
}

/**
 * Gets static data owner link
 *
 * @param string $section Static data section
 * @return array breadcrumb link data
 */
function fn_br_get_static_data_owner_link($section)
{
    $result = array();
    $section = empty($section) ? 'C' : $section;

    $schema = fn_get_schema('static_data', 'schema');
    $section_data = $schema[$section];

    if (!empty($section_data['owner_object']['return_url']) && !empty($section_data['owner_object']['return_url_text'])) {
        $result = array(
            'link' => $section_data['owner_object']['return_url'],
            'title' => __($section_data['owner_object']['return_url_text'])
        );
    }

    return $result;
}

/**
 * Checks if users link should be added
 *
 * @param string $request Previous request params
 * @return boolean Flag that determines if link should be added
 */
function fn_br_check_users_link($request)
{
    // Add user type only if it was not passed on the previous page
    $result = !empty($request['dispatch']) && $request['dispatch'] == 'users.manage' && !empty($request['usertype']);

    return $result;
}

/**
 * Checks if users link with type should be added
 *
 * @param string $request Previous request params
 * @param string $user_type User typer
 * @return boolean Flag that determines if link should be added
 */
function fn_br_check_user_type_link($request, $user_type)
{
    // Add user type only if it was not passed on the previous page
    $result = !empty($user_type) && !fn_br_check_users_link($request);

    return $result;
}
