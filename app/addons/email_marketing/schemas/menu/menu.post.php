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

use Tygh\Registry;

$schema['central']['marketing']['items']['subscribers'] = array(
    'attrs' => array(
        'class'=>'is-addon'
    ),
    'href' => 'em_subscribers.manage',
    'position' => 201,
);

if (!Registry::get('runtime.company_id') || Registry::get('runtime.simple_ultimate')) {

    $schema['top']['administration']['items']['export_data']['subitems']['subscribers'] = array(
        'href' => 'exim.export?section=subscribers',
        'position' => 201
    );

    $schema['top']['administration']['items']['import_data']['subitems']['subscribers'] = array(
        'href' => 'exim.import?section=subscribers',
        'position' => 201
    );
}

return $schema;
