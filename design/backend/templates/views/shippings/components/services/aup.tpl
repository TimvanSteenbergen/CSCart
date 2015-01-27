<fieldset>

<div class="control-group">
    <label class="control-label" for="max_weight">{__("max_box_weight")}</label>
    <div class="controls">
    <input id="max_weight" type="text" name="shipping_data[service_params][max_weight_of_box]" size="30" value="{$shipping.service_params.max_weight_of_box|default:0}" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_width">{__("ship_width")}</label>
    <div class="controls">
    <input id="ship_width" type="text" name="shipping_data[service_params][width]" size="30" value="{$shipping.service_params.width}" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_height">{__("ship_height")}</label>
    <div class="controls">
    <input id="ship_height" type="text" name="shipping_data[service_params][height]" size="30" value="{$shipping.service_params.height}" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_length">{__("ship_length")}</label>
    <div class="controls">
    <input id="ship_length" type="text" name="shipping_data[service_params][length]" size="30" value="{$shipping.service_params.length}" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_aup_use_delivery_confirmation">{__("ship_aup_use_delivery_confirmation")}</label>
    <div class="controls">
    <input type="hidden" name="shipping_data[service_params][use_delivery_confirmation]" value="N" />
    <input id="ship_aup_use_delivery_confirmation" type="checkbox" name="shipping_data[service_params][use_delivery_confirmation]" value="Y" {if $shipping.service_params.use_delivery_confirmation == "Y"}checked="checked"{/if} /></div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_aup_delivery_confirmation_cost">{__("ship_aup_delivery_confirmation_cost")}</label>
    <div class="controls">
    <input id="ship_aup_delivery_confirmation_cost" type="text" name="shipping_data[service_params][delivery_confirmation_cost]" size="30" value="{$shipping.service_params.delivery_confirmation_cost}"/>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_aup_delivery_confirmation_international_cost">{__("ship_aup_delivery_confirmation_international_cost")}</label>
    <div class="controls">
    <input id="ship_aup_delivery_confirmation_international_cost" type="text" name="shipping_data[service_params][delivery_confirmation_international_cost]" size="30" value="{$shipping.service_params.delivery_confirmation_international_cost}"/>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_aup_rpi_fee">{__("ship_aup_rpi_fee")}</label>
    <div class="controls">
    <input id="ship_aup_rpi_fee" type="text" name="shipping_data[service_params][rpi_fee]" size="30" value="{$shipping.service_params.rpi_fee}"/>
    </div>
</div>

</fieldset>