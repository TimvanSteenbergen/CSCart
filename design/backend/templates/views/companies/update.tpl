{if $company_data.company_id}
    {assign var="id" value=$company_data.company_id}
{else}
    {assign var="id" value=0}
{/if}


{include file="views/profiles/components/profiles_scripts.tpl"}

{capture name="mainbox"}

{capture name="tabsbox"}
{** /Item menu section **}

<form class="form-horizontal form-edit {$form_class} {if !fn_check_view_permissions("companies.update", "POST")}cm-hide-inputs{/if} {if !$id}cm-ajax cm-comet cm-disable-check-changes{/if}" action="{""|fn_url}" method="post" id="company_update_form" enctype="multipart/form-data"> {* company update form *}
{* class=""*}
<input type="hidden" name="fake" value="1" />
<input type="hidden" name="selected_section" id="selected_section" value="{$smarty.request.selected_section}" />
<input type="hidden" name="company_id" value="{$id}" />

{** General info section **}
<div id="content_detailed" class="hidden"> {* content detailed *}
<fieldset>

{if "ULTIMATE"|fn_allowed_for && !$id && !$runtime.company_id}
    {include file="common/subheader.tpl" title=__("use_existing_store")}
    
    <div class="control-group">
        <label class="control-label" for="elm_company_exists_store">{__("store")}:</label>
        <div class="controls">
            <input type="hidden" name="company_data[clone_from]" id="elm_company_exists_store" value="" onchange="fn_switch_store_settings(this);" />
            {include file="common/ajax_select_object.tpl" data_url="companies.get_companies_list?show_all=Y&default_label=none" text=__("none") result_elm="elm_company_exists_store" id="exists_store_selector"}
        </div>
    </div>
    
    <div id="clone_settings_container" class="hidden">       
        {foreach from=$clone_schema key="object" item="object_data"}
            <div class="control-group">
                {assign var="label" value="clone_`$object`"}
                <label class="control-label" for="elm_company_clone_{$object}">{__($label)}{if $object_data.tooltip}{include file="common/tooltip.tpl" tooltip=__($object_data.tooltip)}{/if}:</label>
                <div class="controls">
                    <label class="checkbox"><input type="checkbox" name="company_data[clone][{$object}]" id="elm_company_clone_{$object}" {if $object_data.checked_by_default}checked="checked"{/if} class="cm-dependence-{$object}" value="Y" {if $object_data.dependence}onchange="fn_check_dependence('{$object_data.dependence}', this.checked)" onclick="fn_check_dependence('{$object_data.dependence}', this.checked)"{/if} />{if $object_data.checked_by_default}&nbsp;<span class="small-note">({__("recommended")})</span>{/if}</label>
                </div>
            </div>
        {/foreach}
    </div>
{/if}

{include file="common/subheader.tpl" title=__("information")}

{hook name="companies:general_information"}

<div class="control-group">
    <label for="elm_company_name" class="control-label cm-required">{__("vendor_name")}:</label>
    <div class="controls">
        <input type="text" name="company_data[company]" id="elm_company_name" size="32" value="{$company_data.company}" class="input-large" />
    </div>
</div>

{if "ULTIMATE"|fn_allowed_for}
{hook name="companies:storefronts"}
<div class="control-group">
    <label for="elm_company_storefront" class="control-label cm-required">{__("storefront_url")}:</label>
    <div class="controls">
    {if $runtime.company_id}
        http://{$company_data.storefront|unpuny}
    {else}
        <input type="text" name="company_data[storefront]" id="elm_company_storefront" size="32" value="{$company_data.storefront|unpuny}" class="input-large" placeholder="http://" />
    {/if}
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_company_secure_storefront">{__("secure_storefront_url")}:</label>
    <div class="controls">
    {if $runtime.company_id}
        https://{$company_data.secure_storefront|unpuny}
    {else}
        <input type="text" name="company_data[secure_storefront]" id="elm_company_secure_storefront" size="32" value="{$company_data.secure_storefront|unpuny}" class="input-large" placeholder="https://" />
    {/if}
    </div>
