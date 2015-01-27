{include file="common/subheader.tpl" title=__("catalog_mode") target="#catalog_mode_product"}

<div id="catalog_mode_product" class="in collapse">
	<fieldset>
	    <div class="control-group">
	        <label class="control-label" for="buy_now_url">{__("buy_now_url")}:</label>
	        <div class="controls">
	        	<input type="text" id="buy_now_url" name="product_data[buy_now_url]" value="{$product_data.buy_now_url|default:""}" size="40">
	        </div>
	    </div>
	</fieldset>
</div>