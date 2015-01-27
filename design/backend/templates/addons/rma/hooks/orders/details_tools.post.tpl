{if $order_info.allow_return}
    <li>{include file="buttons/button.tpl" but_text=__("return_registration") but_href="rma.create_return?order_id=`$order_info.order_id`" but_role="tool"}</li>
{/if}
{if $order_info.isset_returns}
    <li>{include file="buttons/button.tpl" but_text=__("order_returns") but_href="rma.returns?order_id=`$order_info.order_id`" but_role="tool"}</li>
{/if}