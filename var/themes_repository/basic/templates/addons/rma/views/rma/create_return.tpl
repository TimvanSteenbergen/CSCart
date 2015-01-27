<div class="rma">
    <div class="rma-register">
<form action="{""|fn_url}" method="post" name="return_registration_form">
<input name="order_id" type="hidden" value="{$smarty.request.order_id}" />
<input name="user_id" type="hidden" value="{$order_info.user_id}" />

{if $actions}
    <div class="rma-register-action float-left"><strong>{__("what_you_would_like_to_do")}:</strong></div>
        <div>
            <select name="action">
                {foreach from=$actions item="action" key="action_id"}
                    <option value="{$action_id}">{$action.property}</option>
                {/foreach}
            </select>
        </div>
{/if}

<table class="table table-width{if $actions} margin-top{/if}">
<thead>
    <tr>
        <th><input type="checkbox" name="check_all" value="Y" title="{__("check_uncheck_all")}" class="checkbox cm-check-items" /></th>
        <th>{__("product")}</th>
        <th class="right">{__("price")}</th>
        <th>{__("quantity")}</th>
        <th>{__("reason")}</th>
    </tr>
</thead>
{foreach from=$order_info.products item="oi" key="key"}
<tr {cycle values=",class=\"table-row\""}>
    <td style="width: 1%" class="center rma-register-id">
        <input type="checkbox" name="returns[{$oi.cart_id}][chosen]" id="delete_checkbox" value="Y" class="checkbox cm-item" />
        <input type="hidden" name="returns[{$oi.cart_id}][product_id]" value="{$oi.product_id}" /></td>
    <td style="width: 60%" class="left"><a href="{"products.view?product_id=`$oi.product_id`"|fn_url}">{$oi.product nofilter}</a>
        {if $oi.product_options}
            {include file="common/options_info.tpl" product_options=$oi.product_options}
        {/if}</td>
    <td style="width: 1%" class="right nowrap">
        {if $oi.extra.exclude_from_calculate}{__("free")}{else}{include file="common/price.tpl" value=$oi.price}{/if}</td>
    <td style="width: 1%" class="center">
        <input type="hidden" name="returns[{$oi.cart_id}][available_amount]" value="{$oi.amount}" />
        <select name="returns[{$oi.cart_id}][amount]">
        {section name=$key loop=$oi.amount+1 start="1" step="1"}
                <option value="{$smarty.section.$key.index}">{$smarty.section.$key.index}</option>
        {/section}
        </select></td>
    <td style="width: 1%" class="center">
        {if $reasons}
            <select name="returns[{$oi.cart_id}][reason]" class="reason-select">
            {foreach from=$reasons item="reason" key="reason_id"}
                <option value="{$reason_id}">{$reason.property}</option>
            {/foreach}
            </select>
        {/if}</td>
</tr>
{foreachelse}
<tr>
    <td colspan="6"><p class="no-items">{__("no_items")}</p></td>
</tr>
{/foreach}
</table>

<div class="rma-return-comments">
    <strong>{__("type_comment")}</strong>
    <textarea name="comment" cols="3" rows="4" class="input-textarea-long"></textarea>
</div>
<div class="buttons-container">
    {include file="buttons/button.tpl" but_text=__("rma_return") but_name="dispatch[rma.add_return]" but_meta="cm-process-items"}
</div>
</form>

{capture name="mainbox_title"}{__("return_registration")}{/capture}
    </div>
</div>