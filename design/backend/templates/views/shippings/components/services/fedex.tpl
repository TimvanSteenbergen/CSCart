<fieldset>

<div class="control-group">
    <label class="control-label" for="user_key">{__("authentication_key")}</label>
    <div class="controls">
    <input id="user_key" type="text" name="shipping_data[service_params][user_key]" size="30" value="{$shipping.service_params.user_key}"/>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="user_key_password">{__("authentication_password")}</label>
    <div class="controls">
    <input id="user_key_password" type="text" name="shipping_data[service_params][user_key_password]" size="30" value="{$shipping.service_params.user_key_password}" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="account_number">{__("account_number")}</label>
    <div class="controls">
    <input id="account_number" type="text" name="shipping_data[service_params][account_number]" size="30" value="{$shipping.service_params.account_number}" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_fedex_meter_number">{__("ship_fedex_meter_number")}</label>
    <div class="controls">
    <input id="ship_fedex_meter_number" type="text" name="shipping_data[service_params][meter_number]" size="30" value="{$shipping.service_params.meter_number}" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="test_mode">{__("test_mode")}</label>
    <div class="controls">
    <input type="hidden" name="shipping_data[service_params][test_mode]" value="N" />
    <input id="test_mode" type="checkbox" name="shipping_data[service_params][test_mode]" value="Y" {if $shipping.service_params.test_mode == "Y"}checked="checked"{/if} />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="package_type">{__("package_type")}</label>
    <div class="controls">
    <select id="package_type" name="shipping_data[service_params][package_type]">
        <option value="YOUR_PACKAGING" {if $shipping.service_params.package_type == "YOUR_PACKAGING"}selected="selected"{/if}>{__("ship_fedex_package_type_your_packaging")}</option>
        <option value="FEDEX_BOX" {if $shipping.service_params.package_type == "FEDEX_BOX"}selected="selected"{/if}>{__("ship_fedex_package_type_fedex_box")}</option>
        <option value="FEDEX_10KG_BOX" {if $shipping.service_params.package_type == "FEDEX_10KG_BOX"}selected="selected"{/if}>{__("ship_fedex_package_type_fedex_10kg_box")}</option>
        <option value="FEDEX_25KG_BOX" {if $shipping.service_params.package_type == "FEDEX_25KG_BOX"}selected="selected"{/if}>{__("ship_fedex_package_type_fedex_25kg_box")}</option>
        <option value="FEDEX_ENVELOPE" {if $shipping.service_params.package_type == "FEDEX_ENVELOPE"}selected="selected"{/if}>{__("ship_fedex_package_type_fedex_envelope")}</option>
        <option value="FEDEX_PAK" {if $shipping.service_params.package_type == "FEDEX_PAK"}selected="selected"{/if}>{__("ship_fedex_package_type_fedex_pak")}</option>
        <option value="FEDEX_TUBE" {if $shipping.service_params.package_type == "FEDEX_TUBE"}selected="selected"{/if}>{__("ship_fedex_package_type_fedex_tube")}</option>
    </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_fedex_drop_off_type">{__("ship_fedex_drop_off_type")}</label>
    <div class="controls">
    <select id="ship_fedex_drop_off_type" name="shipping_data[service_params][drop_off_type]">
        <option value="REGULAR_PICKUP" {if $shipping.service_params.drop_off_type == "REGULAR_PICKUP"}selected="selected"{/if}>{__("ship_fedex_drop_off_type_regular_pickup")}</option>
        <option value="REQUEST_COURIER" {if $shipping.service_params.drop_off_type == "REQUEST_COURIER"}selected="selected"{/if}>{__("ship_fedex_drop_off_type_request_courier")}</option>
        <option value="STATION" {if $shipping.service_params.drop_off_type == "STATION"}selected="selected"{/if}>{__("ship_fedex_drop_off_type_station")}</option>
    </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="max_weight">{__("max_box_weight")}</label>
    <div class="controls">
    <input id="max_weight" type="text" name="shipping_data[service_params][max_weight_of_box]" size="30" value="{$shipping.service_params.max_weight_of_box|default:0}" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_fedex_height">{__("ship_fedex_height")}</label>
    <div class="controls">
    <input id="ship_fedex_height" type="text" name="shipping_data[service_params][height]" size="30" value="{$shipping.service_params.height}" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_fedex_width">{__("ship_fedex_width")}</label>
    <div class="controls">
    <input id="ship_fedex_width" type="text" name="shipping_data[service_params][width]" size="30" value="{$shipping.service_params.width}"/>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_fedex_length">{__("ship_fedex_length")}</label>
    <div class="controls">
    <input id="ship_fedex_length" type="text" name="shipping_data[service_params][length]" size="30" value="{$shipping.service_params.length}"/>
    </div>
