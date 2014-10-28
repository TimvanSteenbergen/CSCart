{if $addons.rma.display_product_return_period == "Y" && $product.return_period && $product.is_returnable == "Y"}
    <div class="ty-control-group product-list-field">
        <label class="ty-control-group__label">{__("return_period")}:</label>
        <span class="ty-control-group__item">{$product.return_period}&nbsp;{__("days")}</span>
    </div>
{/if}