</div>
{/hook}

{if $id}
{include file="common/subheader.tpl" title=__("design")}

<div class="control-group">
    <label class="control-label">{__("store_theme")}:</label>
    <div class="controls">
        <p>{$theme_info.title}: {$current_style.name}</p>
        <a href="{"themes.manage?switch_company_id=`$id`"|fn_url}">{__("goto_theme_configuration")}</a>
    </div>
</div>
{else}
    {* TODO: Make theme selector *}
    <input type="hidden" value="responsive" name="company_data[theme_name]">
{/if}
{/if}

{if "MULTIVENDOR"|fn_allowed_for}
    {if !$runtime.company_id}
        {include file="common/select_status.tpl" input_name="company_data[status]" id="company_data" obj=$company_data}
    {else}
        <div class="control-group">
            <label class="control-label">{__("status")}:</label>
            <div class="controls">
                <label class="radio"><input type="radio" checked="checked" />{if $company_data.status == "A"}{__("active")}{elseif $company_data.status == "P"}{__("pending")}{elseif $company_data.status == "D"}{__("disabled")}{/if}</label>
            </div>
        </div>
    {/if}

    <div class="control-group">
        <label class="control-label" for="elm_company_language">{__("language")}:</label>
        <div class="controls">
        <select name="company_data[lang_code]" id="elm_company_language">
            {foreach from=$languages item="language" key="lang_code"}
                <option value="{$lang_code}" {if $lang_code == $company_data.lang_code}selected="selected"{/if}>{$language.name}</option>
            {/foreach}
        </select>
        </div>
    </div>
{/if}


{if !$id}
    {literal}
    <script type="text/javascript">
    function fn_toggle_required_fields()
    {
        var $ = Tygh.$;
        var checked = $('#company_description_vendor_admin').prop('checked');

        $('#company_description_username').prop('disabled', !checked);
        $('#company_description_first_name').prop('disabled', !checked);
        $('#company_description_last_name').prop('disabled', !checked);

        $('.cm-profile-field').each(function(index){
            $('#' + Tygh.$(this).prop('for')).prop('disabled', !checked);
        });
    }

    function fn_switch_store_settings(elm)
    {
        jelm = Tygh.$(elm);
        var close = true;
        if (jelm.val() != 'all' && jelm.val() != '' && jelm.val() != 0) {
            close = false;
        }
        
        Tygh.$('#clone_settings_container').toggleBy(close);
    }

    function fn_check_dependence(object, enabled)
    {
        if (enabled) {
            Tygh.$('.cm-dependence-' + object).prop('checked', 'checked').prop('readonly', true).on('click', function(e) {
                return false
            });
        } else {
            Tygh.$('.cm-dependence-' + object).prop('readonly', false).off('click');
        }
    }
    </script>
    {/literal}

    {if !"ULTIMATE"|fn_allowed_for}
        <div class="control-group">
            <label class="control-label" for="elm_company_vendor_admin">{__("create_administrator_account")}:</label>
            <div class="controls">
                <label class="checkbox">
                    <input type="checkbox" name="company_data[is_create_vendor_admin]" id="elm_company_vendor_admin" checked="checked" value="Y" onchange="fn_toggle_required_fields();" />
                </label>
            </div>
        </div>
        {if $settings.General.use_email_as_login != 'Y'}
        <div class="control-group" id="company_description_admin">
            <label for="elm_company_vendor_username" class="control-label cm-required">{__("account_name")}:</label>
            <div class="controls">
                <input type="text" name="company_data[admin_username]" id="elm_company_vendor_username" size="32" value="{$company_data.admin_username}" class="input-large" />
            </div>
        </div>
        <div class="control-group">
            <label for="elm_company_vendor_firstname" class="control-label cm-required">{__("first_name")}:</label>
            <div class="controls">
                <input type="text" name="company_data[admin_firstname]" id="elm_company_vendor_firstname" size="32" value="{$company_data.admin_first_name}" class="input-large" />
            </div>
        </div>
        <div class="control-group">
            <label for="elm_company_vendor_lastname" class="control-label cm-required">{__("last_name")}:</label>
            <div class="controls">
                <input type="text" name="company_data[admin_lastname]" id="elm_company_vendor_lastname" size="32" value="{$company_data.admin_last_name}" class="input-large" />
            </div>
        </div>
    {/if}
    {/if}
{/if}
{if !$runtime.company_id && "MULTIVENDOR"|fn_allowed_for}
<div class="control-group">
    <label class="control-label" for="elm_company_vendor_commission">{__("vendor_commission")}:</label>
    <div class="controls">
    <input type="text" name="company_data[commission]" id="elm_company_vendor_commission" value="{$company_data.commission}"  />
    <select name="company_data[commission_type]" class="span1">
        <option value="A" {if $company_data.commission_type == "A"}selected="selected"{/if}>{$currencies.$primary_currency.symbol nofilter}</option>
        <option value="P" {if $company_data.commission_type == "P"}selected="selected"{/if}>%</option>
    </select>
    </div>
