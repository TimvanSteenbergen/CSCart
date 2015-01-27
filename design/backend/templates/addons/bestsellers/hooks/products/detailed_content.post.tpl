{include file="common/subheader.tpl" title=__("bestselling") target="#acc_bestsellers"}
<div id="acc_bestsellers" class="collapse in">
    <div class="control-group">
        <label class="control-label" for="sales_amount">{__("sales_amount")}</label>
        <div class="controls">
        <input type="text" id="sales_amount" name="product_data[sales_amount]" value="{$product_data.sales_amount|default:"0"}" class="input-large" size="10" />
        </div>
    </div>
</div>