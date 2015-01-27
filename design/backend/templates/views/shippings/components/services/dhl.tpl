<fieldset>

<div class="control-group">
    <label class="control-label" for="ship_dhl_system_id">{__("ship_dhl_system_id")}</label>
    <div class="controls">
    <input id="ship_dhl_system_id" type="text" name="shipping_data[service_params][system_id]" size="30" value="{$shipping.service_params.system_id}"/>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="password">{__("password")}</label>
    <div class="controls">
    <input id="password" type="text" name="shipping_data[service_params][password]" size="30" value="{$shipping.service_params.password}"/>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="account_number">{__("account_number")}</label>
    <div class="controls">
    <input id="account_number" type="text" name="shipping_data[service_params][account_number]" size="30" value="{$shipping.service_params.account_number}"/>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_dhl_ship_key">{__("ship_dhl_ship_key")}</label>
    <div class="controls">
    <input id="ship_dhl_ship_key" type="text" name="shipping_data[service_params][ship_key]" size="30" value="{$shipping.service_params.ship_key}"/>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_dhl_intl_ship_key">{__("ship_dhl_intl_ship_key")}</label>
    <div class="controls">
    <input id="ship_dhl_intl_ship_key" type="text" name="shipping_data[service_params][intl_ship_key]" size="30" value="{$shipping.service_params.intl_ship_key}"/>
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
    <label class="control-label" for="max_weight">{__("max_box_weight")}</label>
    <div class="controls">
    <input id="max_weight" type="text" name="shipping_data[service_params][max_weight_of_box]" size="30" value="{$shipping.service_params.max_weight_of_box|default:0}"/>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_dhl_length">{__("ship_dhl_length")}</label>
    <div class="controls">
    <input id="ship_dhl_length" type="text" name="shipping_data[service_params][length]" size="30" value="{$shipping.service_params.length}"/>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_dhl_width">{__("ship_dhl_width")}</label>
    <div class="controls">
    <input id="ship_dhl_width" type="text" name="shipping_data[service_params][width]" size="30" value="{$shipping.service_params.width}"/>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_dhl_height">{__("ship_dhl_height")}</label>
    <div class="controls">
    <input id="ship_dhl_height" type="text" name="shipping_data[service_params][height]" size="30" value="{$shipping.service_params.height}"/>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_dhl_shipment_type">{__("ship_dhl_shipment_type")}</label>
    <div class="controls">
    <select id="ship_dhl_shipment_type" name="shipping_data[service_params][shipment_type]">
        <option value="L" {if $shipping.service_params.shipment_type == "L"}selected="selected"{/if}>{__("letter")}</option>
        <option value="P" {if $shipping.service_params.shipment_type == "P"}selected="selected"{/if}>{__("package")}</option>
    </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_dhl_additional_protection">{__("ship_dhl_additional_protection")}</label>
    <div class="controls">
    <select id="ship_dhl_additional_protection" name="shipping_data[service_params][additional_protection]">
        <option value="NR" {if $shipping.service_params.additional_protection == "NR"}selected="selected"{/if}>{__("ship_dhl_additional_protection_nr")}</option>
        <option value="AP" {if $shipping.service_params.additional_protection == "AP"}selected="selected"{/if}>{__("ship_dhl_additional_protection_ap")}</option>
    </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_dhl_ship_hazardous">{__("ship_dhl_ship_hazardous")}</label>
    <div class="controls">
    <input type="hidden" name="shipping_data[service_params][ship_hazardous]" value="N" />
    <input id="ship_dhl_ship_hazardous" type="checkbox" name="shipping_data[service_params][ship_hazardous]" value="Y" {if $shipping.service_params.ship_hazardous == "Y"}checked="checked"{/if} />
    </div>
</div>

{include file="common/subheader.tpl" title=__("cash_on_delivery")}

<div class="control-group">
    <label class="control-label" for="ship_dhl_cod_payment">{__("ship_dhl_cod_payment")}</label>
    <div class="controls">
    <input type="hidden" name="shipping_data[service_params][cod_payment]" value="N" />
    <input id="ship_dhl_cod_payment" type="checkbox" name="shipping_data[service_params][cod_payment]" value="Y" {if $shipping.service_params.cod_payment == "Y"}checked="checked"{/if} />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_dhl_cod_method">{__("ship_dhl_cod_method")}</label>
    <div class="controls">
    <select id="ship_dhl_cod_method" name="shipping_data[service_params][cod_method]">
        <option value="M" {if $shipping.service_params.cod_method == "M"}selected="selected"{/if}>{__("ship_dhl_cod_method_m")}</option>
        <option value="P" {if $shipping.service_params.cod_method == "P"}selected="selected"{/if}>{__("ship_dhl_cod_method_p")}</option>
    </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_dhl_cod_value">{__("ship_dhl_cod_value")}</label>
    <div class="controls">
    <input id="ship_dhl_cod_value" type="text" name="shipping_data[service_params][cod_value]" size="30" value="{$shipping.service_params.cod_value}" />
    </div>
</div>

</fieldset>