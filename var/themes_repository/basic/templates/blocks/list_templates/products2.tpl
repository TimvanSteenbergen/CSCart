{** block-description:products2 **}

{if $columns > $items|count}
    {assign var="columns" value=$items|count}
{/if}

{split data=$products size=$columns assign="splitted_products"}
{math equation="floor(100/x)" x=$columns assign="cell_width"}

{if $block.properties.item_number == "Y"}
    {assign var="cur_number" value=1}
{/if}
<table class="products2-table">
{foreach from=$splitted_products item="sproducts" name="splitted_products"}
<tr>
    <td class="lm-left">&nbsp;</td>
    {foreach from=$sproducts item="product" name="sproducts"}
    {if $product}
        {assign var="obj_id" value=$product.product_id}
        {assign var="obj_id_prefix" value="`$block.block_id`000`$product.product_id`"}
        {capture name=$obj_id}
            {include file="buttons/button.tpl" but_role="text" but_href="products.view?product_id=`$product.product_id`" but_text=__("more_info")}
        {/capture}
        {include file="common/product_data.tpl" product=$product extra_button=$smarty.capture.$obj_id}
        <td style="width: {if $items|count > 1}{$cell_width}{else}100{/if}%" class="lm-center image-border compact left valign-top">
            <div class="products-2">
            {assign var="form_open" value="form_open_`$obj_id`"}
            {$smarty.capture.$form_open nofilter}
                <div class="clearfix">
                    <div class="float-right">
                        <a href="{"products.view?product_id=`$product.product_id`"|fn_url}">{include file="common/image.tpl" image_width="70" image_height="70" images=$product.main_pair obj_id=$obj_id_prefix}</a>
                    </div>
                    
                    {if $block.properties.item_number == "Y"}{$cur_number}.&nbsp;{math equation="num + 1" num=$cur_number assign="cur_number"}{/if}
                    {assign var="name" value="name_$obj_id"}{$smarty.capture.$name nofilter}
                    
                    <p>
                        {assign var="old_price" value="old_price_`$obj_id`"}
                        {if $smarty.capture.$old_price|trim}{$smarty.capture.$old_price nofilter}&nbsp;{/if}
                        
                        {assign var="price" value="price_`$obj_id`"}
                        {$smarty.capture.$price nofilter}
                    </p>
                </div>
                {if $hide_add_to_cart_button != 'Y'}
                    <div class="buttons-container-item">
                    {assign var="add_to_cart" value="add_to_cart_`$obj_id`"}
                    {$smarty.capture.$add_to_cart nofilter}
                    </div>
                {/if}
            {assign var="form_close" value="form_close_`$obj_id`"}
            {$smarty.capture.$form_close nofilter}
            </div>
        </td>
        {if !$smarty.foreach.sproducts.last}
            <td class="delimiter"></td>
        {/if}
    {/if}
    {/foreach}
    <td class="lm-left">&nbsp;</td>
</tr>
{/foreach}
</table>
