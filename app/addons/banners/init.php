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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
    'delete_image_pre',
    'update_language_post',
    'delete_languages_post'
);

if (!fn_allowed_for('ULTIMATE:FREE')) {
    fn_register_hooks(
        'localization_objects'
    );
}

if (fn_allowed_for('ULTIMATE')) {
    fn_register_hooks(
        'delete_company',
        'ult_check_store_permission'
    );
}
