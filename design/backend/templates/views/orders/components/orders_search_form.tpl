{if $in_popup}
    <div class="adv-search">
    <div class="group">
{else}
    <div class="sidebar-row">
    <h6>{__("search")}</h6>
{/if}

<form action="{""|fn_url}" name="orders_search_form" method="get" class="{$form_meta}">
{capture name="simple_search"}

{if $smarty.request.redirect_url}
<input type="hidden" name="redirect_url" value="{$smarty.request.redirect_url}" />
{/if}
{if $selected_section != ""}
<input type="hidden" id="selected_section" name="selected_section" value="{$selected_section}" />
{/if}

{$extra nofilter}

<div class="sidebar-field">
    <label for="cname">{__("customer")}</label>
    <input type="text" name="cname" id="cname" value="{$search.cname}" size="30" />
</div>

<div class="sidebar-field">
    <label for="email">{__("email")}</label>
    <input type="text" name="email" id="email" value="{$search.email}" size="30"/>
</div>

<div class="sidebar-field">
    <label for="issuer">{__("issuer")}</label>
    <input type="text" name="issuer" id="issuer" value="{$search.issuer}" size="30" />
</div>

<div class="sidebar-field">
    <label for="total_from">{__("total")}&nbsp;({$currencies.$primary_currency.symbol nofilter})</label>
    <input type="text" class="input-small" name="total_from" id="total_from" value="{$search.total_from}" size="3" /> - <input type="text" class="input-small" name="total_to" value="{$search.total_to}" size="3" />
</div>

{/capture}

{capture name="advanced_search"}

{hook name="orders:advanced_search"}

<div class="group form-horizontal">
<div class="control-group">
    <label class="control-label">{__("period")}</label>
    <div class="controls">
        {include file="common/period_selector.tpl" period=$search.period form_name="orders_search_form"}
    </div>
</div>
</div>

<div class="group">
{if $incompleted_view}
    <input type="hidden" name="status" value="{$smarty.const.STATUS_INCOMPLETED_ORDER}" />
{else}
<div class="control-group">
    <label class="control-label">{__("order_status")}</label>
    <div class="controls checkbox-list">
        {include file="common/status.tpl" status=$search.status display="checkboxes" name="status" columns=5}
    </div>
</div>
{/if}
</div>

<div class="row-fluid">
    <div class="group span6 form-horizontal">
    <div class="control-group">
        <label class="control-label" for="tax_exempt">{__("tax_exempt")}</label>
        <div class="controls">
        <select name="tax_exempt" id="tax_exempt">
            <option value="">--</option>
            <option value="Y" {if $search.tax_exempt == "Y"}selected="selected"{/if}>{__("yes")}</option>
            <option value="N" {if $search.tax_exempt == "N"}selected="selected"{/if}>{__("no")}</option>
        </select>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="order_id">{__("order_id")}</label>
        <div class="controls">
            <input type="text" name="order_id" id="order_id" value="{$search.order_id}" size="10"/>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="has_credit_memo">{__("has_credit_memo")}</label>
        <div class="controls">
            <input type="checkbox" name="has_credit_memo" id="has_credit_memo" value="Y"{if $search.has_credit_memo} checked="checked"{/if} />
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="crmemo_id">{__("credit_memo_id")}</label>
        <div class="controls">
            <input type="text" name="credit_memo_id" id="crmemo_id" value="{$search.credit_memo_id}" size="10"/>
        </div>
    </div>
    </div>

    <div class="group span6 form-horizontal">
        <div class="control-group">
            <label class="control-label" for="has_invoice">{__("has_invoice")}</label>
            <div class="controls">
                <input type="checkbox" name="has_invoice" id="has_invoice" value="Y"{if $search.has_invoice} checked="checked"{/if} />
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="inv_id">{__("invoice_id")}</label>
            <div class="controls">
                <input type="text" name="invoice_id" id="inv_id" value="{$search.invoice_id}" size="10"/>
            </div>
        </div>
        {include file="common/select_vendor.tpl"}
    </div>
</div>
<div class="group">
    <div class="control-group">
        <label class="checkbox" for="a_uid"><input type="checkbox" name="admin_user_id" id="a_uid" value="{$auth.user_id}" {if $search.admin_user_id}checked="checked"{/if} />{__("new_orders")}</label>
    </div>
</div>
<div class="group">
<div class="control-group">
    <label class="control-label">{__("shipping")}</label>
    <div class="controls checkbox-list">
        {html_checkboxes name="shippings" options=$shippings selected=$search.shippings columns=4}
    </div>
</div>
</div>

<div class="group">
<div class="control-group">
    <label class="control-label">{__("payment_methods")}</label>
    <div class="controls checkbox-list">
        {html_checkboxes name="payments" options=$payments selected=$search.payments columns=4}
    </div>
</div>
</div>
<div class="group">
    <div class="control-group">
        <label class="control-label">{__("ordered_products")}</label>
        <div class="controls ">
            {include file="common/products_to_search.tpl" placement="right"}
        </div>
    </div>
</div>
{/hook}

<div class="group">
    <div class="control-group">
{hook name="orders:search_form"}
{/hook}
    </div>
</div>

{/capture}

{include file="common/advanced_search.tpl" simple_search=$smarty.capture.simple_search advanced_search=$smarty.capture.advanced_search dispatch=$dispatch view_type="orders" in_popup=$in_popup}

</form>

{if $in_popup}
    </div></div>
{else}
    </div><hr>
{/if}