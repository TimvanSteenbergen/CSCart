<div class="qty-discounts-wrap">
    <p class="qty-discounts-label">{__("text_qty_discounts")}:</p>
    
    <table class="table qty-discounts">
    <tr>
        <td class="left valign">{__("quantity")}</td>
        {foreach from=$product.prices item="price"}
            <td class="center">&nbsp;{$price.lower_limit}+&nbsp;</td>
        {/foreach}
    </tr>
    <tr>
        <td class="left valign">{__("price")}</td>
        {foreach from=$product.prices item="price"}
            <td class="center">&nbsp;{include file="common/price.tpl" value=$price.price}&nbsp;</td>
        {/foreach}
    </tr>
    </table>
</div>