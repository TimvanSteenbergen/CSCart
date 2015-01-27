{if $parent_id}
<div class="hidden" id="cat_{$parent_id}">
{/if}
{foreach from=$categories_tree item=category}
    {assign var="comb_id" value="cat_`$category.category_id`"}
    <table class="table table-tree table-middle">
        {if $header && !$parent_id}
            {assign var="header" value=""}
            <thead>
            <tr>
                <th width="5%">{include file="common/check_items.tpl" check_statuses=''|fn_get_default_status_filters:true}</th>
                <th width="8%">{__("position_short")}</th>
                <th width="54%">
                    {if $show_all && !$smarty.request.b_id}
                        <div class="pull-left">
                            <span alt="{__("expand_collapse_list")}" title="{__("expand_collapse_list")}" id="on_cat" class="cm-combinations{if $expand_all} hidden{/if}"><span class="exicon-expand"> </span></span>
                            <span alt="{__("expand_collapse_list")}" title="{__("expand_collapse_list")}" id="off_cat" class="cm-combinations{if !$expand_all} hidden{/if}"><span class="exicon-collapse"> </span></span>
                        </div>
                    {/if}
                    &nbsp;{__("name")}
                </th>
                <th width="12%" class="center">{__("products")}</th>
                <th width="5%" class="center">&nbsp;</th>
                <th width="10%" class="right">{__("status")}</th>
            </tr>
            </thead>
        {/if}
    
        {if "MULTIVENDOR"|fn_allowed_for && $category.disabled}
            <tbody>
            <tr class="{if $category.level > 0}multiple-table-row {/if}cm-row-status-{$category.status|lower}">
                {math equation="x*14" x=$category.level|default:"0" assign="shift"}
                <td width="5%">&nbsp;</td>
                <td width="8%">&nbsp;</td>
                <td width="54%">
                    {strip}
                        <span style="padding-left: {$shift}px;">
                        {if $category.has_children || $category.subcategories}
                            {if $show_all}
                                <span alt="{__("expand_sublist_of_items")}" title="{__("expand_sublist_of_items")}" id="on_{$comb_id}" class="cm-combination {if $expand_all}hidden{/if}" /><span class="exicon-expand"> </span></span>
                                {else}
                                <span alt="{__("expand_sublist_of_items")}" title="{__("expand_sublist_of_items")}" id="on_{$comb_id}" class="exicon-collapse cm-combination" onclick="if (!Tygh.$('#{$comb_id}').children().get(0)) Tygh.$.ceAjax('request', '{"categories.manage?category_id=`$category.category_id`"|fn_url nofilter}', {$ldelim}result_ids: '{$comb_id}'{$rdelim})"><span class="exicon-expand"> </span></span>
                            {/if}
                            <span alt="{__("collapse_sublist_of_items")}" title="{__("collapse_sublist_of_items")}" id="off_{$comb_id}" class="cm-combination{if !$expand_all || !$show_all} hidden{/if}"><span class="exicon-collapse"></span></span>
                        {/if}
                        {$category.category}{if $category.status == "N"}&nbsp;<span class="small-note">-&nbsp;[{__("disabled")}]</span>{/if}
                        </span>
                    {/strip}
                </td>
                <td width="12%" class="center">&nbsp;</td>
                <td width="5%" class="center">&nbsp;</td>
                <td width="10%" class="right">&nbsp;</td>
            </tr>
            </tbody>
            {else}
    
        <tr  class="{if $category.level > 0}multiple-table-row {/if}cm-row-status-{$category.status|lower}">
            {math equation="x*14" x=$category.level|default:"0" assign="shift"}
            {if $category.company_categories}
                {assign var="comb_id" value="comp_`$category.company_id`"}
                <td width="5%">
                    &nbsp;</td>
                <td width="8%">
                    &nbsp;</td>
                <td width="54%">
                    {strip}
                        <span style="padding-left: {$shift}px;">
                    {if $show_all}
                        <span alt="{__("expand_sublist_of_items")}" title="{__("expand_sublist_of_items")}" id="on_{$comb_id}" class="cm-combination {if $expand_all}hidden{/if}"><span class="exicon-expand"></span> </span>
                        {else}
                        <span alt="{__("expand_sublist_of_items")}" title="{__("expand_sublist_of_items")}" id="on_{$comb_id}" class="cm-combination" onclick="if (!Tygh.$('#{$comb_id}').children().get(0)) Tygh.$.ceAjax('request', '{"categories.manage?category_id=`$category.category_id`"|fn_url nofilter}', {$ldelim}result_ids: '{$comb_id}'{$rdelim})"> <span class="exicon-expand"></span></span>
                    {/if}
                            <span alt="{__("collapse_sublist_of_items")}" title="{__("collapse_sublist_of_items")}" id="off_{$comb_id}" class="cm-combination{if !$expand_all || !$show_all} hidden{/if}"><span class="exicon-collapse"></span></span>
                <span class="row-status">{$category.category}</span>
            </span>
                    {/strip}
                </td>
                <td width="12%" class="center">
                    &nbsp;</td>
                <td width="10%" class="center">
                    &nbsp;</td>
                <td width="10%" class="right">
                    &nbsp;</td>
                {else}
                <td width="5%">
                    <input type="checkbox" name="category_ids[]" value="{$category.category_id}" class="checkbox cm-item  cm-item-status-{$category.status|lower}" /></td>
                <td width="8%">
                    <input type="text" name="categories_data[{$category.category_id}][position]" value="{$category.position}" size="3" class="input-micro input-hidden" /></td>
            <td width="54%">
                {strip}
            <span style="padding-left: {$shift}px;">
                {if $category.has_children || $category.subcategories}
                    {if $show_all}
                    <span alt="{__("expand_sublist_of_items")}" title="{__("expand_sublist_of_items")}" id="on_cat_{$category.category_id}" class="cm-combination {if $expand_all}hidden{/if}" ><span class="exicon-expand"> </span></span>
                    {else}
                    <span alt="{__("expand_sublist_of_items")}" title="{__("expand_sublist_of_items")}" id="on_cat_{$category.category_id}" class="cm-combination" onclick="if (!Tygh.$('#cat_{$category.category_id}').children().get(0)) Tygh.$.ceAjax('request', '{"categories.manage?category_id=`$category.category_id`"|fn_url nofilter}', {$ldelim}result_ids: 'cat_{$category.category_id}'{$rdelim})"><span class="exicon-expand"> </span></span>
                {/if}
                <span alt="{__("collapse_sublist_of_items")}" title="{__("collapse_sublist_of_items")}" id="off_cat_{$category.category_id}" class="cm-combination{if !$expand_all || !$show_all} hidden{/if}" ><span class="exicon-collapse"> </span></span>
            {/if}
            <a class="row-status {if $category.status == "N"} manage-root-item-disabled{/if}{if !$category.subcategories} normal{/if}" href="{"categories.update?category_id=`$category.category_id`"|fn_url}"{if !$category.subcategories} style="padding-left: 14px;"{/if} >{$category.category}</a>{if $category.status == "N"}&nbsp;<span class="small-note">-&nbsp;[{__("disabled")}]</span>{/if}
        </span>
        {/strip}
        </td>
            <td width="12%" class="center">
                <a href="{"products.manage?cid=`$category.category_id`"|fn_url}" class="badge">{$category.product_count}</a>
            </td>
            <td width="10%" class="center">
                <div class="hidden-tools">
                    {capture name="tools_items"}
                        <li>{btn type="list" text=__("add_product") href="products.add?category_id=`$category.category_id`"}</li>
                        {if !$hide_inputs}
                        <li class="divider"></li>
                        {/if}
                        <li>{btn type="list" text=__("edit") href="categories.update?category_id=`$category.category_id`"}</li>
                        <li>{btn type="list" class="cm-confirm" data=["data-ca-confirm-text" => "{__("category_deletion_side_effects")}"] text=__("delete") href="categories.delete?category_id=`$category.category_id`"}</li>
                    {/capture}
                    {dropdown content=$smarty.capture.tools_items}
                </div>
            </td>
            <td width="10%" class="nowrap right">
            {include file="common/select_popup.tpl" popup_additional_class="dropleft" id=$category.category_id status=$category.status hidden=true object_id_name="category_id" table="categories" non_editable=$hide_inputs}
            </td>
        {/if}
    </tr>
    {/if}
    </table>
    {if $category.has_children || $category.subcategories}
        <div class="{if !$expand_all} hidden{/if}" id="{$comb_id}">
            {if $category.subcategories}
            {include file="views/categories/components/categories_tree.tpl" categories_tree=$category.subcategories parent_id=false}
            {/if}
            <!--{$comb_id}--></div>
    {/if}
{/foreach}
    {if $parent_id}<!--cat_{$parent_id}--></div>{/if}