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

$schema['news'] = array(
    'condition_function' => 'fn_news_and_email_create_news_condition',
    'default_params' => array(),
    'title' => __('news'),
    'more_data_function' => '',
    'bulk_data_function' => '',
    'action_link' => 'news.manage?compact=Y&q=%search%&match=any&content_id=news_content',
    'detailed_link' => 'news.update?news_id=%id%',
    'show_in_search' => true,
);

return $schema;
