<div class="template-item-first">
{foreach from=$products item="product" name="products"}
    {assign var="obj_id" value=$product.product_id}
    {assign var="obj_id_prefix" value="`$obj_prefix``$product.product_id`"}
    {include file="common/product_data.tpl" product=$product}
    {if $smarty.foreach.products.first}
        {assign var="form_open" value="form_open_`$obj_id`"}
        {$smarty.capture.$form_open nofilter}
            <div class="compact clearfix">
                <div class="item-image product-item-image">
                    <a href="{"products.view?product_id=`$product.product_id`"|fn_url}">{include file="common/image.tpl" image_width="50" image_height="50" images=$product.main_pair obj_id=$obj_id_prefix no_ids=true}</a>
                </div>
                <div class="item-description">
                    {if $block.properties.item_number == "Y"}{$smarty.foreach.products.iteration}.&nbsp;{/if}
                    {assign var="name" value="name_$obj_id"}{$smarty.capture.$name nofilter}
                </div>
            </div>
    
            <p>
            {assign var="old_price" value="old_price_`$obj_id`"}
            {if $smarty.capture.$old_price|trim}{$smarty.capture.$old_price nofilter}&nbsp;{/if}
            
            {assign var="price" value="price_`$obj_id`"}
            {$smarty.capture.$price nofilter}
            </p>
    
            <div class="margin-bottom">
                {assign var="add_to_cart" value="add_to_cart_`$obj_id`"}
                {$smarty.capture.$add_to_cart nofilter}
            </div>
        {assign var="form_close" value="form_close_`$obj_id`"}
        {$smarty.capture.$form_close nofilter}
        <{if $block.properties.item_number == "Y"}ol start="2"{else}ul{/if} class="bullets-list compact">
    {else}
        <li>
            {assign var="name" value="name_$obj_id"}{$smarty.capture.$name nofilter}
        </li>
    {/if}
{/foreach}
</{if $block.properties.item_number == "Y"}ol{else}ul{/if}>
</div>