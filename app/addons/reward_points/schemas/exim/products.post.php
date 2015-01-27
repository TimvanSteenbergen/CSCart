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

$schema['export_fields']['Pay by points'] = array (
    'db_field' => 'is_pbp',
);

$schema['export_fields']['Override points'] = array (
    'db_field' => 'is_op',
);

$schema['export_fields']['Override exchange rate'] = array (
    'db_field' => 'is_oper',
);

return $schema;
