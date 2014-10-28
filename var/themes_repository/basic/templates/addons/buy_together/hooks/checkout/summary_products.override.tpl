{if $cart.products.$key.extra.buy_together}
{if $smarty.capture.prods}
    <hr class="dark-hr" />
{else}
    {capture name="prods"}Y{/capture}
{/if}
<div class="clearfix">
    <a href="{"products.view?product_id=`$product.product_id`"|fn_url}" class="product-title">{$product.product nofilter}</a>
    <p class="step-complete-wrapper">{__("sku")}: {$product.product_code}</p>
    {include file="common/options_info.tpl" product_options=$product.product_options no_block=true}

    {foreach from=$cart_products item="_product" key="key_conf"}
        {if $cart.products.$key_conf.extra.parent.buy_together == $key}
            {capture name="is_conf_prod"}1{/capture}
        {/if}
    {/foreach}

    {if $smarty.capture.is_conf_prod}
        <p><strong>{__("buy_together")}:</strong></p>
        
        <table style="width: 85%" class="table margin-top">
        <tr>
            <th style="width: 50%">{__("product")}</th>
            <th style="width: 10%">{__("price")}</th>
            <th style="width: 10%">{__("quantity")}</th>
            <th class="right" style="width: 10%">{__("subtotal")}</th>
        </tr>
        {foreach from=$cart_products item="_product" key="key_conf"}
        {if $cart.products.$key_conf.extra.parent.buy_together == $key}
        <tr {cycle values=",class=\"table-row\""}>
            <td>
                <a href="{"products.view?product_id=`$_product.product_id`"|fn_url}" class="underlined">{$_product.product}</a><br />
                {if $_product.product_options}
                    {foreach from=$_product.product_options item="option"}
                        <strong>{$option.option_name}</strong>:&nbsp;
                        {if $option.option_type == "F"}
                            {if $_product.extra.custom_files[$option.option_id]}
                                {foreach from=$_product.extra.custom_files[$option.option_id] key="file_id" item="file" name="po_files"}
                                    <a class="cm-no-ajax" href="{"checkout.get_custom_file?cart_id=`$key_conf`&file=`$file_id`&option_id=`$option.option_id`"|fn_url}">{$file.name}</a>
                                    {if !$smarty.foreach.po_files.last},&nbsp;{/if}
                                {/foreach}
                            {/if}
                        {else}
                            {$option.variants[$option.value].variant_name}
                        {/if}
                        <br />
                    {/foreach}
                {/if}
            </td>
            <td class="center">
                {include file="common/price.tpl" value=$_product.price}</td>
            <td class="center">
                <input type="hidden" name="cart_products[{$key_conf}][product_id]" value="{$_product.product_id}" />
                {$_product.amount}
            </td>
            <td class="right">
                {include file="common/price.tpl" value=$_product.display_subtotal}</td>
        </tr>
        {/if}
        {/foreach}
        <tr class="table-footer">
            <td colspan="4">&nbsp;</td>
        </tr>
        </table>
    {/if}
</div>
{/if}