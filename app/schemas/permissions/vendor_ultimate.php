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

return array(
    'default_permission' => true,
    'controllers' => array (
        'companies' => array (
            'modes' => array (
                'add' => array (
                    'permissions' => false
                ),
            ),
        ),
        'localizations' => array (
            'permissions' => false
        ),
        'storage' => array (
            'modes' => array(
                'index' => array(
                    'permissions' => true,
                ),
                'clear_cache' => array(
                    'permissions' => true,
                ),
                'clear_thumbnails' => array(
                    'permissions' => true,
                ),
            ),
            'permissions' => false
        ),
        'upgrade_center' => array (
            'permissions' => false
        ),
        'database' => array (
            'permissions' => false
        ),
        'countries' => array (
            'modes' => array(
                'manage' => array(
                    'permissions' => array ('GET' => true, 'POST' => false),
                ),
            ),
            'permissions' => false,
        ),
        'taxes' => array (
            'modes' => array(
                'manage' => array(
                    'permissions' => array ('GET' => true, 'POST' => false),
                ),
                'update' => array(
                    'permissions' => array ('GET' => true, 'POST' => false),
                ),
            ),
            'permissions' => false,
        ),
        'shippings' => array (
            'permissions' => true,
        ),
        'destinations' => array (
            'modes' => array(
                'manage' => array(
                    'permissions' => array ('GET' => true, 'POST' => false),
                ),
                'update' => array(
                    'permissions' => array ('GET' => true, 'POST' => false),
                ),
            ),
            'permissions' => false,
        ),
        'statuses' => array (
            'modes' => array(
                'manage' => array(
                    'permissions' => array ('GET' => true, 'POST' => false),
                ),
                'update' => array(
                    'permissions' => array ('GET' => true, 'POST' => true),
                ),
            ),
            'permissions' => false,
        ),
        'states' => array (
            'modes' => array(
                'manage' => array(
                    'permissions' => true,
                ),
                'update' => false,
            ),

            'permissions' => false,
        ),
        'profile_fields' => array (
            'modes' => array (
                'manage' => array (
                    'permissions' => array ('GET' => true, 'POST' => false),
                ),
                'update' => array (
                    'permissions' => array ('GET' => true, 'POST' => false),
                ),
            ),
            'permissions' => false,
        ),
        'profiles' => array (
            'modes' => array (
                'manage' => array (
                    'condition' => array(
                        'user_type' => array(
                            'A' => array(
                                'operator' => 'and',
                                'function' => array('fn_check_permission_manage_profiles', 'A'),
                            ),
                        )
                    ),
                ),
            ),
        ),
        'usergroups' => array (
            'modes' => array (
                'manage' => array (
                    'permissions' => array ('GET' => true, 'POST' => false),
                ),
            ),
            'permissions' => false,
        ),
        'currencies' => array (
            'modes' => array (
                'delete' => array (
                    'permissions' => false,
                ),
                'update' => array (
                    'permissions' => array ('GET' => true, 'POST' => false),
                ),
            ),
            'permissions' => true,
        ),
        'languages' => array (
            'modes' => array (
                'delete_language' => array (
                    'permissions' => false,
                ),
                'm_delete' => array (
                    'permissions' => false,
                ),
                'clone_language' => array (
                    'permissions' => false,
                ),
                'install_from_po' => array (
                    'permissions' => false,
                ),
                'install' => array (
                    'permissions' => false,
                ),
                'update_status' => array (
                    'permissions' => false,
                ),
                'update_translation' => array (
                    'permissions' => false,
                ),
            ),
            'permissions' => true,
        ),
        'payments' => array (
            'permissions' => true,
        ),
        'settings_wizard' => array (
            'permissions' => false,
        ),
        'addons' => array (
            'modes' => array (
                'uninstall' => array (
                    'permissions' => false,
                ),
                'install' => array (
                    'permissions' => false,
                ),
            ),
        ),
        'tools' => array (
            'modes' => array (
                'update_status' => array (
                    'param_permissions' => array (
                        'table' => array (
                            'destinations' => false,
                            'countries' => false,
                            'states' => false,
                            'taxes' => false,
                        )
                    )
                ),
                'cleanup_history' => array(
                    'permissions' => true
                ),
            )
        ),
    ),
    'addons' => array (),
    'export' => array (),
    'import' => array (),

);
