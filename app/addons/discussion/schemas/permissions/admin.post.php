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

$schema['discussion_manager'] = array (
    'permissions' => array ('GET' => 'view_discussions', 'POST' => 'manage_discussions'),
);
$schema['discussion'] = array (
    'permissions' => array ('GET' => 'view_discussions', 'POST' => 'manage_discussions'),
);

$schema['index']['modes']['delete_post'] = array (
    'permissions' => 'manage_discussions'
);

$schema['tools']['modes']['update_status']['param_permissions']['table']['discussion_posts'] = 'manage_discussions';

return $schema;
