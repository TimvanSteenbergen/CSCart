{if $order_info.return}
    <li>
        <em>{__("rma_return")}:&nbsp;</em>
        <span>{include file="common/price.tpl" value=$order_info.return}</span>
    </li>
{/if}