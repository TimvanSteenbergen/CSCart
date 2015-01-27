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

fn_register_hooks(
    'update_product_post',
    'delete_product_post',
    'update_category_post',
    'delete_category_after',
    'delete_order',
    'update_news',
    'delete_news',
    'update_page_post',
    'delete_page',
    'update_event',
    'delete_event',
    'clone_product',
    'get_product_data',
    'get_products',
    'get_categories',
    'get_pages',
    'get_companies',
    'delete_company',
    'companies_sorting',
    'get_predefined_statuses',
    'init_secure_controllers',
    'update_company'
);
