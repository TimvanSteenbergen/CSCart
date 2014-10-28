{if $order_info.points_info.price && $product}
    <div class="product-list-field">
        <label>{__("price_in_points")}:</label>
        <span>{$product.extra.points_info.price}</span>
    </div>
{/if}