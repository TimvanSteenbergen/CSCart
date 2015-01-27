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

<table class="ty-categories-picker">
{if $header && !$parent_id}
{assign var="header" value=""}
<tr>
    <th class="ty-categories-picker__title ty-center" style="width: 20px;">
    {if $display != "radio"}
        <input type="checkbox" name="check_all" value="Y" title="{__("check_uncheck_all")}" class="checkbox cm-check-items" />
    {/if}
    </th>
    <th class="ty-categories-picker__title" style="width: 97%">
        {if $show_all}
        <div class="ty-float-left">
            <i id="on_cat" title="{__("expand_collapse_list")}" class="ty-icon-down-dir ty-dir-list cm-combinations {if $expand_all}hidden{/if}"></i>
            <i id="off_cat" title="{__("expand_collapse_list")}" class="ty-icon-right-dir ty-dir-list cm-combinations {if !$expand_all}hidden{/if}"></i>
        </div>
        {/if}
        &nbsp;{__("categories")}
    </th>
</tr>
{/if}
<tr>
       {math equation="x*14" x=$level assign="shift"}
    <td class="ty-categories-picker__item ty-center" style="width: 20px;">
        {if $display == "radio"}
        <input type="radio" name="{$checkbox_name}" value="{$cur_cat.category_id}" class="radio cm-item" />
        {else}
        <input type="checkbox" name="{$checkbox_name}[{$cur_cat.category_id}]" value="{$cur_cat.category_id}" class="checkbox cm-item" />
        {/if}
    </td>
    <td class="ty-categories-picker__item" style="width: 97%;">
        {if $cur_cat.subcategories}
            {math equation="x+10" x=$shift assign="_shift"}
        {else}
            {math equation="x+10" x=$shift assign="_shift"}
        {/if}

        {capture name="category_name"}
            <span id="{$title_id}" {if $cur_cat.has_children || $cur_cat.subcategories}class="ty-strong"{/if}>{$cur_cat.category}</span>{if $cur_cat.status == "N"}&nbsp;<span class="small-note">-&nbsp;[{__("disabled")}]</span>{/if}
        {/capture}

        <div class="g__item">
            <span style="padding-left: {$_shift}px;">
                {if $cur_cat.has_children || $cur_cat.subcategories}
                    {if $show_all}
                    <span id="on_{$comb_id}" class="cm-combination {if isset($path.$cat_id) || $expand_all}hidden{/if}">
                        <i class="ty-icon-right-dir ty-dir-list" title="{__("expand_sublist_of_items")}"></i>
                        {$smarty.capture.category_name nofilter}
                    </span>
                    {else}
                    <span id="on_{$comb_id}" class="cm-combination {if (isset($path.$cat_id))}hidden{/if}" onclick="if (!Tygh.$('#{$title_id}').children().get(0)) Tygh.$.ceAjax('request', '{"categories.picker?category_id=`$cur_cat.category_id`&display=`$display`"|fn_url}', {$ldelim}result_ids: '{$comb_id}'{$rdelim})">
                        <i class="ty-icon-right-dir ty-dir-list" title="{__("expand_sublist_of_items")}"></i>
                        {$smarty.capture.category_name nofilter}
                    </span>
                    {/if}
                    <span id="off_{$comb_id}" class="cm-combination {if !isset($path.$cat_id) && (!$expand_all || !$show_all)}hidden{/if}">
                        <i class="ty-icon-down-dir ty-dir-list" title="{__("collapse_sublist_of_items")}"></i>
                        {$smarty.capture.category_name nofilter}
                    </span>
                {else}
                    {$smarty.capture.category_name nofilter}
                {/if}
            </span>
        </div>
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
