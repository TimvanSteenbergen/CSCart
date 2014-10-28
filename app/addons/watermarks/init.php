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

define('WATERMARK_FONT_ALPHA', 50);
define('WATERMARK_NONE', 'N');
define('WATERMARK_DISABLED', 'D');
define('WATERMARK_NONCREATED', 'A');
define('WATERMARK_FAILED', 'F');
define('WATERMARK_CREATED', 'Y');

fn_register_hooks(
    'attach_absolute_image_paths',
    'delete_image',
    'init_company_data',
    'generate_thumbnail_file_pre',
    'generate_thumbnail_post',
    'get_route',
    'update_company'
);
