{if $filter_features}

{split data=$filter_features size="3" assign="splitted_filter" preverse_keys=true}

{capture name="filtering"}
<input type="hidden" name="advanced_filter" value="Y" />
{if $smarty.request.category_id}
<input type="hidden" name="category_id" value="{$smarty.request.category_id}" />
<input type="hidden" name="subcats" value="Y" />
{/if}

{if $smarty.request.variant_id}
<input type="hidden" name="variant_id" value="{$smarty.request.variant_id}" />
{/if}

<table class="table-filters table-width">
{foreach from=$splitted_filter item="filters_row" name="filters_row"}
<tr>
{foreach from=$filters_row item="filter"}
    <th>{$filter.filter|default:$filter.description|default:""}</th>
{/foreach}
</tr>
<tr class="valign-top{if ($splitted_filter|sizeof > 1) && $smarty.foreach.filters_row.first} delim{/if}">
{foreach from=$filters_row item="filter"}
    <td style="width: 33%">
        {if !isset($filter.feature_type) && !isset($filter.condition_type)}
            {continue}
        {/if}
        
        {if $filter.feature_type == "S" || $filter.feature_type == "E" || $filter.feature_type == "M"}
        <div class="scroll-y">
            {foreach from=$filter.ranges item="range"}
                <div class="select-field"><input type="checkbox" class="checkbox" name="{if $filter.feature_type == "M"}multiple_{/if}variants[]" id="variants_{$range.range_id}" value="{if $filter.feature_type == "M"}{$range.range_id}{else}[V{$range.range_id}]{/if}" {if "[V`$range.range_id`]"|in_array:$search.variants || $range.range_id|in_array:$search.multiple_variants}checked="checked"{/if} /><label for="variants_{$range.range_id}">{$filter.prefix}{$range.range_name}{$filter.suffix}</label></div>
            {/foreach}
        </div>
        {elseif $filter.feature_type == "O" || $filter.feature_type == "N" || $filter.feature_type == "D" || $filter.condition_type == "D" || $filter.condition_type == "F"}
            {if !$filter.slider}
            <div class="scroll-y">
            {/if}
                {if $filter.condition_type}
                    {assign var="el_id" value="field_`$filter.filter_id`"}
                {else}
                    {assign var="el_id" value="feature_`$filter.feature_id`"}
                {/if}
                <p{if !$filter.slider} class="select-field"{/if}><input type="radio" name="variants[{$el_id}]" id="no_ranges_{$el_id}" value="" checked="checked" class="radio" /><label for="no_ranges_{$el_id}">{__("none")}</label></p>
                {if !$filter.slider}
                    {foreach from=$filter.ranges item="range"}
                        {assign var="_type" value=$filter.field_type|default:"R"}
                        <div class="select-field"><input type="radio" class="radio" name="variants[{$el_id}]" id="ranges_{$el_id}{$range.range_id}" value="{$_type}{$range.range_id}" {if $search.variants.$el_id == "`$_type``$range.range_id`"}checked="checked"{/if} /><label for="ranges_{$el_id}{$range.range_id}">{$range.range_name|fn_text_placeholders}</label></div>
                    {/foreach}
                {/if}
            {if !$filter.slider}
            </div>
            {/if}
            
            {if $filter.condition_type != "F"}
            <p><input type="radio" name="variants[{$el_id}]" id="select_custom_{$el_id}" value="O" {if $search.variants[$el_id] == "O"}checked="checked"{/if} class="radio" /><label for="select_custom_{$el_id}">{__("your_range")}</label></p>
            
            <div class="select-field">
                {if $filter.feature_type == "D"}
                    {if $search.custom_range[$filter.feature_id].from || $search.custom_range[$filter.feature_id].to}
                        {assign var="date_extra" value=""}
                    {else}
                        {assign var="date_extra" value="disabled=\"disabled\""}
                    {/if}
                    {include file="common/calendar.tpl" date_id="range_`$el_id`_from" date_name="custom_range[`$filter.feature_id`][from]" date_val=$search.custom_range[$filter.feature_id].from extra=$date_extra start_year=$settings.Company.company_start_year}
                    {include file="common/calendar.tpl" date_id="range_`$el_id`_to" date_name="custom_range[`$filter.feature_id`][to]" date_val=$search.custom_range[$filter.feature_id].to extra=$date_extra start_year=$settings.Company.company_start_year}
                    <input type="hidden" name="custom_range[{$filter.feature_id}][type]" value="D" />
                {else}
                    {if !$filter.slider}
                        {assign var="from_value" value=$search.custom_range[$filter.feature_id].from|default:$search.field_range[$filter.field_type].from}
                        {assign var="to_value" value=$search.custom_range[$filter.feature_id].to|default:$search.field_range[$filter.field_type].to}
                    {else}
                        {assign var="from_value" value=$search.field_range[$filter.field_type].from|default:$filter.range_values.min}
                        {assign var="to_value" value=$search.field_range[$filter.field_type].to|default:$filter.range_values.max}
                        {if $filter.field_type == 'P'}
                            {assign var="cur" value=$search.field_range[$filter.field_type].cur|default:$secondary_currency}
                            {assign var="orig_from" value=$search.field_range[$filter.field_type].orig_from}
                            {assign var="orig_to" value=$search.field_range[$filter.field_type].orig_to}
                            {assign var="orig_cur" value=$search.field_range[$filter.field_type].orig_cur}
                        {/if}
                    {/if}

                    <input type="text" name="{if $filter.field_type}field_range[{$filter.field_type}]{else}custom_range[{$filter.feature_id}]{/if}[from]" id="range_{$el_id}_from" size="3" class="input-text-short{if $search.variants[$el_id] != "O"} disabled{/if}" value="{$from_value}" {if $search.variants[$el_id] != "O"}disabled="disabled"{/if} />
                    &nbsp;-&nbsp;
                    <input type="text" name="{if $filter.field_type}field_range[{$filter.field_type}]{else}custom_range[{$filter.feature_id}]{/if}[to]" size="3" class="input-text-short{if $search.variants[$el_id] != "O"} disabled{/if}" value="{$to_value}" id="range_{$el_id}_to" {if $search.variants[$el_id] != "O"}disabled="disabled"{/if} />
                    {if $filter.field_type == 'P'}
                        <input type="hidden" name="field_range[{$filter.field_type}][cur]" size="3" value="{$cur}" id="range_{$el_id}_cur" {if $search.variants[$el_id] != "O"}disabled="disabled"{/if} />
                        <input type="hidden" name="field_range[{$filter.field_type}][orig_from]" size="3" value="{$orig_from}" id="range_{$el_id}_orig_from" {if $search.variants[$el_id] != "O"}disabled="disabled"{/if} />
                        <input type="hidden" name="field_range[{$filter.field_type}][orig_to]" size="3"  value="{$orig_to}" id="range_{$el_id}_orig_to" {if $search.variants[$el_id] != "O"}disabled="disabled"{/if} />
                        <input type="hidden" name="field_range[{$filter.field_type}][orig_cur]" size="3" value="{$orig_cur}" id="range_{$el_id}_orig_cur" {if $search.variants[$el_id] != "O"}disabled="disabled"{/if} />
                    {/if}
                {/if}
            </div>
            {/if}
            <script type="text/javascript">
            //<![CDATA[
            Tygh.$(document).ready(function(){$ldelim}

                var $ = Tygh.$;
                $('input[type=radio][name="variants[{$el_id}]"]').change(function() {ldelim}
                    var el_id = '{$el_id}';
                    var flag = (this.value !== 'O');
                    $('#range_' + el_id + '_from').prop('disabled', flag).toggleClass('disabled', flag);
                    $('#range_' + el_id + '_to').prop('disabled', flag).toggleClass('disabled', flag);
                    {if $filter.field_type == 'P'}
                        $('#range_' + el_id + '_cur').prop('disabled', flag);
                        $('#range_' + el_id + '_orig_from').prop('disabled', flag);
                        $('#range_' + el_id + '_orig_to').prop('disabled', flag);
                        $('#range_' + el_id + '_orig_cur').prop('disabled', flag);
                    {/if}
                    {if $filter.feature_type == "D"}
                    $('#range_' + el_id + '_from_but').prop('disabled', flag);
                    $('#range_' + el_id + '_to_but').prop('disabled', flag);
                    {/if}
                {rdelim});
                {if $filter.field_type == 'P'}
                    $('#range_{$el_id}_to').change(function() {ldelim}
                        $('#range_{$el_id}_orig_cur').val('');
                    {rdelim});
                    $('#range_{$el_id}_from').change(function() {ldelim}
                        $('#range_{$el_id}_orig_cur').val('');
                    {rdelim});
                {/if}
            {$rdelim});
            //]]>
            </script>
        {elseif $filter.feature_type == "C" || $filter.condition_type == "C"}
            {if $filter.condition_type}
                {assign var="el_id" value=$filter.field_type}
            {else}
                {assign var="el_id" value=$filter.feature_id}
            {/if}
            <div class="select-field">
                <input type="radio" class="radio" name="ch_filters[{$el_id}]" id="ranges_{$el_id}_none" value="" {if !$search.ch_filters[$el_id]}checked="checked"{/if} />
                <label for="ranges_{$el_id}_none">{__("none")}</label>
            </div>
            
            <div class="select-field">
                <input type="radio" class="radio" name="ch_filters[{$el_id}]" id="ranges_{$el_id}_yes" value="Y" {if $search.ch_filters[$el_id] == "Y"}checked="checked"{/if} />
                <label for="ranges_{$el_id}_yes">{__("yes")}</label>
            </div>
            
            <div class="select-field">
                <input type="radio" class="radio" name="ch_filters[{$el_id}]" id="ranges_{$el_id}_no" value="N" {if $search.ch_filters[$el_id] == "N"}checked="checked"{/if} />
                <label for="ranges_{$el_id}_no">{__("no")}</label>
            </div>
            
            {if !$filter.condition_type}
            <div class="select-field">
                <input type="radio" class="radio" name="ch_filters[{$el_id}]" id="ranges_{$el_id}_any" value="A" {if $search.ch_filters[$el_id] == "A"}checked="checked"{/if} />
                <label for="ranges_{$el_id}_any">{__("any")}</label>
            </div>
            {/if}
            
        {elseif $filter.feature_type == "T"}
            <div class="select-field nowrap">
            {$filter.prefix}<input type="text" name="tx_features[{$filter.feature_id}]" class="input-text{if $filter.prefix || $filter.suffix}-medium{/if}" value="{$search.tx_features[$filter.feature_id]}" />{$filter.suffix}
            </div>
        {/if}
    </td>
{/foreach}
</tr>
{/foreach}
</table>
{/capture}

{if $separate_form}

{capture name="section"}
<form action="{""|fn_url}" method="get" name="advanced_filter_form">

{$smarty.capture.filtering nofilter}

<div class="buttons-container">
    {include file="buttons/button.tpl" but_name="dispatch[`$smarty.request.dispatch`]" but_text=__("submit")}
    &nbsp;{__("or")}&nbsp;&nbsp;<a class="text-button nobg cm-reset-link">{__("reset_filter")}</a>
</div>

</form>
{/capture}

{if $search.variants}
    {assign var="_collapse" value=true}
{else}
    {assign var="_collapse" value=false}
{/if}
{include file="common/section.tpl" section_title=__("advanced_filter") section_content=$smarty.capture.section collapse=$_collapse}

{else}

{include file="common/subheader.tpl" title=__("advanced_filter")}
{$smarty.capture.filtering nofilter}

{/if}

{elseif $search.features_hash}
    <input type="hidden" name="features_hash" value="{$search.features_hash}" />
{/if}


