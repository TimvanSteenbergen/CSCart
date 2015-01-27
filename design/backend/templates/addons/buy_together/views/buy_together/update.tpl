{if $item.chain_id}
    {assign var="id" value=$item.chain_id}
{else}
    {assign var="id" value=''}
    {assign var="extra_mode" value="buy_together"}
{/if}

{if $item|fn_allow_save_object:"chains" && ($runtime.company_id || $runtime.simple_ultimate)}
    {assign var="allow_save" value=true}
{else}
    {assign var="allow_save" value=false}
{/if}

{if $allow_save}
    {assign var="no_hide_inputs" value=" cm-no-hide-input"}
{/if}

{if $item.product_id}
    {assign var="product_id" value=$item.product_id}
{else}
    {assign var="product_id" value=$product_id}
{/if}

<div id="content_group_bt_{$id}">

<form action="{""|fn_url}" method="post" name="item_update_form_bt_{$id}" class="{if !$allow_save} cm-hide-inputs{/if} form-horizontal form-edit" enctype="multipart/form-data">
<input type="hidden" class="cm-no-hide-input" name="fake" value="1" />
<input type="hidden" class="cm-no-hide-input" name="item_id" value="{$id}" />
<input type="hidden" class="cm-no-hide-input" name="product_id" value="{$product_id}" />

<div class="tabs cm-j-tabs">
    <ul class="nav nav-tabs">
        <li id="tab_general_{$id}" class="cm-js active"><a>{__("general")}</a></li>
        <li id="tab_products_{$id}" class="cm-js"><a>{__("products")}</a></li>
    </ul>
</div>

<div class="cm-tabs-content" id="tabs_content_{$id}">
    <fieldset>
        <div id="content_tab_general_{$id}">
            <div class="control-group {$no_hide_inputs}">
                <label for="elm_buy_together_name_{$id}" class="control-label cm-required">{__("name")}:</label>
                <div class="controls">
                    <input type="text" name="item_data[name]" id="elm_buy_together_name_{$id}" size="55" value="{$item.name}" class="span9">
                </div>
            </div>
            
            <div class="control-group {$no_hide_inputs}">
                <label class="control-label" for="elm_buy_together_description_{$id}">{__("description")}:</label>
                <div class="controls">
                        <textarea id="elm_buy_together_description_{$id}" name="item_data[description]" cols="55" rows="8" class="cm-wysiwyg input-textarea-long">{$item.description}</textarea>
                </div>
            </div>
            
            <div class="control-group {$no_hide_inputs}">
                <label class="control-label" for="elm_buy_together_avail_from_{$id}">{__("avail_from")}:</label>
                <div class="controls">
                    {include file="common/calendar.tpl" date_id="elm_buy_together_avail_from_`$id`" date_name="item_data[date_from]" date_val=$item.date_from|default:$smarty.const.TIME start_year=$settings.Company.company_start_year}
                </div>
            </div>
            
            <div class="control-group {$no_hide_inputs}">
                <label class="control-label" for="elm_buy_together_avail_till_{$id}">{__("avail_till")}:</label>
                <div class="controls">
                    {include file="common/calendar.tpl" date_id="elm_buy_together_avail_till_`$id`" date_name="item_data[date_to]" date_val=$item.date_to|default:$smarty.const.TIME start_year=$settings.Company.company_start_year}
                </div>
            </div>
            
            <div class="control-group {$no_hide_inputs}">
                <label class="control-label" for="elm_buy_together_promotions_{$id}">{__("display_in_promotions")}:</label>
               <div class="controls">
                    <input type="hidden" name="item_data[display_in_promotions]" value="N">
                    <input type="checkbox" name="item_data[display_in_promotions]" id="elm_buy_together_promotions_{$id}" value="Y" {if $item.display_in_promotions == "Y"}checked="checked"{/if}>
                </div>
            </div>
            
            {include file="common/select_status.tpl" input_name="item_data[status]" id="elm_buy_together_status_`$id`" obj=$item hidden=false}
        </div>
        
        <div id="content_tab_products_{$id}" {if $no_hide_inputs}class="{$no_hide_inputs}"{/if}>
            {include file="common/subheader.tpl" title=__("combination_products")}
            
            {include file="pickers/products/picker.tpl" data_id="objects_`$id`_" input_name="item_data[products]" item_ids=$item.products_info type="table" aoc=true colspan="7" placement="right"}
            
            <ul class="pull-right unstyled right span6">
            {if $allow_save}
                <li>
                    <a class="btn" onclick="fn_buy_together_recalculate('{$id}');">{__("recalculate")}</a><br><br>
                </li>
            {/if}
                <li>
                    <em>{__("total_cost")}:</em>
                    <strong>{include file="common/price.tpl" value=$item.total_price span_id="total_price_`$id`"}</strong>
                </li>
                <li>
                    <em>{__("price_for_all")}:</em>
                    <strong>{include file="common/price.tpl" value=$item.chain_price span_id="price_for_all_`$id`"}</strong>
                </li>
            {if $allow_save}
                <li><br>
                    <label for="elm_buy_together_global_discount_{$id}"><em>{__("share_discount")}&nbsp;({$currencies.$primary_currency.symbol nofilter}):</em>&nbsp;<input type="text" class="input-mini" size="4" id="elm_buy_together_global_discount_{$id}" onkeypress="fn_buy_together_share_discount(event, '{$id}');" />&nbsp;<a onclick="fn_buy_together_apply_discount('{$id}');" class="btn">{__("apply")}</a></label>
                </li>
            {/if}
            </ul>            
        </div>
    </fieldset>
</div>

<div class="buttons-container">    
    {if !$id}
        {include file="buttons/save_cancel.tpl" but_name="dispatch[buy_together.update]" cancel_action="close"}
    {else}
        {if "MULTIVENDOR"|fn_allowed_for}
            {if !$runtime.company_id}
                {assign var="hide_first_button" value=true}
            {/if}
        {/if}

        {if "ULTIMATE"|fn_allowed_for && !$allow_save}
            {assign var="hide_first_button" value=true}
            {assign var="hide_second_button" value=true}
        {/if}
        {include file="buttons/save_cancel.tpl" but_name="dispatch[buy_together.update]" cancel_action="close" hide_first_button=$hide_first_button hide_second_button=$hide_second_button save=$id}
    {/if}
</div>

</form>

<!--content_group_bt_{$id}--></div>