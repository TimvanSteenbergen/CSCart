<?php
/***************************************************************************
 * *
 * (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev *
 * *
 * This is commercial software, only users who have purchased a valid *
 * license and accept to the terms of the License Agreement can install *
 * and use this program. *
 * *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT OF THE SOFTWARE LICENSE AGREEMENT IN THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE. *
 ****************************************************************************/

$schema['ebay'] = array(
    'modes' => array (
        'delete' => array (
            'permissions' => 'manage_ebay_templates'
        ),
        'm_delete' => array (
            'permissions' => 'manage_ebay_templates'
        ),
    ),
    'permissions' => array ('GET' => 'view_ebay_templates', 'POST' => 'manage_ebay_templates'),
);

$schema['tools']['modes']['update_status']['param_permissions']['table']['ebay_templates'] = 'manage_ebay_templates';

return $schema;
