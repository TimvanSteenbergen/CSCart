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

$schema = array (
    'table' => 'categories',
    'object_name' => 'category',
    'key' => array('category_id'),
    'group_by' => 'categories.category_id',
    'sortings' => array (
        'timestamp' => 'categories.timestamp',
        'name' => 'category_descriptions.category',
        'position' => array ('categories.position', 'category_descriptions.category')
    ),
    'references' => array (
        'category_descriptions' => array (
            'join_type' => 'LEFT',
            'fields' => array (
                'category_id' => array (
                    'db_field' => 'category_id'
                ),
                'lang_code' => array (
                    'param' => 'lang_code'
                )
            )
        ),
        'images_links' => array (
            'join_type' => 'LEFT',
            'fields' => array (
                'object_id' => array (
                    'db_field' => 'category_id'
                ),
                'object_type' => array (
                    'value' => 'category'
                )
            )
        )
    ),
    'fields' => array (
        'category_id' => array (
            'db_field' => 'category_id'
        ),
        'parent_id' => array (
            'db_field' => 'parent_id'
        ),
        'parent_category' => array (
            'name' => 'parent_category'
        ),
        'level' => array (
            'name' => 'level'
        ),
        'status' => array (
            'db_field' => 'status'
        ),
        'subcategory_id' => array (
            'name' => 'has_children'
        ),
        'product_count' => array (
            'db_field' => 'product_count'
        ),
        'subcategory_count' => array (
            'name' => 'subcategory_count'
        ),
        'position' => array (
            'db_field' => 'position'
        ),
        'timestamp' => array (
            'db_field' => 'timestamp'
        ),
        'category' => array (
            'table' => 'category_descriptions',
            'db_field' => 'category'
        ),
        'title' => array (
            'table' => 'category_descriptions',
            'db_field' => 'category'
        ),
        'description' => array (
            'table' => 'category_descriptions',
            'db_field' => 'description'
        ),
        'meta_keywords' => array (
            'table' => 'category_descriptions',
            'db_field' => 'meta_keywords'
        ),
        'meta_description' => array (
            'table' => 'category_descriptions',
            'db_field' => 'meta_description'
        ),
        'page_title' => array (
            'table' => 'category_descriptions',
            'db_field' => 'page_title'
        ),
        'icon' => array (
            'schema' => array (
                'is_single' => true,
                'type' => 'images',
                'name' => 'icon',
                'filter' => array (
                    'image_id' => array (
                        'table' => 'images_links',
                        'db_field' => 'image_id'
                    )
                )
            )
        ),
        'subcategories' => array (
            'schema' => array (
                'type' => 'categories',
                'name' => 'subcategories',
                'filter' => array (
                    'parent_id' => array (
                        'db_field' => 'category_id'
                    )
                )
            )
        )
    )
);
return $schema;
