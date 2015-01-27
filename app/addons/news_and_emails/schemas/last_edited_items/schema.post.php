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

$schema['newsletters.update'] = array(
    'func' => array('fn_get_newsletter_name', '@newsletter_id'),
    'text' => 'newsletter'
);
$schema['news.update'] = array(
    'func' => array('fn_get_news_name', '@news_id'),
    'text' => 'news'
);
$schema['mailing_lists.manage'] = array(
    'text' => 'mailing_lists'
);
$schema['subscribers.manage'] = array(
    'text' => 'subscribers'
);
$schema['newsletters.campaigns'] = array(
    'text' => 'campaigns'
);

return $schema;
