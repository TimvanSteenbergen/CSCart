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

$schema['top']['settings']['items']['Stores'] = array(
    'href' => 'settings.manage?section_id=Stores',
    'position' => 510,
    'type' => 'setting',
);

$schema['top']['administration']['items']['stores'] = array(
    'href' => 'companies.manage',
    'position' => 100
);

if (fn_allowed_for('ULTIMATE:FREE')) {
    $schema['central']['customers']['items']['usergroups']['is_promo'] = true;
}

if (Registry::get('runtime.company_id') && !Registry::get('runtime.simple_ultimate')) {
    unset($schema['top']['settings']['items']['Emails'], $schema['top']['settings']['items']['Security'], $schema['top']['settings']['items']['Shippings'], $schema['top']['settings']['items']['Stores'], $schema['top']['settings']['items']['Upgrade_center']);
}

return $schema;
