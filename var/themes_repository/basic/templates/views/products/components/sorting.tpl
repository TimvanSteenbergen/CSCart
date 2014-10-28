<div class="sort-container">
{if !$config.tweaks.disable_dhtml}
    {assign var="ajax_class" value="cm-ajax"}
{/if}

{assign var="curl" value=$config.current_url|fn_query_remove:"sort_by":"sort_order":"result_ids":"layout"}
{assign var="sorting" value=""|fn_get_products_sorting}
{assign var="sorting_orders" value=""|fn_get_products_sorting_orders}
{assign var="layouts" value=""|fn_get_products_views:false:false}
{assign var="pagination_id" value=$id|default:"pagination_contents"}
{assign var="avail_sorting" value=$settings.Appearance.available_product_list_sortings}

{if $search.sort_order_rev == "asc"}
    {capture name="sorting_text"}
        <a>{$sorting[$search.sort_by].description}<i class="icon-up-dir"></i></a>
    {/capture}
{else}
    {capture name="sorting_text"}
        <a>{$sorting[$search.sort_by].description}<i class="icon-down-dir"></i></a>
    {/capture}
{/if}

{if !(($category_data.selected_layouts|count == 1) || ($category_data.selected_layouts|count == 0 && ""|fn_get_products_views:true|count <= 1)) && !$hide_layouts}
<div class="views-icons">
{foreach from=$layouts key="layout" item="item"}
{if ($category_data.selected_layouts.$layout) || (!$category_data.selected_layouts && $item.active)}
{if $layout == $selected_layout}
{$sort_order = $search.sort_order_rev}
{else}
{$sort_order = $search.sort_order}
{/if}
<a class="{$ajax_class} {if $layout == $selected_layout}active{/if}" data-ca-target-id="{$pagination_id}" href="{"`$curl`&sort_by=`$search.sort_by`&sort_order=`$sort_order`&layout=`$layout`"|fn_url}" rel="nofollow"><i class="icon-{$layout|replace:"_":"-"}"></i></a>
{/if}
{/foreach}
</div>
{/if}

{if $avail_sorting}
{include file="common/sorting.tpl"}
{/if}

{assign var="pagination" value=$search|fn_generate_pagination}

{if $pagination.total_items}
{assign var="range_url" value=$curl|fn_query_remove:"items_per_page":"page"}
{assign var="product_steps" value=$settings.Appearance.columns_in_products_list|fn_get_product_pagination_steps:$settings.Appearance.products_per_page}
<div class="dropdown-container">
<span id="sw_elm_pagination_steps" class="sort-dropdown cm-combination"><a>{$pagination.items_per_page} {__("per_page")}<i class="icon-down-micro"></i></a></span>
    <ul id="elm_pagination_steps" class="dropdown-content cm-popup-box hidden">
        {foreach from=$product_steps item="step"}
        {if $step != $pagination.items_per_page}
            <li><a class="{$ajax_class}" href="{"`$range_url`&items_per_page=`$step`"|fn_url}" data-ca-target-id="{$pagination_id}" rel="nofollow">{$step} {__("per_page")}</a></li>
        {/if}
        {/foreach}
    </ul>
</div>
{/if}
</div>