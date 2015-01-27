{if $feature.prefix}<span>{$feature.prefix}</span>{/if}
{if $feature.feature_type == "S" || $feature.feature_type == "N" || $feature.feature_type == "E"}
    {if $feature.use_variant_picker}
        {assign var="suffix" value=$data_name|md5}
        
        {if $over}
            {assign var="input_id" value="field_`$field`__`$feature.feature_id`_"}
        {else}
            {assign var="input_id" value="feature_`$feature.feature_id`_`$suffix`"}
        {/if}    
        
        <input type="hidden" name="{$data_name}[product_features][{$feature.feature_id}]" id="{$input_id}" value="{$selected|default:$feature.variant_id}" {if $over} disabled="disabled"{/if} />
        {if $feature.variants[$feature.variant_id].variant}
            {assign var="selected_variant" value=$feature.variants[$feature.variant_id].variant}
        {elseif $feature.variant_id}
            {assign var="selected_variant" value=$feature.variant_id|fn_get_product_feature_variant}
            {assign var="selected_variant" value=$selected_variant.variant}
        {else}
            {assign var="selected_variant" value=__("none")}
        {/if}
        {include file="common/ajax_select_object.tpl" data_url="product_features.get_feature_variants_list?feature_id=`$feature.feature_id`&enter_other=N" text=$selected_variant result_elm=$input_id id="`$feature.feature_id`_selector_`$suffix`"}
    {else}
        <select name="{$data_name}[product_features][{$feature.feature_id}]" {if $over}id="field_{$field}__{$feature.feature_id}_" disabled="disabled" class="elm-disabled"{/if}>
            <option value="">-{__("none")}-</option>
            {foreach from=$feature.variants item="var"}
            <option value="{$var.variant_id}" {if $var.variant_id == $feature.variant_id}selected="selected"{/if}>{$var.variant}</option>
            {/foreach}
        </select>
    {/if}
{elseif $feature.feature_type == "M"}
        <input type="hidden" name="{$data_name}[product_features][{$feature.feature_id}]" value="" {if $over}id="field_{$field}__{$feature.feature_id}_" disabled="disabled"{/if} />
        {foreach from=$feature.variants item="var"}
            <div class="select-field">
                <input type="checkbox" name="{$data_name}[product_features][{$feature.feature_id}][{$var.variant_id}]" value="{$var.variant_id}" class="checkbox{if $over} elm-disabled{/if}" id="field_{$field}__{$feature.feature_id}_{$var.variant_id}_{$data_name|md5}" {if $over} disabled="disabled"{/if} {if $var.selected}checked="checked"{/if} />
                <label for="field_{$field}__{$feature.feature_id}_{$var.variant_id}_{$data_name|md5}">{$var.variant}</label>
            </div>
        {/foreach}
{elseif $feature.feature_type == "C"}
    <input type="hidden" name="{$data_name}[product_features][{$feature.feature_id}]" value="N" {if $over}disabled="disabled" id="field_{$field}__{$feature.feature_id}_copy"{/if} />
    <input type="checkbox" name="{$data_name}[product_features][{$feature.feature_id}]" value="Y" {if $over}id="field_{$field}__{$feature.feature_id}_" disabled="disabled" class="elm-disabled"{/if} {if $feature.value == "Y"}checked="checked"{/if} />
{elseif $feature.feature_type == "D"}
    {if $over}
        {assign var="date_id" value="field_`$field`__`$feature.feature_id`_"}
        {assign var="date_extra" value=" disabled=\"disabled\""}
        {assign var="d_meta" value="input-text-disabled"}
    {else}
        {assign var="date_id" value="date_`$pid``$feature.feature_id`"}
        {assign var="date_extra" value=""}
        {assign var="d_meta" value=""}
    {/if}
    {$feature.value}{include file="common/calendar.tpl" date_id=$date_id date_name="`$data_name`[product_features][`$feature.feature_id`]" date_val=$feature.value_int start_year=$settings.Company.company_start_year extra=$date_extra date_meta=$d_meta}
{else}
    <input type="text" name="{$data_name}[product_features][{$feature.feature_id}]" value="{if $feature.feature_type == "O"}{if $feature.value_int != ""}{$feature.value_int|floatval}{/if}{else}{$feature.value}{/if}" {if $over} id="field_{$field}__{$feature.feature_id}_" disabled="disabled"{/if} class="input-text {if $over}input-text-disabled{/if} {if $feature.feature_type == "O"}cm-value-decimal{/if}" />
{/if}
{if $feature.suffix}<span>{$feature.suffix}</span>{/if}
<input type="hidden" name="{$data_name}[active_features][]" value="{$feature.feature_id}" />