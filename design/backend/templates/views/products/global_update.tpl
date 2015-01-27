{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="global_update_form" class="form-horizontal form-edit  "/>

<p>{__("global_update_description")}</p>

<div class="control-group">
    <label class="control-label" for="gu_price">{__("price")}</label>
    <div class="controls">
    <input type="text" id="gu_price" name="update_data[price]" size="6" value="" />
    <select class="input-mini" name="update_data[price_type]">
        <option value="A" >{$currencies.$primary_currency.symbol nofilter}</option>
        <option value="P" >%</option>
    </select>
    </div>
</div>

{if !($runtime.company_id && "ULTIMATE"|fn_allowed_for)}
    <div class="control-group">
        <label for="gu_list_price" class="control-label">{__("list_price")}</label>
        <div class="controls">
        <input type="text" id="gu_list_price" name="update_data[list_price]" size="6" value="" />
        <select class="input-mini" name="update_data[list_price_type]">
            <option value="A" >{$currencies.$primary_currency.symbol nofilter}</option>
            <option value="P" >%</option>
        </select>
        </div>
    </div>

    <div class="control-group">
        <label for="gu_in_stock" class="control-label">{__("in_stock")}</label>
        <div class="controls">
        <input type="text" id="gu_in_stock" name="update_data[amount]" size="6" value="" /></div>
    </div>
{/if}

{hook name="products:global_update"}{/hook}

{include file="common/subheader.tpl" title=__("products")}

{include file="pickers/products/picker.tpl" type="links" input_name="update_data[product_ids]" no_item_text=__("text_all_products_included")}
</form>

{capture name="buttons"}
    {include file="buttons/button.tpl" but_target_form="global_update_form" but_text=__("apply") but_role="submit-link" but_name="dispatch[products.global_update]"}
{/capture}

{/capture}
{include file="common/mainbox.tpl" title=__("global_update") buttons=$smarty.capture.buttons content=$smarty.capture.mainbox}