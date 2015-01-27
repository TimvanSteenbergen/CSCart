<!-- Hook Reward points start -->
{if $order_info.points_info.price && $oi}
<p><strong>{__("price_in_points")}:</strong> {$oi.extra.points_info.price}</p>
{/if}
<!-- Hook Reward points end -->