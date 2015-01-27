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

namespace Tygh\SmartyEngine;

class FileResource extends \Smarty_Internal_Resource_File
{
    /**
     * Allows to override template source with addons
     * @param \Smarty_Template_Source   $source
     * @param \Smarty_Internal_Template $_template
     */
    public function populate(\Smarty_Template_Source $source, \Smarty_Internal_Template $_template=null)
    {
        $overridden_resource = fn_addon_template_overrides($source->resource, $_template);

        if ($overridden_resource != $source->resource) {
            $source->unique_resource = str_replace($source->resource, $overridden_resource, $source->unique_resource);
            $source->name = $overridden_resource;
            $source->resource = $overridden_resource;
        }

        return parent::populate($source, $_template);
    }
}
