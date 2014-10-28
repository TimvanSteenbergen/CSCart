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

use Tygh\BlockManager\Block;

return array(
    'langvar' => array(
        'function' => 'fn_update_lang_var',
        'args' => array(
            array(
                array(
                    'name' => '$id',
                    'value' => '$value'
                ),
            ),
            '$lang_code'
        ),
        'input_type' => 'textarea', // (input|textarea|wysiwyg|price)
    ),
    'product' => array(
        'function' => 'fn_update_product',
        'args' => array(array('$field' => '$value'), '$id', '$lang_code'),
        'input_type' => 'input',
        'input_type_fields' => array(
            'product' => 'input',
            'full_description' => 'wysiwyg',
            'price' => 'price',
        ),
    ),
    'category' => array(
        'function' => 'fn_update_category',
        'args' => array(array('$field' => '$value'), '$id', '$lang_code'),
        'input_type' => 'input',
        'input_type_fields' => array(
            'description' => 'wysiwyg',
        ),
    ),
    'page' => array(
        'function' => 'fn_update_page',
        'args' => array(array('$field' => '$value'), '$id', '$lang_code'),
        'input_type' => 'input',
        'input_type_fields' => array(
            'description' => 'wysiwyg',
        ),
    ),
    'block' => array(
        'function' => function($field, $value, $id, $lang_code) {
            $block = Block::instance()->getById($id);
            $data = array(
                'block_id' => $id,
                'type' => $block['type'],
            );
            $description = array();
            if ($field == 'content') {
                $data['content_data'] = array(
                    'lang_code' => $lang_code,
                    'content' => array(
                        'content' => $value
                    ),
                );
            } elseif ($field == 'name') {
                $description = array(
                    'lang_code' => $lang_code,
                    'name' => $value,
                );
                $data['description'] = $description;
            } else {
                return;
            }
            Block::instance()->update($data, $description);
        },
        'args' => array('$field', '$value', '$id', '$lang_code'),
        'input_type' => 'input',
        'input_type_fields' => array(
            'content' => 'textarea',
        ),
    ),
);
