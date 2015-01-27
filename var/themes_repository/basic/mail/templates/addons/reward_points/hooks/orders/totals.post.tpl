{if  $order_info.points_info.reward}
    <tr>
        <td style="text-align: right; white-space: nowrap; font-size: 12px; font-family: Arial;"><b>{__("reward_points")}:&nbsp;</b></td>
        <td style="text-align: right; white-space: nowrap; font-size: 12px; font-family: Arial;">{$order_info.points_info.reward}</td>
    </tr>
{/if}

{if $order_info.points_info.in_use}
    <tr>
        <td style="text-align: right; white-space: nowrap; font-size: 12px; font-family: Arial;"><b>{__("points_in_use")}</b>&nbsp;({$order_info.points_info.in_use.points}&nbsp;{__("points_lower")})<b>:</b>&nbsp;</td>
        <td style="text-align: right; white-space: nowrap; font-size: 12px; font-family: Arial;">{include file="common/price.tpl" value=$order_info.points_info.in_use.cost}</td>
    </tr> 
{/if}