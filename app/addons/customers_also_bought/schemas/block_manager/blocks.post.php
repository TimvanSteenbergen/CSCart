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

$schema['products']['content']['items']['fillings']['also_bought'] = array (
    'params' => array (
        'sort_by' => 'amnt',
        'request' => array(
            'also_bought_for_product_id' => '%PRODUCT_ID%'
        ),
    ),
);

$schema['products']['cache']['update_handlers'][] = 'also_bought_products';

return $schema;
