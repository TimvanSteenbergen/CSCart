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

$schema['central']['marketing']['items']['gift_certificates'] = array(
    'attrs' => array(
        'class'=>'is-addon'
    ),
    'href' => 'gift_certificates.manage',
    'position' => 500,
    'subitems' => array(
        'gift_certificates' => array(
            'href' => 'gift_certificates.manage',
            'position' => 510
        ),
        'gift_certificate_statuses' => array(
            'href' => 'statuses.manage?type=G',
            'position' => 520
        )
    )
);

return $schema;
