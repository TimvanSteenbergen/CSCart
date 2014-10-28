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

$schema['polls'] = array (
    'content' => array (
        'items' => array (
            'remove_indent' => true,
            'hide_label' => true,
            'type' => 'enum',
            'object' => 'news',
            'items_function' => 'fn_get_polls',
            'fillings' => array (
                'manually' => array (
                    'picker' => 'addons/polls/pickers/polls/picker.tpl',
                    'picker_params' => array (
                        'multiple' => true,
                    ),
                ),
            ),
        ),
    ),
    'templates' => 'addons/polls/blocks/',
    'wrappers' => 'blocks/wrappers',
    'cache' => array (
        'update_handlers' => array ('polls', 'polls_answers', 'polls_votes', 'poll_descriptions', 'poll_items'),
    ),
);

return $schema;
