<fieldset>

<div class="control-group">
    <label class='control-label' for="ship_can_merchant_id">{__("ship_can_merchant_id")}</label>
    <div class="controls">
    <input id="ship_can_merchant_id" type="text" name="shipping_data[service_params][merchant_id]" size="30" value="{$shipping.service_params.merchant_id}" />
    </div>
</div>

<div class="control-group">
    <label class='control-label' for="max_weight">{__("max_box_weight")}</label>
    <div class="controls">
    <input id="max_weight" type="text" name="shipping_data[service_params][max_weight_of_box]" size="30" value="{$shipping.service_params.max_weight_of_box|default:0}"/>
    </div>
</div>

<div class="control-group">
    <label class='control-label' for="ship_length">{__("ship_length")}</label>
    <div class="controls">
    <input id="ship_length" type="text" name="shipping_data[service_params][length]" size="30" value="{$shipping.service_params.length}"/>
    </div>
</div>

<div class="control-group">
    <label class='control-label' for="ship_width">{__("ship_width")}</label>
    <div class="controls">
    <input id="ship_width" type="text" name="shipping_data[service_params][width]" size="30" value="{$shipping.service_params.width}" />
    </div>
</div>

<div class="control-group">
    <label class='control-label' for="ship_height">{__("ship_height")}</label>
    <div class="controls">
    <input id="ship_height" type="text" name="shipping_data[service_params][height]" size="30" value="{$shipping.service_params.height}"/>
    </div>
</div>
    
</fieldset>