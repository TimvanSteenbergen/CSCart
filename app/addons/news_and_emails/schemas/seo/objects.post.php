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

$schema['n'] = array(
    'table' => '?:news_descriptions',
    'description' => 'news',
    'dispatch' => 'news.view',
    'item' => 'news_id',
    'condition' => '',

    'name' => 'news',

    'html_options' => array('file'),
    'option' => 'seo_other_type',

    'indexed_pages' => array(
        'news.view' => array(
            'index' => array('news_id')
        ),
        'news.list' => array()
    )
);

return $schema;