</div>
{/if}


{if "MULTIVENDOR"|fn_allowed_for}
{if $company_data.status == "N" && $settings.General.use_email_as_login != 'Y'}
<div class="control-group">
    <label class="control-label" for="elm_company_request_account_name">{__("request_account_name")}:</label>
    <div class="controls">
        <input type="text" name="company_data[request_account_name]" id="elm_company_request_account_name" size="32" value="{$company_data.request_account_name}" />
    </div>
</div>
{/if}

{hook name="companies:contact_information"}
{if !$id}
    {include file="views/profiles/components/profile_fields.tpl" section="C" title=__("contact_information")}
{else}
    {include file="common/subheader.tpl" title=__("contact_information")}
{/if}

<div class="control-group">
    <label for="elm_company_email" class="control-label cm-required cm-email">{__("email")}:</label>
    <div class="controls">
        <input type="text" name="company_data[email]" id="elm_company_email" size="32" value="{$company_data.email}" class="input-large" />
    </div>
</div>

<div class="control-group">
    <label for="elm_company_phone" class="control-label cm-required">{__("phone")}:</label>
    <div class="controls">
        <input type="text" class="input-large" name="company_data[phone]" id="elm_company_phone" size="32" value="{$company_data.phone}" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_company_url">{__("url")}:</label>
    <div class="controls">
        <input type="text" class="input-large" name="company_data[url]" id="elm_company_url" size="32" value="{$company_data.url}" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_company_fax">{__("fax")}:</label>
    <div class="controls">
        <input type="text" class="input-large" name="company_data[fax]" id="elm_company_fax" size="32" value="{$company_data.fax}"  />
    </div>
</div>
{/hook}

{hook name="companies:shipping_address"}
{if !$id}
    {include file="views/profiles/components/profile_fields.tpl" section="B" title=__("shipping_address") shipping_flag=false}
{else}
    {include file="common/subheader.tpl" title=__("shipping_address")}
{/if}

<div class="control-group">
    <label for="elm_company_address" class="control-label cm-required">{__("address")}:</label>
    <div class="controls">
        <input type="text" class="input-large" name="company_data[address]" id="elm_company_address" size="32" value="{$company_data.address}" />
    </div>
</div>

<div class="control-group">
    <label for="elm_company_city" class="control-label cm-required">{__("city")}:</label>
    <div class="controls">
        <input type="text" class="input-large" name="company_data[city]" id="elm_company_city" size="32" value="{$company_data.city}" />
    </div>
</div>

<div class="control-group">
    <label for="elm_company_country" class="control-label cm-required">{__("country")}:</label>
    <div class="controls">
    {assign var="_country" value=$company_data.country|default:$settings.General.default_country}
    <select class="cm-country cm-location-shipping" id="elm_company_country" name="company_data[country]">
        <option value="">- {__("select_country")} -</option>
        {foreach from=$countries item="country" key="code"}
        <option {if $_country == $code}selected="selected"{/if} value="{$code}">{$country}</option>
        {/foreach}
    </select>
    </div>
