<div id="products_search_{$block.block_id}">
{if $search}
    {assign var="_title" value=__("search_results")}
    {assign var="_collapse" value=true}
{else}
    {assign var="_title" value=__("advanced_search")}
    {assign var="_collapse" value=false}
{/if}

{include file="views/products/components/products_search_form.tpl" dispatch="products.search" collapse=$_collapse}

{if $search}
    {if $products}
        {assign var="title_extra" value="{__("products_found")}: `$search.total_items`"}
        {assign var="layouts" value=""|fn_get_products_views:false:0}
        {if $category_data.product_columns}
            {assign var="product_columns" value=$category_data.product_columns}
        {else}
            {assign var="product_columns" value=$settings.Appearance.columns_in_products_list}
        {/if}
        
        {if $layouts.$selected_layout.template}
            {include file="`$layouts.$selected_layout.template`" columns=$product_columns show_qty=true}
        {/if}
    {else}
        {hook name="products:search_results_no_matching_found"}
            <p class="no-items">{__("text_no_matching_products_found")}</p>
        {/hook}
    {/if}

{/if}

<!--products_search_{$block.block_id}--></div>

{hook name="products:search_results_mainbox_title"}
{capture name="mainbox_title"}<span class="float-right">{$title_extra nofilter}</span>{$_title}{/capture}
{/hook}