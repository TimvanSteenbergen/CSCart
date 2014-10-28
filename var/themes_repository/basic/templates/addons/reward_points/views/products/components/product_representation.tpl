{if $product.points_info.price}
    <div class="control-group{if !$capture_options_vs_qty} product-list-field{/if}">
        <label>{__("price_in_points")}:</label>
        <span id="price_in_points_{$obj_prefix}{$obj_id}">{$product.points_info.price}&nbsp;{__("points_lower")}</span>
    </div>
{/if}
<div class="control-group product-list-field{if !$product.points_info.reward.amount} hidden{/if}">
    <label>{__("reward_points")}:</label>
    <span id="reward_points_{$obj_prefix}{$obj_id}" >{$product.points_info.reward.amount}&nbsp;{__("points_lower")}</span>
</div>