</div>

<div class="control-group">
    {$_country = $company_data.country|default:$settings.General.default_country}
    {$_state = $company_data.state|default:$settings.General.default_state}

    <label for="elm_company_state" class="control-label cm-required">{__("state")}:</label>
    <div class="controls">
    <select id="elm_company_state" name="company_data[state]" class="cm-state cm-location-shipping {if !$states.$_country}hidden{/if}">
        <option value="">- {__("select_state")} -</option>
        {if $states.$_country}
            {foreach from=$states.$_country item=state}
                <option {if $_state == $state.code}selected="selected"{/if} value="{$state.code}">{$state.state}</option>
            {/foreach}
        {/if}
    </select>
    <input type="text" id="elm_company_state_d" name="company_data[state]" size="32" maxlength="64" value="{$_state}" {if $states.$_country}disabled="disabled"{/if} class="cm-state cm-location-shipping {if $states.$_country}hidden{/if} cm-skip-avail-switch" />
    </div>
</div>

<div class="control-group">
    <label for="elm_company_zipcode" class="control-label cm-required cm-zipcode cm-location-shipping">{__("zip_postal_code")}:</label>
    <div class="controls">
        <input type="text" name="company_data[zipcode]" id="elm_company_zipcode" size="32" value="{$company_data.zipcode}" />
    </div>
</div>
{/hook}
{/if}

{if "ULTIMATE"|fn_allowed_for}
    {include file="common/subheader.tpl" title="{__("settings")}: {__("company")}" }
    
    {foreach from=$company_settings key="field_id" item="item"}
        {include file="common/settings_fields.tpl" item=$item section="Company" html_id="field_`$section`_`$item.name`_`$item.object_id`" html_name="update[`$item.object_id`]"}
    {/foreach}
{/if}

{/hook}

</fieldset>
</div> {* /content detailed *}
{** /General info section **}



{** Company description section **}
<div id="content_description" class="hidden"> {* content description *}
<fieldset>
{hook name="companies:description"}
<div class="control-group">
    <label class="control-label" for="elm_company_description">{__("description")}:</label>
    <div class="controls">
        <textarea id="elm_company_description" name="company_data[company_description]" cols="55" rows="8" class="cm-wysiwyg input-large">{$company_data.company_description}</textarea>
    </div>
</div>
{/hook}
</fieldset>
</div> {* /content description *}
{** /Company description section **}


{if "MULTIVENDOR"|fn_allowed_for}
    {** Company logos section **}
    <div id="content_logos" class="hidden"> {* content logos *}
    {include file="views/companies/components/logos_list.tpl" logos=$company_data.logos company_id=$id}

    </div> {* /content logos *}
    {** /Company logos section **}

    {** Company categories section **}
    <div id="content_categories" class="hidden"> {* content categories *}
        {hook name="companies:categories"}
        {include file="pickers/categories/picker.tpl" multiple=true input_name="company_data[categories]" item_ids=$company_data.categories data_id="category_ids" no_item_text=__("text_all_categories_included") use_keys="N" but_meta="pull-right"}
        {/hook}
    </div> {* /content categories *}
    {** /Company categories section **}
{/if}


{if "ULTIMATE"|fn_allowed_for}
{** Company regions settings section **}
<div id="content_regions" class="hidden"> {* content regions *}
    <fieldset>
        <div class="control-group">
            <div class="controls">
            <input type="hidden" name="company_data[redirect_customer]" value="N" checked="checked"/>
            <label class="checkbox"><input type="checkbox" name="company_data[redirect_customer]" id="sw_company_redirect" {if $company_data.redirect_customer == "Y"}checked="checked"{/if} value="Y" class="cm-switch-availability cm-switch-inverse" />{__("redirect_customer_from_storefront")}</label>
            </div>
        </div>

        <div class="control-group" id="company_redirect">
            <label class="control-label" for="elm_company_entry_page">{__("entry_page")}</label>
            <div class="controls">
            <select name="company_data[entry_page]" id="elm_company_entry_page" {if $company_data.redirect_customer == "Y"}disabled="disabled"{/if}>
                <option value="none" {if $company_data.entry_page == "none"}selected="selected"{/if}>{__("none")}</option>
                <option value="index" {if $company_data.entry_page == "index"}selected="selected"{/if}>{__("index")}</option>
                <option value="all_pages" {if $company_data.entry_page == "all_pages"}selected="selected"{/if}>{__("all_pages")}</option>
            </select>
            </div>
        </div>
        
        {include file="common/double_selectboxes.tpl"
            title=__("countries")
            first_name="company_data[countries_list]"
            first_data=$company_data.countries_list
            second_name="all_countries"
            second_data=$countries_list}
    </fieldset>
