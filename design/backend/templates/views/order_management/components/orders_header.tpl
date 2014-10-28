<div class="pb-container" align="center">

<span class="{if $runtime.mode == "products"}active{else}complete{/if}">
    <em>1</em>
    {if $runtime.mode != "products"}<a href="{"order_management.products"|fn_url}">{/if}{__("products")}{if $runtime.mode != "products"}</a>{/if}
</span>

<img src="{$images_dir}/icons/pb_arrow.gif" width="25" height="7" border="0" alt="&rarr;" />

<span class="{if $runtime.mode == "customer_info"}active{elseif $runtime.mode != "customer_info"}complete{/if}">
    <em>2</em>
    {if $runtime.mode != "customer_info"}<a href="{"order_management.customer_info"|fn_url}">{/if}{__("customer_details")}{if $runtime.mode != "customer_info"}</a>{/if}
</span>

<img src="{$images_dir}/icons/pb_arrow.gif" width="25" height="7" border="0" alt="&rarr;" />

<span class="{if $runtime.mode == "totals"}active{elseif $runtime.mode == "summary"}complete{/if}">
    <em>3</em>
    {if $runtime.mode == "summary"}<a href="{"order_management.totals"|fn_url}">{/if}{__("totals")}{if $runtime.mode == "summary"}</a>{/if}
</span>

<img src="{$images_dir}/icons/pb_arrow.gif" width="25" height="7" border="0" alt="&rarr;" />

<span class="{if $runtime.mode == "summary"}active{/if}">
    <em>4</em>
    {__("summary")}
</span>

</div>

{if $cart.order_id}
{capture name="extra_tools"}
    {include file="buttons/button.tpl" but_href="orders.details?order_id=`$cart.order_id`" but_text="{__("order")}: #`$cart.order_id`" but_role="tool"}
{/capture}
{/if}