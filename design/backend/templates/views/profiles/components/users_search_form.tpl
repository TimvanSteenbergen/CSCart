{if $in_popup}
    <div class="adv-search">
    <div class="group">
{else}
    <div class="sidebar-row">
    <h6>{__("search")}</h6>
{/if}
<form name="user_search_form" action="{""|fn_url}" method="get" class="{$form_meta}">

{if $smarty.request.redirect_url}
<input type="hidden" name="redirect_url" value="{$smarty.request.redirect_url}" />
{/if}

{if $selected_section != ""}
<input type="hidden" id="selected_section" name="selected_section" value="{$selected_section}" />
{/if}

{if $search.user_type}
<input type="hidden" name="user_type" value="{$search.user_type}" />
{/if}

{if $put_request_vars}
{foreach from=$smarty.request key="k" item="v"}
{if $v && $k != "callback"}
<input type="hidden" name="{$k}" value="{$v}" />
{/if}
{/foreach}
{/if}

{capture name="simple_search"}
{$extra nofilter}
<div class="sidebar-field">
    <label for="elm_name">{__("name")}</label>
    <div class="break">
        <input type="text" name="name" id="elm_name" value="{$search.name}" />
    </div>
</div>
<div class="sidebar-field">
    <label for="elm_company">{__("company")}</label>
    <div class="break">
        <input type="text" name="company" id="elm_company" value="{$search.company}" />
    </div>
</div>
<div class="sidebar-field">
    <label for="elm_email">{__("email")}</label>
    <div class="break">
        <input type="text" name="email" id="elm_email" value="{$search.email}" />
    </div>
</div>
{/capture}

{capture name="advanced_search"}
    <div class="row-fluid">
        <div class="group span6 form-horizontal">
            {if $settings.General.use_email_as_login != "Y"}
            <div class="control-group">
                <label class="control-label" for="elm_user_login">{__("username")}</label>
                <div class="controls">
                <input type="text" name="user_login" id="elm_user_login" value="{$search.user_login}" />
                </div>
            </div>
            {/if}
            {if !"ULTIMATE:FREE"|fn_allowed_for}
                <div class="control-group">
                    <label class="control-label" for="elm_usergroup_id">{__("usergroup")}</label>
                    <div class="controls">
                    <select name="usergroup_id" id="elm_usergroup_id">
                        <option value="{$smarty.const.ALL_USERGROUPS}"> -- </option>
                        <option value="0" {if $search.usergroup_id === "0"}selected="selected"{/if}>{__("not_a_member")}</option>
                        {foreach from=$usergroups item=usergroup}
                        <option value="{$usergroup.usergroup_id}" {if $search.usergroup_id == $usergroup.usergroup_id}selected="selected"{/if}>{$usergroup.usergroup}</option>
                        {/foreach}
                    </select>
                    </div>
                </div>
            {/if}
            <div class="control-group">
                <label class="control-label" for="elm_tax_exempt">{__("tax_exempt")}</label>
                <div class="controls">
                <select name="tax_exempt" id="elm_tax_exempt">
                    <option value="">--</option>
                    <option value="Y" {if $search.tax_exempt == "Y"}selected="selected"{/if}>{__("yes")}</option>
                    <option value="N" {if $search.tax_exempt == "N"}selected="selected"{/if}>{__("no")}</option>
                </select>
                </div>
            </div>

            {include file="common/select_vendor.tpl"}
            {hook name="profiles:search_form"}{/hook}
    </div>

    <div class="group span6 form-horizontal">
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
                <input class="cm-state cm-location-search" type="text" id="srch_state_d" name="state" maxlength="64" value="{$search.state}" disabled="disabled" />
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="elm_address">{__("address")}</label>
            <div class="controls">
                <input type="text" name="address" id="elm_address" value="{$search.address}" />
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="elm_zipcode">{__("zip_postal_code")}</label>
            <div class="controls">
                <input type="text" name="zipcode" id="elm_zipcode" value="{$search.zipcode}" />
            </div>
        </div>

    </div>
</div>

<div class="group">
    <div class="control-group">
        <label class="control-label">{__("ordered_products")}</label>
        <div class="controls">
            {include file="common/products_to_search.tpl" placement="right"}
        </div>
    </div>
</div>

{/capture}

{include file="common/advanced_search.tpl" simple_search=$smarty.capture.simple_search advanced_search=$smarty.capture.advanced_search dispatch=$dispatch view_type="users" in_popup=$in_popup}

</form>

{if $in_popup}
</div></div>
{else}
</div><hr>
{/if}