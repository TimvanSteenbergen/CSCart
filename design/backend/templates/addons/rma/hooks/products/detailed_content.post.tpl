{include file="common/subheader.tpl" title=__("rma") target="#acc_addon_rma"}
<div id="acc_addon_rma" class="collapse in">
<div class="control-group">
    <label class="control-label" for="is_returnable">{__("returnable")}:</label>
    <div class="controls">
        <label class="checkbox">
        <input type="hidden" name="product_data[is_returnable]" id="is_returnable" value="N" />
        <input type="checkbox" name="product_data[is_returnable]" value="Y" {if $product_data.is_returnable == "Y" || $runtime.mode == "add"}checked="checked"{/if} onclick="Tygh.$.disable_elms(['return_period'], !this.checked);" />
        </label>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="return_period">{__("return_period_days")}:</label>
    <div class="controls">
        <input type="text" id="return_period" name="product_data[return_period]" value="{$product_data.return_period|default:"10"}" size="10"  {if $product_data.is_returnable != "Y" && $runtime.mode != "add"}disabled="disabled"{/if} />
    </div>
</div>
</div>
