{if $order_info.points_info.reward}
    <tr>
        <td>{__("points")}:</td>
        <td>{$order_info.points_info.reward}&nbsp;{__("points_lower")}</td>
    </tr>
{/if}

{if $order_info.points_info.in_use}
    <tr>
        <td>{__("points_in_use")}&nbsp;({$order_info.points_info.in_use.points}&nbsp;{__("points_lower")}):</td>
        <td>{include file="common/price.tpl" value=$order_info.points_info.in_use.cost}</td>
    </tr>
{/if}