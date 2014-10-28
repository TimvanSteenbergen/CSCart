<{if $block.properties.item_number == "Y"}ol{else}ul{/if} class="bullets-list compact template-without-image">
{foreach from=$items item="product" name="products"}
{assign var="obj_id" value=$product.product_id}
{include file="common/product_data.tpl" product=$product}
<li>
    {assign var="form_open" value="form_open_`$obj_id`"}
    {$smarty.capture.$form_open nofilter}

    {if $product.manufacturer}<strong>{$product.manufacturer}</strong>{/if}
    {assign var="name" value="name_$obj_id"}{$smarty.capture.$name nofilter}

    <div class="margin-top">
        {assign var="old_price" value="old_price_`$obj_id`"}
        {if $smarty.capture.$old_price|trim}{$smarty.capture.$old_price nofilter}&nbsp;{/if}
        
        {assign var="price" value="price_`$obj_id`"}
        {$smarty.capture.$price nofilter}
    </div>
    
    {assign var="add_to_cart" value="add_to_cart_`$obj_id`"}
    {if $smarty.capture.$add_to_cart|trim}<p>{$smarty.capture.$add_to_cart nofilter}</p>{/if}

    {assign var="form_close" value="form_close_`$obj_id`"}
    {$smarty.capture.$form_close nofilter}
</li>
{if !$smarty.foreach.products.last}
    <ul><li class="delim">&nbsp;</li></ul>
{/if}
{/foreach}
</{if $block.properties.item_number == "Y"}ol{else}ul{/if}>