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
    'categories' => array (
        'modes' => array (
            'do_m_delete' => array('POST'),
            'delete' => array('POST'),
            'm_delete_confirmation' => array('POST')
        ),
    ),
    'countries' => array (
        'modes' => array (
            'update' => array('POST'),
            'delete' => array('POST'),
        ),
    ),
    'companies' => array (
        'modes' => array (
            'update' => array('POST'),
            'delete' => array('GET', 'POST'),
            'm_delete' => array('GET', 'POST'),
        ),
    ),
    'currencies' => array (
        'modes' => array (
            'update' => array('POST'),
            'delete' => array('POST'),
        ),
    ),
    'destinations' => array (
        'modes' => array (
            'update' => array('POST'),
            'update_destinations' => array('POST'),
            'delete_destinations' => array('POST'),
        ),
    ),
    'localizations' => array (
        'restrict' => array ('POST')
    ),
    'database' => array (
        'restrict' => array ('POST')
    ),
    'exim' => array (
        'restrict' => array ('POST')
    ),
    'languages' => array (
        'restrict' => array ('POST'),
        'modes' => array (
            'delete_language' => array('GET'),
        ),
    ),
    'usergroups' => array (
        'modes' => array (
            'add' => array('POST'),
            'delete' => array('POST'),
        ),
    ),
    'pages' => array (
        'modes' => array (
            'do_m_delete' => array('POST'),
            'delete' => array('POST'),
            'm_delete_confirmation' => array('POST'),
        ),
    ),
    'products' => array (
        'modes' => array (
            'do_m_delete' => array('POST'),
            'delete' => array('POST'),
            'm_delete_confirmation' => array('POST'),
        ),
    ),
    'profiles' => array (
        'restrict' => array ('POST')
    ),
    'payments' => array (
        'restrict' => array ('POST')
    ),
    'settings' => array (
        'restrict' => array ('POST')
    ),
    'addons' => array (
        'restrict' => array ('POST'),
        'modes' => array (
            'update_status' => array('GET', 'POST'),
            'install' => array('GET', 'POST'),
            'uninstall' => array('GET', 'POST'),
        ),
    ),
    'shippings' => array (
        'modes' => array (
            'delete_shippings' => array('POST'),
            'add_shippings' => array('POST'),
            'test' => array('GET'),
        ),
    ),
    'customization' => array (
        'restrict' => array ('GET', 'POST')
    ),
    'states' => array (
        'modes' => array (
            'update' => array('POST'),
            'delete' => array('POST'),
        ),
    ),

    'taxes' => array (
        'modes' => array (
            'do_m_delete' => array('POST'),
            'delete' => array('POST'),
            'm_delete_confirmation' => array('POST'),
        ),
    ),
    'file_editor' => array (
        'restrict' => array ('POST'),
        'modes' => array (
            'delete_file' => array('GET'),
            'rename_file' => array('GET'),
            'create_file' => array('GET'),
            'chmod' => array('GET'),
            'get_file' => array('GET'),
            'restore' => array('GET')
        ),
    ),
    'tools' => array (
        'modes' => array (
            'phpinfo' => array('POST', 'GET'),
            'update_status' => array('GET'),
        ),
    ),
    'upgrade_center' => array (
        'restrict' => array ('POST')
    ),
    'block_manager' => array (
        'restrict' => array ('POST'),
        'modes' => array (
            'delete' => array('GET'),
            'bulk_actions' => array('GET'),
            'update_status' => array('GET'),
        ),
    ),
    'image' => array(
        'modes' => array (
            'delete_image' => array('POST', 'GET'),
            'delete_image_pair' => array('POST', 'GET')
        ),
    ),
    'elf_connector' => array(
        'restrict' => array('POST'),
    ),
    'themes' => array(
        'modes' => array(
            'upload' => array('POST'),
            'clone' => array('POST'),
            'styles' => array('GET', 'POST'),
            'set' => array('GET', 'POST'),
            'delete' => array('GET', 'POST'),
            'install' => array('GET', 'POST'),
        ),
    ),
    'upgrade_center' => array (
        'restrict' => array ('POST'),
        'modes' => array (
            'get_upgrade' => array('GET', 'POST'),
            'run_backup' => array('GET', 'POST'),
            'check' => array('GET', 'POST'),
        ),
    ),
);