</div>
{** /Company regions settings section **}

{/if}

{if "MULTIVENDOR"|fn_allowed_for && !$runtime.company_id}
{** Shipping methods section **}
<div id="content_shipping_methods" class="hidden"> {* shipping_methods *}
    {hook name="companies:shipping_methods"}
        {if $shippings}
        <input type="hidden" name="company_data[shippings]" value="" />
        <table width="100%" class="table table-middle">
        <thead>
        <tr>
            <th width="50%">{__("shipping_methods")}</th>
            <th class="center">{__("available_for_vendor")}</th>
        </tr>
        </thead>
        {foreach from=$shippings item="shipping" key="shipping_id"}
        <tr>
            <td><a href="{"shippings.update?shipping_id=`$shipping_id`"|fn_url}">{$shipping.shipping}{if $shipping.status == "D"} ({__("disabled")|lower}){/if}</a></td>
            <td class="center">
                <input type="checkbox" {if !$id || $shipping_id|in_array:$company_data.shippings_ids} checked="checked"{/if} name="company_data[shippings][]" value="{$shipping_id}">
            </td>
        </tr>
        {/foreach}
        </table>
        {else}
            <p class="no-items">{__("no_data")}</p>
        {/if}
    {/hook}
</div> {* /content shipping_methods *}
{** /Shipping methods section **}
{/if}

<div id="content_addons" class="hidden">
    {hook name="companies:detailed_content"}{/hook}
</div>

{hook name="companies:tabs_content"}{/hook}

</form> {* /product update form *}

{hook name="companies:tabs_extra"}{/hook}

{/capture}
{include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox group_name="companies" active_tab=$smarty.request.selected_section track=true}

{/capture}

{capture name="sidebar"}
{if $runtime.company_id}
<div class="sidebar-row">
    <h6>{__("menu")}</h6>
    <ul class="nav nav-list">
        <li><a href="{"products.manage?company_id=`$id`"|fn_url}">{__("view_vendor_products")}</a></li>
        {if "ULTIMATE"|fn_allowed_for}
            <li><a href="{"categories.manage?company_id=`$id`"|fn_url}">{__("view_vendor_categories")}</a></li>
        {/if}
        <li><a href="{"profiles.manage?company_id=`$id`"|fn_url}">{__("view_vendor_users")}</a></li>
        <li><a href="{"orders.manage?company_id=`$id`"|fn_url}">{__("view_vendor_orders")}</a></li>
    </ul>
</div>
{/if}
{/capture}

{** Form submit section **}
{capture name="buttons"}   
    {if $id}
        {include file="buttons/save_cancel.tpl" but_name="dispatch[companies.update]" but_target_form="company_update_form" save=$id}
    {else}
        {include file="buttons/save_cancel.tpl" but_name="dispatch[companies.add]" but_target_form="company_update_form" but_meta="cm-comet"}
    {/if}
{/capture}
{** /Form submit section **}

{if $id}
    {include file="common/mainbox.tpl" title="{__("editing_vendor")}: `$company_data.company`" content=$smarty.capture.mainbox select_languages=true buttons=$smarty.capture.buttons sidebar=$smarty.capture.sidebar}
{else}
    {include file="common/mainbox.tpl" title=__("new_vendor") content=$smarty.capture.mainbox sidebar=$smarty.capture.sidebar buttons=$smarty.capture.buttons}
{/if}
