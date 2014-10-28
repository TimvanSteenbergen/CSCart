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

function fn_searchanise_add_product_actions($primary_object_ids)
{
    if (empty($primary_object_ids) || !is_array($primary_object_ids)) {
        return true;
    }
    $product_ids = array();

    // Add actions for all updated products.
    foreach ($primary_object_ids as $k => $v) {
        if (!empty($v['product_id'])) {
            $product_ids[] = $v['product_id'];
        }
    }
    
    fn_se_add_chunk_product_action('update', $product_ids);

    return true;
}