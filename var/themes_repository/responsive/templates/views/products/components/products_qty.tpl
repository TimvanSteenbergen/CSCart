{if $product.max_qty}
<div class="ty-control-group product-list-field">
    {if ($product.min_qty)}
        <label class="ty-control-group__label">{__("allow_qty")}:</label>
        <span id="product_min_max_qty_{$product.product_id}">
        {$product.min_qty}&nbsp;-&nbsp;{$product.max_qty}&nbsp;{__("items")}
    {else}
        <label class="ty-control-group__label">{__("allow_qty")}:</label>
        <span id="product_min_max_qty_{$product.product_id}">
        {$product.quantity}&nbsp;{$product.max_qty}&nbsp;{__("qty_at_most")}&nbsp;{__("items")}
    {/if}
    </span>
</div>
{/if}