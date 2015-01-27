{if $addons.rma.display_product_return_period == "Y" && $product.return_period && $product.is_returnable == "Y"}
    <div class="control-group{if !$capture_options_vs_qty} product-list-field{/if}">
        <label>{__("return_period")}:</label>
        <span class="valign">{$product.return_period}&nbsp;{__("days")}</span>
    </div>
{/if}