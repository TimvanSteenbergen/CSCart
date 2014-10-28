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

$schema['central']['website']['items']['comments_and_reviews'] = array(
    'attrs' => array(
        'class'=>'is-addon'
    ),
    'href' => 'discussion_manager.manage',
    'position' => 601
);

$schema['central']['website']['items']['discussion_title_home_page'] = array(
    'attrs' => array(
        'class'=>'is-addon'
    ),
    'href' => 'discussion.update?discussion_type=E',
    'position' => 602
);

return $schema;
