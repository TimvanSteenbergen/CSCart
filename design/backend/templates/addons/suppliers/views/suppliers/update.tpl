{if $supplier}
    {assign var="id" value=$supplier.supplier_id}
{else}
    {assign var="id" value=0}
{/if}

{include file="views/profiles/components/profiles_scripts.tpl"}

{capture name="mainbox"}

    {capture name="tabsbox"}
    {** /Item menu section **}

        <form class="form-horizontal form-edit {$form_class}" action="{""|fn_url}" method="post" id="supplier_update_form" enctype="multipart/form-data"> {* supplier update form *}
        {* class=""*}
            <input type="hidden" name="fake" value="1" />
            <input type="hidden" name="selected_section" id="selected_section" value="{$smarty.request.selected_section}" />
            <input type="hidden" name="supplier_id" value="{$id}" />

            {** General info section **}
            <div id="content_general" class="hidden"> {* content detailed *}
                <fieldset>

                    {include file="common/subheader.tpl" title=__("information")}

                    {hook name="suppliers:general_information"}
                        <div class="control-group">
                            <label for="elm_supplier_name" class="control-label cm-required">{__("name")}:</label>
                            <div class="controls">
                                <input type="text" name="supplier_data[name]" id="elm_supplier_name" size="32" value="{$supplier.name}" class="input-large" />
                            </div>
                        </div>

                        {include file="views/companies/components/company_field.tpl"
                            name="supplier_data[company_id]"
                            selected=$supplier.company_id
                        }

                        {include file="common/select_status.tpl" input_name="supplier_data[status]" id="elm_supplier_status" obj=$supplier}

                        {hook name="suppliers:contact_information"}
                            {include file="common/subheader.tpl" title=__("contact_information")}

                            <div class="control-group">
                                <label for="elm_supplier_email" class="control-label cm-required cm-email">{__("email")}:</label>
                                <div class="controls">
                                    <input type="text" name="supplier_data[email]" id="elm_supplier_email" size="32" value="{$supplier.email}" class="input-large" />
                                </div>
                            </div>

                            <div class="control-group">
                                <label for="elm_supplier_phone" class="control-label cm-required">{__("phone")}:</label>
                                <div class="controls">
                                    <input type="text" class="input-large" name="supplier_data[phone]" id="elm_supplier_phone" size="32" value="{$supplier.phone}" />
                                </div>
                            </div>

                            <div class="control-group">
                                <label class="control-label" for="elm_supplier_url">{__("url")}:</label>
                                <div class="controls">
                                    <input type="text" class="input-large" name="supplier_data[url]" id="elm_supplier_url" size="32" value="{$supplier.url}" />
                                </div>
                            </div>

                            <div class="control-group">
                                <label class="control-label" for="elm_supplier_fax">{__("fax")}:</label>
                                <div class="controls">
                                    <input type="text" class="input-large" name="supplier_data[fax]" id="elm_supplier_fax" size="32" value="{$supplier.fax}"  />
                                </div>
                            </div>
                        {/hook}

                        {hook name="suppliers:shipping_address"}
                            {include file="common/subheader.tpl" title=__("shipping_address")}

                            <div class="control-group">
                                <label for="elm_supplier_address" class="control-label cm-required">{__("address")}:</label>
                                <div class="controls">
                                    <input type="text" class="input-large" name="supplier_data[address]" id="elm_supplier_address" size="32" value="{$supplier.address}" />
                                </div>
                            </div>

                            <div class="control-group">
                                <label for="elm_supplier_city" class="control-label cm-required">{__("city")}:</label>
                                <div class="controls">
                                    <input type="text" class="input-large" name="supplier_data[city]" id="elm_supplier_city" size="32" value="{$supplier.city}" />
                                </div>
                            </div>

                            <div class="control-group">
                                <label for="elm_supplier_country" class="control-label cm-required">{__("country")}:</label>
                                <div class="controls">
                                    {assign var="_country" value=$supplier.country|default:$settings.General.default_country}
                                    <select class="cm-country cm-location-shipping" id="elm_supplier_country" name="supplier_data[country]">
                                        <option value="">- {__("select_country")} -</option>
                                        {foreach from=$countries item="country" key="code"}
                                            <option {if $_country == $code}selected="selected"{/if} value="{$code}">{$country}</option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>

                            <div class="control-group">
                                {$_country = $supplier.country|default:$settings.General.default_country}
                                {$_state = $supplier.state|default:$settings.General.default_state}

                                <label for="elm_supplier_state" class="control-label cm-required">{__("state")}:</label>
                                <div class="controls">
                                    <select id="elm_supplier_state" name="supplier_data[state]" class="cm-state cm-location-shipping {if !$states.$_country}hidden{/if}">
                                        <option value="">- {__("select_state")} -</option>
                                        {if $states && $states.$_country}
                                            {foreach from=$states.$_country item=state}
                                                <option {if $_state == $state.code}selected="selected"{/if} value="{$state.code}">{$state.state}</option>
                                            {/foreach}
                                        {/if}
                                    </select>
                                    <input type="text" id="elm_supplier_state_d" name="supplier_data[state]" size="32" maxlength="64" value="{$_state}" {if $states.$_country}disabled="disabled"{/if} class="cm-state cm-location-shipping {if $states.$_country}hidden{/if} cm-skip-avail-switch" />
                                </div>
                            </div>

                            <div class="control-group">
                                <label for="elm_supplier_zipcode" class="control-label cm-required cm-zipcode cm-location-shipping">{__("zip_postal_code")}:</label>
                                <div class="controls">
                                    <input type="text" name="supplier_data[zipcode]" id="elm_supplier_zipcode" size="32" value="{$supplier.zipcode}" />
                                </div>
                            </div>
                        {/hook}

                    {/hook}

                </fieldset>
            </div> {* /content detailed *}
            {** /General info section **}

            <div id="content_products" class="hidden">
                {include file="pickers/products/picker.tpl" input_name="supplier_data[products]" data_id="supplier_products" item_ids=$supplier.products type="links"}
            </div>

            <div id="content_shippings">
                {hook name="companies:shipping_methods"}
                    {if $shippings}
                    <table width="100%" class="table table-middle">
                    <thead>
                    <tr>
                        <th width="50%">{__("shipping_methods")}</th>
                        <th class="center">{__("available_for_supplier")}</th>
                    </tr>
                    </thead>
                    {if $supplier.shippings}
                        {assign var="shippings_ids" value=$supplier.shippings}
                    {/if}
                    {foreach from=$shippings item="shipping" key="shipping_id"}
                    <tr>
                        <td><a href="{"shippings.update?shipping_id=`$shipping_id`"|fn_url}">{$shipping.shipping}</a></td>
                        <td class="center">
                            <input type="checkbox" {if !$supplier.supplier_id || $shipping_id|in_array:$supplier.shippings} checked="checked"{/if} name="supplier_data[shippings][]" value="{$shipping_id}">
                        </td>
                    </tr>
                    {/foreach}
                    </table>
                    {else}
                        <p class="no-items">{__("no_data")}</p>
                    {/if}
                {/hook}
            </div>

        </form> {* /product update form *}

        {hook name="suppliers:tabs_extra"}{/hook}

    {/capture}
    {include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox group_name="suppliers" active_tab=$smarty.request.selected_section track=true}

{/capture}

{** Form submit section **}
{capture name="buttons"}
    {include file="buttons/save_cancel.tpl" but_name="dispatch[suppliers.update]" but_target_form="supplier_update_form" save=$id}
{/capture}
{** /Form submit section **}

{if $supplier}
    {$_title="{__("editing_supplier")}: `$supplier.name`"}
{else}
    {$_title=__("add_supplier")}
{/if}

{include file="common/mainbox.tpl" title=$_title content=$smarty.capture.mainbox select_languages=true buttons=$smarty.capture.buttons}
