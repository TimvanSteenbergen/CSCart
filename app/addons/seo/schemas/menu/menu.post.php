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

$schema['central']['website']['items']['seo_rules'] = array(
    'attrs' => array(
        'class'=>'is-addon'
    ),
    'href' => 'seo_rules.manage',
    'position' => 400
);

$schema['central']['website']['items']['seo.redirects_manager'] = array(
    'attrs' => array(
        'class'=>'is-addon'
    ),
    'href' => 'seo_redirects.manage',
    'position' => 410
);

return $schema;
