<fieldset>

<script type="text/javascript">
    var values = {ldelim}
	    'Household Goods' : {ldelim}
    		'Box' : '{__("temando_package_box")|escape:"javascript"}',
	    	'Carton' : '{__("temando_package_carton")|escape:"javascript"}',
		    'Crate' : '{__("temando_package_crate")|escape:"javascript"}',
    		'Cylinder' : '{__("temando_package_cylinder")|escape:"javascript"}',
	    	'Document Envelope' : '{__("temando_package_documentenvelope")|escape:"javascript"}',
		    'Flat Pack' : '{__("temando_package_flatpack")|escape:"javascript"}',
    		'Letter' : '{__("temando_package_letter")|escape:"javascript"}',
	    	'Pallet' : '{__("temando_package_pallet")|escape:"javascript"}',
		    'Parcel' : '{__("temando_package_parcel")|escape:"javascript"}',
    		'Satchel/Bag' : '{__("temando_package_satchel")|escape:"javascript"}',
	    	'Skid' : '{__("temando_package_skid")|escape:"javascript"}',
		    'Unpackaged or N/A' : '{__("temando_package_unpackaged")|escape:"javascript"}',
    		'Wheel/Tyre' : '{__("temando_package_wheel")|escape:"javascript"}'
	    {rdelim},
        'Excess Baggage' : {ldelim}
    		'Backpack' : '{__("temando_package_backpack")|escape:"javascript"}',
	    	'Box' : '{__("temando_package_box")|escape:"javascript"}',
		    'Carton' : '{__("temando_package_carton")|escape:"javascript"}',
    		'Suitcase' : '{__("temando_package_suitcase")|escape:"javascript"}'
	    {rdelim},
        'Furniture' : {ldelim}
    		'Box' : '{__("temando_package_box")|escape:"javascript"}',
	    	'Carton' : '{__("temando_package_carton")|escape:"javascript"}',
		    'Crate' : '{__("temando_package_crate")|escape:"javascript"}',
    		'Flat Pack' : '{__("temando_package_flatpack")|escape:"javascript"}',
	    	'Pallet' : '{__("temando_package_pallet")|escape:"javascript"}',
		    'Skid' : '{__("temando_package_skid")|escape:"javascript"}',
    		'Unpackaged or N/A' : '{__("temando_package_unpackaged")|escape:"javascript"}'
	    {rdelim},
        'Other (Etc.)' : {ldelim}
    		'Box' : '{__("temando_package_box")|escape:"javascript"}',
	    	'Carton' : '{__("temando_package_carton")|escape:"javascript"}',
		    'Crate' : '{__("temando_package_crate")|escape:"javascript"}',
    		'Cylinder' : '{__("temando_package_cylinder")|escape:"javascript"}',
	    	'Document Envelope' : '{__("temando_package_documentenvelope")|escape:"javascript"}',
		    'Flat Pack' : '{__("temando_package_flatpack")|escape:"javascript"}',
    		'Letter' : '{__("temando_package_letter")|escape:"javascript"}',
	    	'Pallet' : '{__("temando_package_pallet")|escape:"javascript"}',
		    'Parcel' : '{__("temando_package_parcel")|escape:"javascript"}',
    		'Pipe' : '{__("temando_package_pipe")|escape:"javascript"}',
	    	'Satchel/Bag' : '{__("temando_package_satchel")|escape:"javascript"}',
		    'Skid' : '{__("temando_package_skid")|escape:"javascript"}',
    		'Tube' : '{__("temando_package_tube")|escape:"javascript"}',
	    	'Unpackaged or N/A' : '{__("temando_package_unpackaged")|escape:"javascript"}',
		    'Wheel/Tyre' : '{__("temando_package_wheel")|escape:"javascript"}'
    	{rdelim}
    {rdelim}
{literal}
Tygh.$(document).ready(function(){
    fn_temando_change_packages(Tygh.$('#ship_temando_subslass').val());
});

Tygh.$('#ship_temando_subslass').on('change', function(){
    fn_temando_change_packages(Tygh.$(this).val());
});

function fn_temando_change_packages(subclass_value)
{
    Tygh.$('#ship_temando_package').html('');
    var packages = values[subclass_value];
    var options = '';
    Tygh.$.each(packages, function(i, val){
        options += '<option value="' + i + '">' + val + '</option>';
    });
    Tygh.$('#ship_temando_package').html(options);
}
{/literal}
</script>

