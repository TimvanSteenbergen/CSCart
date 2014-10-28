<ul>
{foreach from=$categories_tree item=category}
    {$shift = 14 * $category.level|default:0}
    {capture name="category_subtitle"}
        {if $category.company_categories}
            {$expanded = $category.company_id == $category_data.company_id}
            {$comb_id = "comp_`$category.company_id`"}
            <strong class="row-status">{$category.category}</strong>
        {else}
            {$expanded = $category.category_id|in_array:$runtime.active_category_ids}
            {$comb_id = "cat_`$category.category_id`"}
            {if "MULTIVENDOR"|fn_allowed_for && $category.disabled}
                {$category.category}
            {else}
                <a class="row-status {if $category.status == "N"} manage-root-item-disabled{/if}{if !$category.subcategories} normal{/if}" href="{"categories.update?category_id=`$category.category_id`"|fn_url}"{if !$category.subcategories} style="padding-left: 14px;"{/if} >{$category.category}</a>
            {/if}
        {/if}
    {/capture}
    <li {if $category.active}class="active"{/if} style="padding-left: {$shift}px;">
    {strip}
        <div class="link">
            {if $category.subcategories}
                <span alt="{__("expand_sublist_of_items")}" title="{__("expand_sublist_of_items")}" id="on_{$comb_id}" class="cm-combination{if $expanded} hidden{/if}" ><span class="exicon-expand"> </span></span>
                <span alt="{__("collapse_sublist_of_items")}" title="{__("collapse_sublist_of_items")}" id="off_{$comb_id}" class="cm-combination{if !$expanded} hidden{/if}" ><span class="exicon-collapse"> </span></span>
            {/if}
            {$smarty.capture.category_subtitle nofilter}
        </div>
    {/strip}
    </li>
    {if $category.subcategories}
        <li class="{if !$expanded} hidden{/if}" id="{$comb_id}">
            {if $category.subcategories}
            {include file="views/categories/components/categories_links_tree.tpl" categories_tree=$category.subcategories}
            {/if}
        <!--{$comb_id}--></li>
    {/if}
{/foreach}
</ul>