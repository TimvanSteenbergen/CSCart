<div class="ty-qty-discount">
    <div class="ty-qty-discount__label">{__("text_qty_discounts")}:</div>
    <table class="ty-table ty-qty-discount__table">
        <thead>
            <tr>
                <th class="ty-qty-discount__td">{__("quantity")}</th>
                {foreach from=$product.prices item="price"}
                    <th class="ty-qty-discount__td ty-center">{$price.lower_limit}+</th>
                {/foreach}
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="ty-qty-discount__td">{__("price")}</td>
                {foreach from=$product.prices item="price"}
                    <td class="ty-qty-discount__td ty-center">{include file="common/price.tpl" value=$price.price}</td>
                {/foreach}
            </tr>
        </tbody>
    </table>
</div>