<div class="control-group">
	<label class="control-label" for="username">{__("username")}:</label>
	<div class="controls">
	<input id="username" type="text" name="shipping_data[service_params][username]" size="30" value="{$shipping.service_params.username}" class="input-text" />
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="password">{__("password")}:</label>
	<div class="controls">
	<input id="password" type="text" name="shipping_data[service_params][password]" size="30" value="{$shipping.service_params.password}" class="input-text" />
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="test_mode">{__("test_mode")}:</label>
	<div class="controls">
	<input type="hidden" name="shipping_data[service_params][test_mode]" value="N" />
	<input id="test_mode" type="checkbox" name="shipping_data[service_params][test_mode]" value="Y" {if $shipping.service_params.test_mode == "Y"}checked="checked"{/if} class="checkbox" />
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="ship_temando_method">{__("ship_temando_method")}:</label>
	<div class="controls">
	<select id="ship_temando_method" name="shipping_data[service_params][temando_method]">
		<option value="Door to Door" {if $shipping.service_params.temando_method == "Door to Door"}selected="selected"{/if}>{__("temando_method_doortodoor")}</option>
		<option value="Depot to Depot" {if $shipping.service_params.temando_method == "Depot to Depot"}selected="selected"{/if}>{__("temando_method_depottodepot")}</option>
	</select>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="ship_temando_weight_measurement">{__("ship_temando_weight_measurement")}:</label>
	<div class="controls">
	<select id="ship_temando_weight_measurement" name="shipping_data[service_params][temando_weight]">
		<option value="Grams" {if $shipping.service_params.temando_weight == "Grams"}selected="selected"{/if}>{__("temando_weight_grams")}</option>
		<option value="Kilograms" {if $shipping.service_params.temando_weight == "Kilograms"}selected="selected"{/if}>{__("temando_weight_kilograms")}</option>
		<option value="Ounces" {if $shipping.service_params.temando_weight == "Ounces"}selected="selected"{/if}>{__("temando_weight_ounces")}</option>
		<option value="Pounds" {if $shipping.service_params.temando_weight == "Pounds"}selected="selected"{/if}>{__("temando_weight_pounds")}</option>
	</select>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="ship_temando_fragile">{__("fragile")}:</label>
	<div class="controls">
	<input type="hidden" name="shipping_data[service_params][temando_fragile]" value="N" />
	<input id="ship_temando_fragile" type="checkbox" name="shipping_data[service_params][temando_fragile]" value="Y" {if $shipping.service_params.temando_fragile == "Y"}checked="checked"{/if} class="checkbox" />
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="ship_temando_measurement">{__("ship_temando_measurement")}:</label>
	<div class="controls">
	<select id="ship_temando_measurement" name="shipping_data[service_params][temando_measurement]">
		<option value="Centimetres" {if $shipping.service_params.temando_measurement == "Centimetres"}selected="selected"{/if}>{__("temando_centimetres")}</option>
		<option value="Metres" {if $shipping.service_params.temando_measurement == "Metres"}selected="selected"{/if}>{__("temando_metres")}</option>
		<option value="Inches" {if $shipping.service_params.temando_measurement == "Inches"}selected="selected"{/if}>{__("temando_inches")}</option>
		<option value="Feet" {if $shipping.service_params.temando_measurement == "Feet"}selected="selected"{/if}>{__("temando_feet")}</option>
	</select>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="ship_temando_height">{__("ship_temando_height")}:</label>
	<div class="controls">
	<input id="ship_temando_height" type="text" name="shipping_data[service_params][temando_height]" size="30" value="{$shipping.service_params.temando_height}" class="input-text" />
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="ship_temando_width">{__("ship_temando_width")}:</label>
	<div class="controls">
	<input id="ship_temando_width" type="text" name="shipping_data[service_params][temando_width]" size="30" value="{$shipping.service_params.temando_width}" class="input-text" />
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="ship_temando_length">{__("ship_temando_length")}:</label>
	<div class="controls">
	<input id="ship_temando_length" type="text" name="shipping_data[service_params][temando_length]" size="30" value="{$shipping.service_params.temando_length}" class="input-text" />
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="ship_temando_subclass">{__("ship_temando_subclass")}:</label>
	<div class="controls">
	<select id="ship_temando_subslass" name="shipping_data[service_params][temando_subclass]">
		<option value="Household Goods" {if $shipping.service_params.temando_subclass == "Household Goods"}selected="selected"{/if}>{__("temando_subclass_householdgoods")}</option>
		<option value="Excess Baggage" {if $shipping.service_params.temando_subclass == "Excess Baggage"}selected="selected"{/if}>{__("temando_subclass_excessbaggage")}</option>
		<option value="Furniture" {if $shipping.service_params.temando_subclass == "Furniture"}selected="selected"{/if}>{__("temando_subclass_furniture")}</option>
		<option value="Other (Etc.)" {if $shipping.service_params.temando_subclass == "Other (Etc.)"}selected="selected"{/if}>{__("temando_subclass_other")}</option>
	</select>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="ship_temando_package">{__("ship_temando_package")}:</label>
	<div class="controls">
	<select id="ship_temando_package" name="shipping_data[service_params][temando_package]"></select>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="ship_temando_readydate">{__("ship_temando_readydate")}:</label>
	<div class="controls">
	<input id="ship_temando_readydate" type="text" name="shipping_data[service_params][temando_readydate]" size="30" value="{$shipping.service_params.temando_readydate}" class="input-text" />
	</div>
</div>

</fieldset>
