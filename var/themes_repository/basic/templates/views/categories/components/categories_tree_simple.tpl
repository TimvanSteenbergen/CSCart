{* --------- CATEGORY TREE --------------*}
{if $parent_id}
<div class="hidden" id="cat_{$parent_id}">
{math equation="x+1" x=$level assign="level"}
{/if}
{foreach from=$categories_tree item=cur_cat}
{assign var="cat_id" value=$cur_cat.category_id}
{assign var="comb_id" value="cat_`$cur_cat.category_id`"}
{assign var="title_id" value="category_`$cur_cat.category_id`"}

{if $cur_cat.company_categories}
    {assign var="comb_id" value="comp_`$cur_cat.company_id`_`$random`"}
    {assign var="title_id" value="c_company_`$cur_cat.company_id`"}
{/if}

<table class="table categories-picker table-width">
{if $header && !$parent_id}
{assign var="header" value=""}
<tr>
    <th class="center" style="width: 20px;">
    {if $display != "radio"}
        <input type="checkbox" name="check_all" value="Y" title="{__("check_uncheck_all")}" class="checkbox cm-check-items" />
    {/if}
    </th>
    <th style="width: 97%">
        {if $show_all}
        <div class="float-left">
            <i id="on_cat" title="{__("expand_collapse_list")}" class="icon-down-dir dir-list cm-combinations {if $expand_all}hidden{/if}"></i>
            <i id="off_cat" title="{__("expand_collapse_list")}" class="icon-right-dir dir-list cm-combinations {if !$expand_all}hidden{/if}"></i>
        </div>
        {/if}
        &nbsp;{__("categories")}
    </th>
</tr>
{/if}
<tr {if $level == "0"}class="table-row"{/if}>
       {math equation="x*14" x=$level assign="shift"}
    <td class="center" style="width: 20px;">
        {if $display == "radio"}
        <input type="radio" name="{$checkbox_name}" value="{$cur_cat.category_id}" class="radio cm-item" />
        {else}
        <input type="checkbox" name="{$checkbox_name}[{$cur_cat.category_id}]" value="{$cur_cat.category_id}" class="checkbox cm-item" />
        {/if}
    </td>
    <td style="width: 97%;">
        {if $cur_cat.subcategories}
            {math equation="x+10" x=$shift assign="_shift"}
        {else}
            {math equation="x+10" x=$shift assign="_shift"}
        {/if}
        <table class="table-width">
        <tr>
            <td class="nowrap" style="padding-left: {$_shift}px;">
                {if $cur_cat.has_children || $cur_cat.subcategories}
                    {if $show_all}
                    <i id="on_{$comb_id}" class="icon-right-dir dir-list cm-combination {if isset($path.$cat_id) || $expand_all}hidden{/if}" title="{__("expand_sublist_of_items")}"></i>
                    {else}
                    <i id="on_{$comb_id}" class="icon-right-dir dir-list cm-combination {if (isset($path.$cat_id))}hidden{/if}" onclick="if (!Tygh.$('#{$title_id}').children().get(0)) Tygh.$.ceAjax('request', '{"categories.picker?category_id=`$cur_cat.category_id`&display=`$display`"|fn_url}', {$ldelim}result_ids: '{$comb_id}'{$rdelim})" title="{__("expand_sublist_of_items")}"></i>
                    {/if}
                    <i id="off_{$comb_id}" class="icon-down-dir dir-list cm-combination {if !isset($path.$cat_id) && (!$expand_all || !$show_all)}hidden{/if}" title="{__("collapse_sublist_of_items")}"></i>
                {else}
                    <span class="tree-space"></span>
                {/if}</td>
            <td style="width: 100%;">
                <span id="{$title_id}" {if $cur_cat.has_children || $cur_cat.subcategories}class="strong"{/if}>{$cur_cat.category}</span>{if $cur_cat.status == "N"}&nbsp;<span class="small-note">-&nbsp;[{__("disabled")}]</span>{/if}
            </td>
        </tr>
        </table>
    </td>
</tr>
</table>

{if $cur_cat.has_children || $cur_cat.subcategories}
    <div {if !$expand_all}class="hidden"{/if} id="{$comb_id}">
    {if $cur_cat.subcategories}
        {math equation="x+1" x=$level assign="level"}
        {include file="views/categories/components/categories_tree_simple.tpl" categories_tree=$cur_cat.subcategories parent_id=false}
        {math equation="x-1" x=$level assign="level"}
    {/if}
    <!--{$comb_id}--></div>
{/if}
{/foreach}
{if $parent_id}<!--cat_{$parent_id}--></div>{/if}
{* --------- /CATEGORY TREE --------------*}
