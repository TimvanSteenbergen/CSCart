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

include_once(Registry::get('config.dir.addons') . 'google_export/schemas/exim/products.functions.php');

$schema['export_fields']['Google description'] = array (
    'table' => 'product_descriptions',
    'db_field' => 'full_description',
    'multilang' => true,
    'process_get' => array ('fn_exim_google_export_format_description', '#this'),
    'export_only' => true,
);

$schema['export_fields']['Sale price'] = array (
    'table' => 'product_prices',
    'db_field' => 'price',
    'process_get' => array('fn_exim_google_export_format_price', '#this', '#key')
);

return $schema;
