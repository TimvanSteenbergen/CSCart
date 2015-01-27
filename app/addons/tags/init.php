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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

define('TAGS_MAX_LEVEL', 6);

fn_register_hooks(
    'delete_product_post',
    'clone_product',
    'update_product_post',
    'update_page_post',
    'delete_page',
    'clone_page',
    'get_page_data',
    'get_pages',
    'get_products',
    'get_users',
    'seo_is_indexed_page',
    'get_predefined_statuses'
);
