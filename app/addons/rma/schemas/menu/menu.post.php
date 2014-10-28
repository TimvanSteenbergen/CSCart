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

$schema['central']['orders']['items']['return_requests'] = array(
    'attrs' => array(
        'class'=>'is-addon'
    ),
    'href' => 'rma.returns',
    'position' => 900,
    'subitems' => array(
        'return_requests' => array(
            'href' => 'rma.returns',
            'position' => 910,
        ),
        'rma_reasons' => array(
            'href' => 'rma.properties?property_type=R',
            'position' => 920,
        ),
        'rma_actions' => array(
            'href' => 'rma.properties?property_type=A',
            'position' => 930,
        ),
        'rma_request_statuses' => array(
            'href' => 'statuses.manage?type=R',
            'position' => 940,
        ),
    )
);

return $schema;
