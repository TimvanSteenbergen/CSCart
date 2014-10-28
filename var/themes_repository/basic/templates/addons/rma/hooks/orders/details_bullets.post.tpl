{if $order_info.allow_return}
    <li><i class="icon-back"></i> <a href="{"rma.create_return?order_id=`$order_info.order_id`"|fn_url}" class="return">{__("return_registration")}</a></li>
{/if}
{if $order_info.isset_returns}
    <li><i class="icon-back"></i> <a href="{"rma.returns?order_id=`$order_info.order_id`"|fn_url}" class="return">{__("order_returns")}</a></li>
{/if}