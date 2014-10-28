{* --------- CATEGORY TREE --------------*}
{math equation="rand()" assign="rnd_value"}
{assign var="random" value=$random|default:$rnd_value}
{if $parent_id}
<div class="hidden" id="cat_{$parent_id}_{$random}">
{/if}
{foreach from=$categories_tree item=cur_cat}
{assign var="cat_id" value=$cur_cat.category_id}
{assign var="comb_id" value="cat_`$cur_cat.category_id`_`$random`"}
{assign var="title_id" value="category_`$cur_cat.category_id`"}

<table width="100%" class="table table-tree table-middle">
{if $header && !$parent_id}
{assign var="header" value=""}
<thead>
<tr>
    <th>
    {if $display != "radio"}
        {include file="common/check_items.tpl"}
    {/if}
    </th>
    <th width="84%">
        {if $show_all}
        <div class="pull-left">
            <span id="on_cat" alt="{__("expand_collapse_list")}" title="{__("expand_collapse_list")}" class="hand cm-combinations-cat {if $expand_all}hidden{/if}"><span class="exicon-expand"> </span></span>
            <span id="off_cat" alt="{__("expand_collapse_list")}" title="{__("expand_collapse_list")}" class="hand cm-combinations-cat {if !$expand_all}hidden{/if}"><span class="exicon-collapse"> </span></span>
        </div>
        {/if}
        {__("categories")}
    </th>
    {if !$runtime.company_id}
    <th class="right">{__("products")}</th>
    {/if}
</tr>
</thead>
{/if}

{if "MULTIVENDOR"|fn_allowed_for && $cur_cat.disabled}
{assign var="level" value=$cur_cat.level|default:0}
<tr class="{if $cur_cat.level > 0} multiple-table-row {/if}cm-row-status-{$category.status|lower}">
    {math equation="x*14" x=$level assign="shift"}
    <td width="{if $cur_cat.level > 0}4% {/if}">&nbsp;</td>
    <td>
        {if $cur_cat.subcategories}
            {math equation="x+10" x=$shift assign="_shift"}
        {else}
            {math equation="x+21" x=$shift assign="_shift"}
        {/if}
        <span class="nowrap" style="padding-left: {$_shift}px;">
        {if $cur_cat.has_children || $cur_cat.subcategories}
            {if $show_all}
            <span title="{__("expand_sublist_of_items")}" id="on_{$comb_id}" class="hand cm-combination-cat cm-uncheck {if isset($path.$cat_id) || $expand_all}hidden{/if}"><span class="exicon-expand"></span></span>
            {else}
            {if $except_id}
                {assign var="_except_id" value="&except_id=`$except_id`"}
            {/if}
            <span title="{__("expand_sublist_of_items")}" id="on_{$comb_id}" class="hand cm-combination-cat cm-uncheck {if (isset($path.$cat_id))}hidden{/if}" onclick="if (!$('#{$comb_id}').children().length) Tygh.$.ceAjax('request', '{"categories.picker?category_id=`$cur_cat.category_id`&random=`$random`&display=`$display`&checkbox_name=`$checkbox_name``$_except_id`"|fn_url nofilter}', {$ldelim}result_ids: '{$comb_id}'{$rdelim})"><span class="exicon-expand"> </span></span>
            {/if}
            <span title="{__("collapse_sublist_of_items")}" id="off_{$comb_id}" class="hand cm-combination-cat cm-uncheck {if !isset($path.$cat_id) && (!$expand_all || !$show_all)}hidden{/if}"><span class="exicon-collapse"></span></span>
        {/if}
        <span id="category_{$cur_cat.category_id}">{$cur_cat.category}</span>{if $cur_cat.status == "N"}&nbsp;<span class="small-note">-&nbsp;[{__("disabled")}]</span>{/if}
        </span>
    </td>
    {if !$runtime.company_id}
    <td class="right">&nbsp;</td>
    {/if}
</tr>
{else}

{assign var="level" value=$cur_cat.level|default:0}
<tr class="cm-row-status-{$category.status|lower}">
       {math equation="x*14" x=$level assign="shift"}
    <td class="left first-column" width="1%">
        {if $cur_cat.company_categories}
            &nbsp;
            {assign var="comb_id" value="comp_`$cur_cat.company_id`_`$random`"}
            {assign var="title_id" value="c_company_`$cur_cat.company_id`"}
        {else}
            {if $display == "radio"}
            <input type="radio" id="input_cat_{$cur_cat.category_id}" name="{$checkbox_name}" value="{$cur_cat.category_id}" class="cm-item" />
            {else}
            <input type="checkbox" id="input_cat_{$cur_cat.category_id}" name="{$checkbox_name}[{$cur_cat.category_id}]" value="{$cur_cat.category_id}" class="cm-item" />
            {/if}
        {/if}
    </td>
    {if $cur_cat.subcategories}
        {math equation="x+10" x=$shift assign="_shift"}
    {else}
        {math equation="x+21" x=$shift assign="_shift"}
    {/if}
        <td style="padding-left: {$_shift}px;">
            {if $cur_cat.has_children || $cur_cat.subcategories}
                {if $show_all}
                <span title="{__("expand_sublist_of_items")}" id="on_{$comb_id}" class="hand cm-combination-cat cm-uncheck {if isset($path.$cat_id) || $expand_all}hidden{/if}"><span class="exicon-expand"></span></span>
                {else}
                {if $except_id}
                    {assign var="_except_id" value="&except_id=`$except_id`"}
                {/if}
                <span title="{__("expand_sublist_of_items")}" id="on_{$comb_id}" class="hand cm-combination-cat cm-uncheck {if (isset($path.$cat_id))}hidden{/if}" onclick="if (!Tygh.$('#{$comb_id}').children().length) Tygh.$.ceAjax('request', '{"categories.picker?category_id=`$cur_cat.category_id`&random=`$random`&display=`$display`&checkbox_name=`$checkbox_name``$_except_id`"|fn_url nofilter}', {$ldelim}result_ids: '{$comb_id}'{$rdelim})"><span class="exicon-expand"></span></span>
                {/if}
                <span title="{__("collapse_sublist_of_items")}" id="off_{$comb_id}" class="hand cm-combination-cat cm-uncheck {if !isset($path.$cat_id) && (!$expand_all || !$show_all)}hidden{/if}"><span class="exicon-collapse"></span></span>
            {/if}

            {if $cur_cat.company_categories}
                <span id="{$title_id}">{$cur_cat.category}</span>
            {else}
                <label id="{$title_id}" class="inline-label" for="input_cat_{$cur_cat.category_id}">{$cur_cat.category}</label>
            {/if}
            {if $cur_cat.status == "N"}&nbsp;<span class="small-note">-&nbsp;[{__("disabled")}]</span>{/if}
        </td>
    {if !$runtime.company_id}
    <td class="right">
        {if $cur_cat.company_categories}
            &nbsp;
        {else}
            {$cur_cat.product_count}&nbsp;&nbsp;&nbsp;
        {/if}
    </td>
    {/if}
</tr>
{/if}
</table>

{if $cur_cat.has_children || $cur_cat.subcategories}
    <div{if !$expand_all} class="hidden"{/if} id="{$comb_id}">
    {if $cur_cat.subcategories}
        {include file="views/categories/components/categories_tree_simple.tpl" categories_tree=$cur_cat.subcategories parent_id=false}
    {/if}
    <!--{$comb_id}--></div>
{/if}
{/foreach}
{if $parent_id}<!--cat_{$parent_id}_{$random}--></div>{/if}
{* --------- /CATEGORY TREE --------------*}
