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

$schema['central']['marketing']['items']['banners'] = array(
    'attrs' => array(
        'class'=>'is-addon'
    ),
    'href' => 'banners.manage',
    'position' => 500
);

if (!empty($schema['top']['addons']['items']['statistics'])) {
    $schema['top']['addons']['items']['statistics']['subitems']['banners'] = array(
        'href' => 'statistics.banners',
        'position' => 900
    );
}

return $schema;
