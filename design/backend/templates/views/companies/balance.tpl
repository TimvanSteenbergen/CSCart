{capture name="mainbox"}

{if $runtime.company_id}
    {assign var="hide_controls" value=true}
{/if}

{assign var="c_url" value=$config.current_url|fn_query_remove:"sort_by":"sort_order"}
{assign var="c_icon" value="<i class=\"exicon-`$search.sort_order_rev`\"></i>"}
{assign var="c_dummy" value="<i class=\"exicon-dummy\"></i>"}

{capture name="add_new_picker"}
    {include file="views/companies/components/balance_new_payment.tpl" c_url=$c_url}
{/capture}
{include file="common/popupbox.tpl" id="add_payment" content=$smarty.capture.add_new_picker text=__("new_payout") act="hidden"}

<form action="{""|fn_url}" method="post" class="form-horizontal form-edit" name="manage_payouts_form">

{include file="common/pagination.tpl" save_current_page=true save_current_url=true}

<input type="hidden" name="redirect_url" value="{$c_url}" />
{if $payouts}
<table width="100%" class="table table-middle">
<thead>
<tr>
    <th class="left">{include file="common/check_items.tpl"}</th>
    <th>
        <span id="on_st" alt="{__("expand_collapse_list")}" title="{__("expand_collapse_list")}" class=" hand cm-combinations-visitors"><span class="exicon-expand"></span></span>
        <span id="off_st" alt="{__("expand_collapse_list")}" title="{__("expand_collapse_list")}" class="hand hidden cm-combinations-visitors"><span class="exicon-collapse"></span></span>
        <a class="cm-ajax" href="{"`$c_url`&sort_by=sort_vendor&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("vendor")}{if $search.sort_by == "sort_vendor"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
    <th><a class="cm-ajax" href="{"`$c_url`&sort_by=sort_period&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("sales_period")}{if $search.sort_by == "sort_period"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
    <th><a class="cm-ajax" href="{"`$c_url`&sort_by=sort_amount&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("payment_amount")}{if $search.sort_by == "sort_amount"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
    <th><a class="cm-ajax" href="{"`$c_url`&sort_by=sort_date&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("date")}{if $search.sort_by == "sort_date"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
    <th>{__("vendor_commission")}</th>
    <th>{__("payment")}</th>
    <th class="center" width="5%">&nbsp;
    </th>
</tr>
</thead>
{foreach name="payouts" from=$payouts item=payout}
<tr>
    <td class="left">
           <input type="checkbox" name="payout_ids[]" value="{$payout.payout_id}" class="cm-item" /></td>
    <td>
        <span name="plus_minus" id="on_payout_note_{$smarty.foreach.payouts.iteration}" alt="{__("expand_collapse_list")}" title="{__("expand_collapse_list")}" class="hand cm-combination-visitors"><span class="exicon-expand"></span></span>
        <span name="minus_plus" id="off_payout_note_{$smarty.foreach.payouts.iteration}" alt="{__("expand_collapse_list")}" title="{__("expand_collapse_list")}" class="hand hidden cm-combination-visitors"><span class="exicon-collapse"></span></span>
        {if $payout.company_id}
            {$payout.company|default:__("deleted")}
        {else}
            {$settings.Company.company_name}
        {/if}
    </td>
    <td>
        {if !$payout.date}
            {$payout.start_date|date_format:"`$settings.Appearance.date_format`"}&nbsp;-&nbsp;{$payout.end_date|date_format:"`$settings.Appearance.date_format`"}
        {else}
            -
        {/if}
    </td>
    <td>
        <span class="{if $payout.payout_amount < 0}text-error{else}text-success{/if}">{include file="common/price.tpl" value=$payout.payout_amount}</span>
    </td>
    <td>
        {$payout.payout_date|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}
    </td>
    <td>
        {if $payout.commission_type == "A"}{include file="common/price.tpl" value=$payout.commission}{else}{$payout.commission}%{/if}
    </td>
    <td>
        {if $payout.payment_method}{$payout.payment_method}{elseif $payout.order_id}{__("order")}: <a href="{"orders.details?order_id=`$payout.order_id`"|fn_url}">#{$payout.order_id}</a>{else}-{/if}
    </td>
    <td class="center nowrap">    
        {if !$hide_controls}
        <div class="hidden-tools">
            {capture name="tools_list"}
                <li>{btn type="list" class="cm-confirm" text=__("delete") href="companies.payout_delete?payout_id=`$payout.payout_id`&redirect_url={$c_url|rawurlencode}"}</li>
            {/capture}
            {dropdown content=$smarty.capture.tools_list}
        </div>
        {/if}
    </td>
</tr>
<tr id="payout_note_{$smarty.foreach.payouts.iteration}" class="row-more {if $hide_extra_button != "Y"}hidden{/if}">
    <td colspan="8" class="row-more-body top row-gray">
        <div class="control-group">
            <label class="control-label" for="payout_comments_{$payout.payout_id}">{__("comment")}</label>
            <div class="controls">
            {if $runtime.company_id}
                <p>
                {if $payout.comments}{$payout.comments}{else}-{/if}
                </p>
            {else}
            <textarea class="span6" rows="4" cols="25" name="payout_comments[{$payout.payout_id}]" id="payout_comments_{$payout.payout_id}">{strip}
                {$payout.comments}
            {/strip}</textarea>
            {/if}
            </div>
        </div>
    </td>
</tr>
{/foreach}
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}

{include file="common/pagination.tpl"}

{include file="views/companies/components/balance_info.tpl"}
</form>

{capture name="buttons"}
    {if !$hide_controls && $payouts}
        {capture name="tools_list"}
            <li>{btn type="delete_selected" dispatch="dispatch[companies.m_delete_payouts]" form="manage_payouts_form"}</li>
        {/capture}
        {dropdown content=$smarty.capture.tools_list}

        {include file="buttons/button.tpl" but_text=__("save") but_name="dispatch[companies.update_payout_comments]" but_role="submit-link" but_target_form="manage_payouts_form"}
    {/if}
{/capture}

{capture name="adv_buttons"}
    {if !$hide_controls}
        {include file="common/popupbox.tpl" id="add_payment" text=__("new_payout") content="" title=__("add_payout") act="general" icon="icon-plus"}
    {/if}
{/capture}

{capture name="sidebar"}
    {include file="common/saved_search.tpl" dispatch="companies.balance" view_type="balance"}
    {include file="views/companies/components/balance_search_form.tpl" dispatch="companies.balance"}
{/capture}

{/capture}
{include file="common/mainbox.tpl" title=__("vendor_account_balance") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons sidebar=$smarty.capture.sidebar}