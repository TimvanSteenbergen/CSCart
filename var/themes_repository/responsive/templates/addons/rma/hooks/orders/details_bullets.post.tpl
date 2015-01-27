{if $order_info.allow_return}
    {include file="buttons/button.tpl" but_meta="ty-btn__text" but_role="text" but_text=__("return_registration") but_href="rma.create_return?order_id=`$order_info.order_id`" but_icon="ty-orders__actions-icon ty-icon-back"}
{/if}
{if $order_info.isset_returns}
    {include file="buttons/button.tpl" but_meta="ty-btn__text" but_role="text" but_text=__("order_returns") but_href="rma.returns?order_id=`$order_info.order_id`" but_icon="ty-orders__actions-icon ty-icon-back"}
{/if}