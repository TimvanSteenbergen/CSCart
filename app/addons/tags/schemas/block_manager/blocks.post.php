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

$schema['tags'] = array (
    'content' => array (
        'items' => array (
            'remove_indent' => true,
            'hide_label' => true,
            'type' => 'enum',
            'object' => 'products',
            'items_function' => 'fn_get_tags',
            'fillings' => array (
                'tag_cloud' => array (
                    'params' => array (
                        'status' => 'A',
                        'sort_by' => 'popularity',
                        'sort_order' => 'desc',
                        'sort_popular' => true,
                    ),
                    'settings' => array(
                        'limit' => array (
                            'type' => 'input',
                            'default_value' => 50
                        )
                    )
                ),
                'my_tags' => array (
                    'params' => array (
                        'auth' => array(
                            'user_id' => '%USER_ID%',
                        ),
                        'sort_by' => 'tag',
                        'sort_order' => 'asc',
                        'see' => 'my'
                    ),
                )
            ),
        ),
    ),
    'templates' => array (
        'addons/tags/blocks/tag_cloud.tpl' => array (
            'fillings' => array ('tag_cloud')
        ),
        'addons/tags/blocks/user_tag_cloud.tpl' => array (
            'fillings' => array ('my_tags')
        )
    ),
    'wrappers' => 'blocks/wrappers',
    'cache' => array (
        'update_handlers' => array('tags', 'tag_links'),
    ),
);

return $schema;
