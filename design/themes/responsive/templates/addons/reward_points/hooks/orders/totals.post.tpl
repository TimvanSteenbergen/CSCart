{if $order_info.points_info.reward}
    <tr class="ty-orders-summary__row">
        <td><strong>{__("points")}:&nbsp;</strong></td>
        <td>{$order_info.points_info.reward}&nbsp;{__("points_lower")}</td>
    </tr>
{/if}
{if $order_info.points_info.in_use}
    <tr class="ty-orders-summary__row">
        <td><strong>{__("points_in_use")}</strong>&nbsp;({$order_info.points_info.in_use.points}&nbsp;{__("points_lower")})&nbsp;<strong>:</strong></td>
        <td>{include file="common/price.tpl" value=$order_info.points_info.in_use.cost}</td>
    </tr>
{/if}