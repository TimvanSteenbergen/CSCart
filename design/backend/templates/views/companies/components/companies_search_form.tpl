{if $in_popup}
<div class="adv-search">
<div class="group">
    {else}
<div class="sidebar-row">
    <h6>{__("search")}</h6>
{/if}

<form name="companies_search_form" action="{""|fn_url}" method="get" class="{$form_meta}">
{capture name="simple_search"}

{if $smarty.request.redirect_url}
<input type="hidden" name="redirect_url" value="{$smarty.request.redirect_url}" />
{/if}

{if $selected_section != ""}
<input type="hidden" id="selected_section" name="selected_section" value="{$selected_section}" />
{/if}

{if $search.user_type}
<input type="hidden" name="user_type" value="{$search.user_type}" />
{/if}

{if $company_id}
<input type="hidden" name="company_id" value="{$company_id}" />
{/if}

{if $put_request_vars}
    {foreach from=$smarty.request key="k" item="v"}
        {if $v && $k != "callback"} {* bzz: hardcode *}
            <input type="hidden" name="{$k}" value="{$v}" />
        {/if}
    {/foreach}
{/if}

{$extra nofilter}
<div class="sidebar-field">
    <label for="elm_name">{__("name")}</label>
    <input type="text" name="company" id="elm_name" value="{$search.company}" />
</div>

<div class="sidebar-field">
    <label for="elm_email">{__("email")}</label>
    <input type="text" name="email" id="elm_email" value="{$search.email}" />
</div>

{/capture}

{capture name="advanced_search"}
<div class="row-fluid">
<div class="group span6 form-horizontal">
    <div class="control-group">
        <label for="elm_address" class='control-label'>{__("address")}</label>
        <div class="controls">
        <input type="text" name="address" id="elm_address" value="{$search.address}" />
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="elm_city">{__("city")}</label>
        <div class="controls">
        <input type="text" name="city" id="elm_city" value="{$search.city}" />
        </div>
    </div>
    <div class="control-group">
        <label for="srch_country" class="control-label">{__("country")}</label>
        <div class="controls">
            <select id="srch_country" name="country" class="cm-country cm-location-search">
                <option value="">- {__("select_country")} -</option>
                {foreach from=$countries item="country" key="code"}
                <option value="{$code}" {if $search.country == $code}selected="selected"{/if}>{$country}</option>
                {/foreach}
            </select>
        </div>
    </div>

    <div class="control-group">
        <label for="srch_state" class="control-label">{__("state")}</label>
        <div class="controls">
        <select id="srch_state" class="cm-state cm-location-search hidden" name="state">
            <option value="">- {__("select_state")} -</option>
        </select>
        <input class="cm-state cm-location-search" type="text" id="srch_state_d" name="state" maxlength="64" value="{$search.state}" disabled="disabled"/>
        </div>
    </div>

    {if !"ULTIMATE"|fn_allowed_for}
    <div class="control-group">
        <label class="control-label" for="status">{__("status")}</label>
        <div class="controls">
        <select name="status" id="status">
            <option value="">--</option>
            <option value="A" {if $search.status == "A"}selected="selected"{/if}>{__("active")}</option>
            <option value="P" {if $search.status == "P"}selected="selected"{/if}>{__("pending")}</option>
            <option value="N" {if $search.status == "N"}selected="selected"{/if}>{__("new")}</option>
            <option value="D" {if $search.status == "D"}selected="selected"{/if}>{__("disabled")}</option>
        </select>
        </div>
    </div>
    {/if}

</div>
<div class="group span6 form-horizontal">
    <div class="control-group">
        <label class="control-label" for="elm_zipcode">{__("zip_postal_code")}</label>
        <div class="controls">
        <input type="text" name="zipcode" id="elm_zipcode" value="{$search.zipcode}" />
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="elm_phone">{__("phone")}</label>
        <div class="controls">
        <input type="text" name="phone" id="elm_phone" value="{$search.phone}" />
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="elm_url">{__("url")}</label>
        <div class="controls">
        <input type="text" name="url" id="elm_url" value="{$search.url}"/>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="elm_fax">{__("fax")}</label>
        <div class="controls">
        <input type="text" name="fax" id="elm_fax" value="{$search.fax}" /></div>
    </div>

    {hook name="companies:search_form"}
{/hook}

</div>
</div>
{/capture}

{include file="common/advanced_search.tpl" simple_search=$smarty.capture.simple_search advanced_search=$smarty.capture.advanced_search dispatch=$dispatch view_type="companies" in_popup=$in_popup}

</form>

{if $in_popup}
</div></div>
    {else}
</div><hr>
{/if}
