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

$schema['gift_certificates']['modes']['add']['vendor_only'] = true;
$schema['gift_certificates']['modes']['add']['use_company'] = true;
$schema['gift_certificates']['modes']['add']['page_title'] = 'new_certificate';

$schema['gift_certificates']['modes']['update']['vendor_only'] = true;
$schema['gift_certificates']['modes']['update']['use_company'] = true;
$schema['gift_certificates']['modes']['update']['page_title'] = 'editing_certificate';

$schema['gift_certificates']['modes']['manage']['vendor_only'] = true;
$schema['gift_certificates']['modes']['manage']['use_company'] = true;
$schema['gift_certificates']['modes']['manage']['page_title'] = 'gift_certificates';

return $schema;
