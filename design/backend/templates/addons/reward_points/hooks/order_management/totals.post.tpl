{if $cart.points_info.reward}
    <tr>
        <td>{__("points")}:</td>
        <td>{$cart.points_info.reward}</td>
    </tr>
{/if}

{if $cart.points_info.in_use}
    <tr>
        <td class="nowrap">{__("points_in_use")}&nbsp;({$cart.points_info.in_use.points}&nbsp;{__("points")})&nbsp;<a href="{"order_management.delete_points_in_use"|fn_url}"><i class="icon-trash" title="{__("delete")}"></i></a>:</td>
        <td>{include file="common/price.tpl" value=$cart.points_info.in_use.cost}</td>
    </tr>
{/if}