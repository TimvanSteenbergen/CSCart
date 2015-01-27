{if $shipping}
    {assign var="id" value=$shipping.shipping_id}
{else}
    {assign var="id" value=0}
{/if}

{assign var="allow_save" value=$shipping|fn_allow_save_object:"shippings"}

<script type="text/javascript">
(function(_, $) {
    $(document).ready(function() {

        $('#elm_rate_calculation_R,#elm_rate_calculation_M').on('change', function() {
            if ($(this).val() == 'M') {
                $('#elm_service').val('');
                $('#elm_service').prop('disabled', true);
            } else {
                $('#elm_service').prop('disabled', false);
            }

            $('#elm_service').trigger('change');
        });

        $('#elm_service').on('change', function() {
            var option = $(this).find('option:selected');
            var href;

            if (option.val()) {
                href = fn_url('shippings.configure?shipping_id={$id}&module=' + option.data('caShippingModule') + '&code=' + option.data('caShippingCode'));

                if ($('#configure a').prop('href') != href) {
                    $('#content_configure').remove();
                    $('#configure a').prop('href', href);
                }
                $('#configure').show();
            } else {
                $('#configure').hide();
            }
        });

    });
}(Tygh, Tygh.$));
</script>


{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="shippings_form" enctype="multipart/form-data" class="form-horizontal form-edit {if !$allow_save} cm-hide-inputs{/if}">
<input type="hidden" name="shipping_id" value="{$id}" />

{if $id}
{capture name="tabsbox"}
<div id="content_general">
{/if}

<fieldset>
<div class="control-group">
    <label class="control-label cm-required" for="ship_descr_shipping">{__("name")}</label>
    <div class="controls">
        <input type="text" name="shipping_data[shipping]" id="ship_descr_shipping" size="30" value="{$shipping.shipping}" class="input-large" />
    </div>
</div>

{if $allow_save}
    {if "MULTIVENDOR"|fn_allowed_for}
        {assign var="zero_company_id_name_lang_var" value="none"}
    {/if}
    {include file="views/companies/components/company_field.tpl"
        name="shipping_data[company_id]"
        id="shipping_data_`$id`"
        selected=$shipping.company_id
        zero_company_id_name_lang_var=$zero_company_id_name_lang_var
    }
{/if}

<div class="control-group">
    <label class="control-label">{__("icon")}</label>
    <div class="controls">
    {include file="common/attach_images.tpl" image_name="shipping" image_object_type="shipping" image_pair=$shipping.icon no_detailed="Y" hide_titles="Y" image_object_id=$id}
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_delivery_time">{__("delivery_time")}</label>
    <div class="controls">
    <input type="text" class="input-medium" name="shipping_data[delivery_time]" id="elm_delivery_time" size="30" value="{$shipping.delivery_time}" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_min_weight">{__("weight_limit")}&nbsp;({$settings.General.weight_symbol}):</label>
    <div class="controls">
        <input type="text" name="shipping_data[min_weight]" id="elm_min_weight" size="4" value="{$shipping.min_weight}" class="input-mini" />&nbsp;-&nbsp;<input type="text" name="shipping_data[max_weight]" size="4" value="{if $shipping.max_weight != "0.00"}{$shipping.max_weight}{/if}" class="input-mini right" />
    </div>
</div>

<div class="control-group">
    <label class="control-label">{__("rate_calculation")}</label>
    <div class="controls">
        <label class="radio inline" for="elm_rate_calculation_M">
        <input type="radio" name="shipping_data[rate_calculation]" id="elm_rate_calculation_M" value="M" {if $shipping.rate_calculation == "M" || ! $shipping.rate_calculation}checked="checked"{/if} />
        {__("rate_calculation_manual")}</label>
        <label class="radio inline" for="elm_rate_calculation_R">
        <input type="radio" name="shipping_data[rate_calculation]" id="elm_rate_calculation_R" value="R" {if $shipping.rate_calculation == "R"}checked="checked"{/if} />
        {__("rate_calculation_realtime")}</label>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_service">{__("shipping_service")}</label>
    <div class="controls">
        <select name="shipping_data[service_id]" id="elm_service" {if $shipping.rate_calculation == "M" || !$id}disabled="disabled"{/if}>
            <option value="">--</option>
            {foreach from=$services item=service}
                <option data-ca-shipping-module="{$service.module}" data-ca-shipping-code="{$service.code}" value="{$service.service_id}" {if $shipping.service_id == $service.service_id}selected="selected"{/if}>{$service.description}</option>
            {/foreach}
        </select>
        {if $allow_save}
            &nbsp;<span>{__("test")}</span> &nbsp;{__("weight")} ({$settings.General.weight_symbol})&nbsp;
            <div class="input-append">
                <input id="elm_weight" type="text" class="input-mini" size="3" name="shipping_data[test_weight]" value="0" />
                <input type="hidden" name="result_ids" value="elm_shipping_test" />
                {include file="buttons/button.tpl" but_role="action" but_name="dispatch[shippings.test]" but_text=__("test") but_meta="cm-submit btn cm-skip-validation cm-ajax cm-form-dialog-opener"}
            </div>
            <div id="elm_shipping_test" title="{__("test")}"></div>
        {/if}
    </div>
</div>

<div class="control-group">
    <label class="control-label">{__("taxes")}</label>
    <div class="controls">
            {foreach from=$taxes item="tax"}
            <label class="checkbox inline" for="elm_shippings_taxes_{$tax.tax_id}">
            <input type="checkbox" name="shipping_data[tax_ids][{$tax.tax_id}]" id="elm_shippings_taxes_{$tax.tax_id}" {if $tax.tax_id|in_array:$shipping.tax_ids}checked="checked"{/if} value="{$tax.tax_id}" />
            {$tax.tax}</label>
        {foreachelse}
            &ndash;
        {/foreach}
    </div>
</div>

{hook name="shippings:update"}
{/hook}
{if !"ULTIMATE:FREE"|fn_allowed_for}
    <div class="control-group">
        <label class="control-label">{__("usergroups")}</label>
        <div class="controls">
            {include file="common/select_usergroups.tpl" id="elm_ship_data_usergroup_id" name="shipping_data[usergroup_ids]" usergroups=$usergroups usergroup_ids=$shipping.usergroup_ids input_extra="" list_mode=false}
        </div>
    </div>
{/if}
{include file="views/localizations/components/select.tpl" data_name="shipping_data[localization]" data_from=$shipping.localization}

{include file="common/select_status.tpl" input_name="shipping_data[status]" id="elm_shipping_status" obj=$shipping}
</fieldset>

{capture name="buttons"}
    {if $id}
        {capture name="tools_list"}
            {hook name="shippings:update_tools_list"}
                <li>{btn type="list" text=__("add_shipping_method") href="shippings.add"}</li>
                <li>{btn type="list" text=__("shipping_methods") href="shippings.manage"}</li>
                <li class="divider"></li>
                <li>{btn type="list" text=__("delete") class="cm-confirm" dispatch="dispatch[shippings.delete_rate_values]" form="shippings_form"}</li>
            {/hook}
        {/capture}
        {dropdown content=$smarty.capture.tools_list}
    {/if}

    {if !$hide_for_vendor}
        {include file="buttons/save_cancel.tpl" but_name="dispatch[shippings.update]" but_target_form="shippings_form" save=$id}
    {else}
        {include file="buttons/save_cancel.tpl" but_name="dispatch[shippings.update]" hide_first_button=true hide_second_button=true but_target_form="shippings_form" save=$id}
    {/if}
{/capture}

{if $id}
    <input type="hidden" name="selected_section" value="general" />
    <!--content_general--></div>

    <div id="content_configure">
    <!--content_configure--></div>

    <div id="content_shipping_charges">
    {include file="views/shippings/components/rates.tpl" id=$id shipping=$shipping}
    <!--content_shipping_charges--></div>

    {hook name="shippings:tabs_content"}
    {/hook}

    {/capture}
    {include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox active_tab=$smarty.request.selected_section track=true}
{/if}

</form>
{/capture}{*mainbox*}

{if !$id}
    {assign var="title" value=__("new_shipping_method")}
{else}
    {assign var="title" value="{__("editing_shipping_method")}: `$shipping.shipping`"}
{/if}
{include file="common/mainbox.tpl" title=$title content=$smarty.capture.mainbox buttons=$smarty.capture.buttons select_languages=true}
