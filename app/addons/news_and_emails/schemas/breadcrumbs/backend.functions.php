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

/**
 * Gets newsletters link
 *
 * @param int $newsletter_id Newsletter identifier
 * @param string $type Newsletter type
 * @return array Breadcrumb link data
 */
function fn_br_newsletters_link($newsletter_id, $type)
{
    if (empty($type) && !empty($newsletter_id)) {
        $data = fn_get_newsletter_data($newsletter_id);
        $type = !empty($data['type']) ? $data['type'] : '';
    }

    if ($type == NEWSLETTER_TYPE_AUTORESPONDER) {
        $object_name = __('autoresponders');
    } elseif ($type == NEWSLETTER_TYPE_TEMPLATE) {
        $object_name = __('templates');
    } else {
        $object_name = __('newsletters');
    }

    $result = array(
        'title' => $object_name,
        'link' => "newsletters.manage?type=$type"
    );

    return $result;
}
