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

$schema['central']['marketing']['items']['ebay_templates'] = array(
    'attrs' => array(
        'class'=>'is-addon'
    ),
    'href' => 'ebay.manage',
    'position' => 500
);

if (fn_ebay_extend_addons()) {
    $schema['central']['marketing']['items']['ebay_templates_extra'] = array(
        'attrs' => array(
            'class'=>'is-addon'
        ),
        'href' => 'ebay.extra',
        'position' => 510
    );
}

return $schema;
