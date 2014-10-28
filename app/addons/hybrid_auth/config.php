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

fn_define('LOGIN_STATUS_USER_EXIST', 9);
fn_define('LOGIN_STATUS_NOT_FOUND_EMAIL', 10);

fn_define('HYBRID_AUTH_FALSE', 0);
fn_define('HYBRID_AUTH_OK', 1);
fn_define('HYBRID_AUTH_LOGIN', 2);
fn_define('HYBRID_AUTH_LOADING', 3);
fn_define('HYBRID_AUTH_ERROR_AUTH_DATA', 4);
