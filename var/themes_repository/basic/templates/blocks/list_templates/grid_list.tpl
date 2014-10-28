{if $products}

{script src="js/tygh/exceptions.js"}

{if !$no_pagination}
    {include file="common/pagination.tpl"}
{/if}

{if !$no_sorting}
    {include file="views/products/components/sorting.tpl"}
{/if}

{if !$show_empty}
{if $products|sizeof < $columns}
    {assign var="columns" value=$products|@sizeof}
{/if}
{split data=$products size=$columns|default:"2" assign="splitted_products"}
{else}
{split data=$products size=$columns|default:"2" assign="splitted_products" skip_complete=true}
{/if}

{math equation="100 / x" x=$columns|default:"2" assign="cell_width"}
{if $item_number == "Y"}
    {assign var="cur_number" value=1}
{/if}

{script src="js/tygh/product_image_gallery.js"}

{if $settings.Appearance.enable_quick_view == 'Y'}
{$quick_nav_ids = $products|fn_fields_from_multi_level:"product_id":"product_id"}
{/if}
<table class="fixed-layout multicolumns-list table-width">
{foreach from=$splitted_products item="sproducts" name="sprod"}
<tr{if !$smarty.foreach.sprod.last} class="row-border"{/if}>
{foreach from=$sproducts item="product" name="sproducts"}
    <td class="product-spacer">&nbsp;</td>
    <td class="product-cell valign-top" style="width: {$cell_width}%">
    {if $product}
        {assign var="obj_id" value=$product.product_id}
        {assign var="obj_id_prefix" value="`$obj_prefix``$product.product_id`"}
        {include file="common/product_data.tpl" product=$product}

        <div class="product-cell-wrapper">
    
        {assign var="form_open" value="form_open_`$obj_id`"}
        {$smarty.capture.$form_open nofilter}
        {hook name="products:product_multicolumns_list"}
        <table class="center-block">
        <tr class="valign-top">
            <td class="preview-image">
                <div class="preview-image-wrapper">
                    {include file="views/products/components/product_icon.tpl" product=$product show_gallery=true}

                    {assign var="discount_label" value="discount_label_`$obj_prefix``$obj_id`"}
                    {$smarty.capture.$discount_label nofilter}

                </div>
            </td>
        </tr>
        <tr>
            <td class="product-title-wrap">
                {if $item_number == "Y"}<span class="item-number">{$cur_number}.&nbsp;</span>{math equation="num + 1" num=$cur_number assign="cur_number"}{/if}
                {assign var="name" value="name_$obj_id"}{$smarty.capture.$name nofilter}

                {assign var="rating" value="rating_$obj_id"}
                {$smarty.capture.$rating nofilter}

                <div class="prices">
                    {assign var="old_price" value="old_price_`$obj_id`"}
                    {if $smarty.capture.$old_price|trim}{$smarty.capture.$old_price nofilter}{/if}

                    {assign var="price" value="price_`$obj_id`"}
                    {$smarty.capture.$price nofilter}

                    {assign var="clean_price" value="clean_price_`$obj_id`"}
                    {$smarty.capture.$clean_price nofilter}

                    {assign var="list_discount" value="list_discount_`$obj_id`"}
                    {$smarty.capture.$list_discount nofilter}
                </div>

            </td>
        </tr>
        <tr>
            <td class="product-description">
                {if $settings.Appearance.enable_quick_view == 'Y'}
                    {include file="views/products/components/quick_view_link.tpl" quick_nav_ids=$quick_nav_ids}
                {/if}

                {if $show_add_to_cart}
                <div class="buttons-container-item">
                    {assign var="add_to_cart" value="add_to_cart_`$obj_id`"}
                    {$smarty.capture.$add_to_cart nofilter}
                </div>
                {/if}
            </td>
        </tr>
        </table>
        {/hook}
        {assign var="form_close" value="form_close_`$obj_id`"}
        {$smarty.capture.$form_close nofilter}

        </div>
    {/if}
    </td>
    <td class="product-spacer">&nbsp;</td>
{/foreach}
{if $show_empty && $smarty.foreach.sprod.last}
    {assign var="iteration" value=$smarty.foreach.sproducts.iteration}
    {capture name="iteration"}{$iteration}{/capture}
    {hook name="products:products_multicolumns_extra"}
    {/hook}
    {assign var="iteration" value=$smarty.capture.iteration}
    {if $iteration % $columns != 0} 
        {math assign="empty_count" equation="c - it%c" it=$iteration c=$columns}
        {section loop=$empty_count name="empty_rows"}
            <td class="product-spacer">&nbsp;</td>
            <td class="product-cell product-cell-empty valign-top" style="width: {$cell_width}%">
                <div>
                    <p>{__("empty")}</p>
                </div>
            </td>
            <td class="product-spacer">&nbsp;</td>
        {/section}
    {/if}
{/if}
</tr>
{/foreach}
</table>

{if !$no_pagination}
    {include file="common/pagination.tpl"}
{/if}

{/if}

{capture name="mainbox_title"}{$title}{/capture}