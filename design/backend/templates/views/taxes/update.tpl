{if $tax}
    {assign var="id" value=$tax.tax_id}
{else}
    {assign var="id" value=0}
{/if}

{capture name="mainbox"}

{capture name="tabsbox"}

<form action="{""|fn_url}" method="post" name="tax_form" class="form-horizontal form-edit {if ""|fn_check_form_permissions} cm-hide-inputs{/if}">
<input type="hidden" name="tax_id" value="{$id}" />
<input type="hidden" name="destination_id" value="{$destination_id}" />
<input type="hidden" name="selected_section" value="{$smarty.request.selected_section}" />

<div id="content_general">
<fieldset>
    <div class="control-group">
        <label for="elm_tax" class="control-label cm-required">{__("name")}:</label>
        <div class="controls">
            <input type="text" name="tax_data[tax]" id="elm_tax" size="30" value="{$tax.tax}" class="input-text-large main-input" />
        </div>
    </div>
    
    <div class="control-group">
        <label class="control-label" for="elm_regnumber">{__("regnumber")}:</label>
        <div class="controls">
            <input type="text" name="tax_data[regnumber]" id="elm_regnumber" size="30" value="{$tax.regnumber}" class="input-text" />
        </div>
    </div>
    
    <div class="control-group">
        <label class="control-label" for="elm_priority">{__("priority")}:</label>
        <div class="controls">
            <input type="text" name="tax_data[priority]" id="elm_priority" size="5" value="{$tax.priority}" class="input-text" />
        </div>
    </div>
    
    <div class="control-group">
        <label for="elm_address_type" class="control-label cm-required">{__("rates_depend_on")}:</label>
        <div class="controls">
        <select name="tax_data[address_type]" id="elm_address_type">
            <option value="S" {if $tax.address_type == "S"}selected="selected"{/if}>{__("shipping_address")}</option>
            <option value="B" {if $tax.address_type == "B"}selected="selected"{/if}>{__("billing_address")}</option>
        </select>
        </div>
    </div>
    
    {include file="common/select_status.tpl" input_name="tax_data[status]" id="elm_tax_data" obj=$tax}
    
    <div class="control-group">
        <label class="control-label" for="elm_price_includes_tax">{__("price_includes_tax")}:</label>
        <div class="controls">
            <input type="hidden" name="tax_data[price_includes_tax]" value="N" />
            <input type="checkbox" name="tax_data[price_includes_tax]" id="elm_price_includes_tax" value="Y" {if $tax.price_includes_tax == "Y"}checked="checked"{/if} />
        </div>
    </div>
</fieldset>
<!-- id="content_general" --></div>

<div id="content_tax_rates">

<table class="table table-middle">
<thead>
<tr>
    <th>{__("location")}</th>
    <th>{__("rate_value")}</th>
    <th>{__("type")}</th>
</tr>
</thead>
{foreach from=$destinations item=destination}
{assign var="d_id" value=$destination.destination_id}
<tr>
    <td>{$destination.destination}</td>
    <td><input type="hidden" name="tax_data[rates][{$d_id}][rate_id]" value="{$rates.$d_id.rate_id}" />
        <input type="text" name="tax_data[rates][{$d_id}][rate_value]" value="{$rates.$d_id.rate_value}" class="input-text" /></td>
    <td>
        <select name="tax_data[rates][{$d_id}][rate_type]">
            <option value="F" {if $rates.$d_id.rate_type == "F"}selected="selected"{/if}>{__("absolute")} ({$currencies.$primary_currency.symbol nofilter})</option>
            <option value="P" {if $rates.$d_id.rate_type == "P"}selected="selected"{/if}>{__("percent")} (%)</option>
        </select>
    </td>
</tr>
{/foreach}
</table>
<!-- id="content_tax_rates" --></div>

{capture name="buttons"}
    {include file="buttons/save_cancel.tpl" but_name="dispatch[taxes.update]" but_role="submit-link" but_target_form="tax_form" save=$id}
{/capture}

</form>
{/capture}
{include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox track=true active_tab=$smarty.request.selected_section}

{/capture}

{if $runtime.mode == "add"}
    {assign var="title" value=__("new_tax")}
{else}
    {assign var="title" value="{__("editing_tax")}:&nbsp;`$tax.tax`"}
{/if}
{include file="common/mainbox.tpl" title=$title content=$smarty.capture.mainbox select_languages=true buttons=$smarty.capture.buttons}