</div>

{if $code == 'SMART_POST'}
{include file="common/subheader.tpl" title=__("ship_fedex_smart_post")}

<div class="control-group">
    <label class="control-label" for="package_type">{__("ship_fedex_indicia")}</label>
    <div class="controls">
    <select id="package_type" name="shipping_data[service_params][indicia]">
        <option value="PRESORTED_STANDARD" {if $shipping.service_params.indicia == "PRESORTED_STANDARD"}selected="selected"{/if}>{__("ship_fedex_indicia_presorted_standard")}</option>
        <option value="PARCEL_SELECT" {if $shipping.service_params.indicia == "PARCEL_SELECT"}selected="selected"{/if}>{__("ship_fedex_indicia_parcel_select")}</option>
        <option value="MEDIA_MAIL" {if $shipping.service_params.indicia == "MEDIA_MAIL"}selected="selected"{/if}>{__("ship_fedex_indicia_media_mail")}</option>
        <option value="PRESORTED_BOUND_PRINTED_MATTER" {if $shipping.service_params.indicia == "PRESORTED_BOUND_PRINTED_MATTER"}selected="selected"{/if}>{__("ship_fedex_indicia_presorted_bound_printed_matter")}</option>
    </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="package_type">{__("ship_fedex_ancillary_endorsement")}</label>
    <div class="controls">
    <select id="package_type" name="shipping_data[service_params][ancillary_endorsement]">
        <option value="" {if $shipping.service_params.ancillary_endorsement == ""}selected="selected"{/if}>{__("none")}</option>
        <option value="ADDRESS_CORRECTION" {if $shipping.service_params.ancillary_endorsement == "ADDRESS_CORRECTION"}selected="selected"{/if}>{__("ship_fedex_ancillary_endorsement_address_correction")}</option>
        <option value="CARRIER_LEAVE_IF_NO_RESPONSE" {if $shipping.service_params.ancillary_endorsement == "CARRIER_LEAVE_IF_NO_RESPONSE"}selected="selected"{/if}>{__("ship_fedex_ancillary_endorsement_carrier_leave_if_no_response")}</option>
        <option value="CHANGE_SERVICE" {if $shipping.service_params.ancillary_endorsement == "CHANGE_SERVICE"}selected="selected"{/if}>{__("ship_fedex_ancillary_endorsement_change_service")}</option>
        <option value="FORWARDING_SERVICE" {if $shipping.service_params.ancillary_endorsement == "FORWARDING_SERVICE"}selected="selected"{/if}>{__("ship_fedex_ancillary_endorsement_forwarding_service")}</option>
        <option value="RETURN_DELIVERY" {if $shipping.service_params.ancillary_endorsement == "RETURN_DELIVERY"}selected="selected"{/if}>{__("ship_fedex_ancillary_endorsement_return_delivery")}</option>
    </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="test_mode">{__("ship_fedex_special_services")}</label>
    <div class="controls">
    <input type="hidden" name="shipping_data[service_params][special_services]" value="N" />
    <input id="test_mode" type="checkbox" name="shipping_data[service_params][special_services]" value="Y" {if $shipping.service_params.special_services == "Y"}checked="checked"{/if}/>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_fedex_length">{__("ship_fedex_hub_id")}</label>
    <div class="controls">
    <input id="ship_fedex_length" type="text" name="shipping_data[service_params][hub_id]" size="30" value="{$shipping.service_params.hub_id}" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_fedex_length">{__("ship_fedex_customer_manifest_id")}</label>
    <div class="controls">
    <input id="ship_fedex_length" type="text" name="shipping_data[service_params][customer_manifest_id]" size="30" value="{$shipping.service_params.customer_manifest_id}" />
    </div>
</div>
{/if}

</fieldset>