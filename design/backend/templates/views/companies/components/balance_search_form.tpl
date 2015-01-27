<div class="sidebar-row">
<h6>{__("search")}</h6>

<form action="{""|fn_url}" name="balance_search_form" method="get" class="cm-disable-empty">
{capture name="simple_search"}

{if $smarty.request.redirect_url}
    <input type="hidden" name="redirect_url" value="{$smarty.request.redirect_url}" />
{/if}
{if $selected_section != ""}
    <input type="hidden" id="selected_section" name="selected_section" value="{$selected_section}" />
{/if}

<div class="sidebar-field ajax-select">
    <label>{__("vendor")}</label>
    {if !$runtime.company_id}
        <input type="hidden" name="vendor" id="search_hidden_vendor" value="{$search.vendor|default:'all'}" />
    {include file="common/ajax_select_object.tpl" data_url="companies.get_companies_list?show_all=Y" text=$search.vendor|fn_get_company_name|default:__("all_vendors") result_elm="search_hidden_vendor" id="company_search"}
        {else}
        {$search.vendor|fn_get_company_name}
    {/if}
</div>

<div class="sidebar-field">
    <label>{__("transaction_type")}</label>
    <select name="transaction_type">
        <option value="both" {if $search.transaction_type == "both"}selected="selected"{/if}>{__("both")}</option>
        <option value="income" {if $search.transaction_type == "income"}selected="selected"{/if}>{__("income")}</option>
        <option value="expenditure" {if $search.transaction_type == "expenditure"}selected="selected"{/if}>{__("expenditure")}</option>
    </select>
</div>
{/capture}

{capture name="advanced_search"}
<div class="group form-horizontal">
    <div class="control-group">
        <label class="control-label">{__("sales_period")}</label>
        <div class="controls">
            {include file="common/period_selector.tpl" period=$search.period form_name="balance_search_form"}
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">{__("payment")}</label>
        <div class="controls">
            <input type="text" name="payment" value="{$search.payment}" />
        </div>
    </div>
</div>
{/capture}

{include file="common/advanced_search.tpl" simple_search=$smarty.capture.simple_search advanced_search=$smarty.capture.advanced_search dispatch=$dispatch view_type="balance"}

</form>
</div>
<hr>