{include file="common/subheader.tpl" title=__("general")}

<div class="control-group">
    <label class="control-label" for="product_weight">{__("weight")} ({$settings.General.weight_symbol nofilter}):</label>
    <div class="controls">
        <input type="text" name="product_data[weight]" id="product_weight" size="10" value="{$product_data.weight|default:"0"}" class="input-long" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="product_free_shipping">{__("free_shipping")}:</label>
    <div class="controls">
        <input type="hidden" name="product_data[free_shipping]" value="N" />
        <input type="checkbox" name="product_data[free_shipping]" id="product_free_shipping" value="Y" {if $product_data.free_shipping == "Y"}checked="checked"{/if} />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="product_shipping_freight">{__("shipping_freight")} ({$currencies.$primary_currency.symbol nofilter}):</label>
    <div class="controls">
        <input type="text" name="product_data[shipping_freight]" id="product_shipping_freight" size="10" value="{$product_data.shipping_freight|default:"0.00"}" class="input-long" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="product_items_in_box">{__("items_in_box")}:</label>
    <div class="controls">
        <input type="text" name="product_data[min_items_in_box]" id="product_items_in_box" size="5" value="{$product_data.min_items_in_box|default:"0"}" class="input-micro" onkeyup="fn_product_shipping_settings(this);" />
        &nbsp;-&nbsp;
        <input type="text" name="product_data[max_items_in_box]" size="5" value="{$product_data.max_items_in_box|default:"0"}" class="input-micro" onkeyup="fn_product_shipping_settings(this);" />
    </div>
    
    {if $product_data.min_items_in_box > 0 || $product_data.max_items_in_box}
        {assign var="box_settings" value=true}
    {/if}
</div>

<div class="control-group">
    <label class="control-label" for="product_box_length">{__("box_length")}:</label>
    <div class="controls">
        <input type="text" name="product_data[box_length]" id="product_box_length" size="10" value="{$product_data.box_length|default:"0"}" class="input-long shipping-dependence" {if !$box_settings}disabled="disabled"{/if} />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="product_box_width">{__("box_width")}:</label>
    <div class="controls">
        <input type="text" name="product_data[box_width]" id="product_box_width" size="10" value="{$product_data.box_width|default:"0"}" class="input-long shipping-dependence" {if !$box_settings}disabled="disabled"{/if} />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="product_box_height">{__("box_height")}:</label>
    <div class="controls">
        <input type="text" name="product_data[box_height]" id="product_box_height" size="10" value="{$product_data.box_height|default:"0"}" class="input-long shipping-dependence" {if !$box_settings}disabled="disabled"{/if} />
    </div>
</div>

<script type="text/javascript">
{literal}
function fn_product_shipping_settings(elm)
{
    var jelm = Tygh.$(elm);
    var available = false;
    
    Tygh.$('input', jelm.parent()).each(function() {
        if (parseInt(Tygh.$(this).val()) > 0) {
            available = true;
        }
    });
    
    Tygh.$('input.shipping-dependence').prop('disabled', (available ? false : true));
    
}

{/literal}
</script>
