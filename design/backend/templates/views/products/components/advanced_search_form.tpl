{split data=$filter_features size="3" assign="splitted_filter" preverse_keys=true}
<table cellpadding="8">
{foreach from=$splitted_filter item="filters_row" name="filters_row"}
<thead>
    <tr>
    {foreach from=$filters_row item="filter"}
        {if $filter && $filter.field_type != "P"}
        <td><strong>{$filter.filter|default:$filter.description}</strong></td>
        {/if}
    {/foreach}
    </tr>
</thead>
<tr valign="top"{if ($splitted_filter|sizeof > 1) && $smarty.foreach.filters_row.first} class="delim"{/if}>
{foreach from=$filters_row item="filter"}
    {if $filter && $filter.field_type != "P"}
        <td width="33%">
            {if $filter.feature_type == "S" || $filter.feature_type == "E" || $filter.feature_type == "M" || $filter.feature_type == "N" && !$filter.filter_id}
                <div class="scroll-y">
                    {assign var="filter_ranges" value=$filter.ranges|default:$filter.variants}
                    {foreach from=$filter_ranges key="range_id" item="range"}
                        <label for="variants_{$range_id}" class="checkbox"><input type="checkbox" name="{if $filter.feature_type == "M"}multiple_{/if}variants[]" id="{$prefix}variants_{$range_id}" value="{if $filter.feature_type == "M"}{$range_id}{else}[V{$range_id}]{/if}" {if "[V`$range_id`]"|in_array:$search.variants || $range_id|in_array:$search.multiple_variants}checked="checked"{/if} />{$filter.prefix}{$range.variant}{$filter.suffix}</label>
                    {/foreach}
                </div>
            {elseif $filter.feature_type == "O" || $filter.feature_type == "N" && $filter.filter_id || $filter.feature_type == "D" || $filter.condition_type == "D" || $filter.condition_type == "F"}
                {if !$filter.slider}<div class="scroll-y">{/if}
                    {if $filter.condition_type}
                        {assign var="el_id" value="field_`$filter.filter_id`"}
                    {else}
                        {assign var="el_id" value="feature_`$filter.feature_id`"}
                    {/if}

                    <label for="{$prefix}no_ranges_{$el_id}" class="radio"><input type="radio" name="variants[{$el_id}]" id="{$prefix}no_ranges_{$el_id}" value="" checked="checked" />{__("none")}</label>
                    {assign var="filter_ranges" value=$filter.ranges|default:$filter.variants}
                    {assign var="_type" value=$filter.field_type|default:"R"}
                    {if !$filter.slider}
                        {foreach from=$filter_ranges key="range_id" item="range"}
                            {assign var="range_name" value=$range.range_name|default:$range.variant}
                            <label for="{$prefix}ranges_{$el_id}{$range_id}" class="radio"><input type="radio" name="variants[{$el_id}]" id="{$prefix}ranges_{$el_id}{$range_id}" value="{$_type}{$range_id}" {if $search.variants.$el_id == "`$_type``$range_id`"}checked="checked"{/if} />{$range_name|fn_text_placeholders}</label>
                        {/foreach}
                    {/if}
                {if !$filter.slider}</div>{/if}

                {if $filter.condition_type != "F"}
                <label for="{$prefix}select_custom_{$el_id}" class="radio"><input type="radio" name="variants[{$el_id}]" id="{$prefix}select_custom_{$el_id}" value="O" {if $search.variants[$el_id] == "O"}checked="checked"{/if}  />{__("your_range")}</label>

                    {if $filter.feature_type == "D"}
                        {if $search.custom_range[$filter.feature_id].from || $search.custom_range[$filter.feature_id].to}
                            {assign var="date_extra" value=""}
                        {else}
                            {assign var="date_extra" value="disabled=\"disabled\""}
                        {/if}
                        {include file="common/calendar.tpl" date_id="`$prefix`range_`$el_id`_from" date_name="custom_range[`$filter.feature_id`][from]" date_val=$search.custom_range[$filter.feature_id].from extra=$date_extra start_year=$settings.Company.company_start_year}
                        {include file="common/calendar.tpl" date_id="`$prefix`range_`$el_id`_to" date_name="custom_range[`$filter.feature_id`][to]" date_val=$search.custom_range[$filter.feature_id].to extra=$date_extra start_year=$settings.Company.company_start_year}
                        <input type="hidden" name="custom_range[{$filter.feature_id}][type]" value="D" />
                    {else}
                        {if !$filter.slider}
                            {assign var="from_value" value=$search.custom_range[$filter.feature_id].from|default:$search.field_range[$filter.field_type].from}
                            {assign var="to_value" value=$search.custom_range[$filter.feature_id].to|default:$search.field_range[$filter.field_type].to}
                        {else}
                            {assign var="from_value" value=$search.field_range[$filter.field_type].from|default:$filter.range_values.min}
                            {assign var="to_value" value=$search.field_range[$filter.field_type].to|default:$filter.range_values.max}
                        {/if}

                        <input type="text" name="{if $filter.field_type}field_range[{$filter.field_type}]{else}custom_range[{$filter.feature_id}]{/if}[from]" id="{$prefix}range_{$el_id}_from" size="3" class="input-mini" value="{$from_value}" {if $search.variants[$el_id] != "O"}disabled="disabled"{/if} /> - <input type="text" name="{if $filter.field_type}field_range[{$filter.field_type}]{else}custom_range[{$filter.feature_id}]{/if}[to]" size="3" class="input-mini" value="{$to_value}" id="{$prefix}range_{$el_id}_to" {if $search.variants[$el_id] != "O"}disabled="disabled"{/if} />
                    {/if}
                {/if}
                <script type="text/javascript">
                Tygh.$(document).ready(function() {ldelim}
                    var $ = Tygh.$;
                    $("input[type=radio][name='variants[{$el_id}]']").change(function() {ldelim}
                        var el_id = '{$el_id}';
                        $('#{$prefix}range_' + el_id + '_from').prop('disabled', this.value !== 'O');
                        $('#{$prefix}range_' + el_id + '_to').prop('disabled', this.value !== 'O');
                        {if $filter.feature_type == "D"}
                        $('#{$prefix}range_' + el_id + '_from_but').prop('disabled', this.value !== 'O');
                        $('#{$prefix}range_' + el_id + '_to_but').prop('disabled', this.value !== 'O');
                        {/if}
                    {rdelim});
                {rdelim});
                </script>
            {elseif $filter.feature_type == "C" || $filter.condition_type == "C"}
                {if $filter.condition_type}
                    {assign var="el_id" value=$filter.field_type}
                {else}
                    {assign var="el_id" value=$filter.feature_id}
                {/if}
                    <label for="{$prefix}ranges_{$el_id}_none" class="radio">
                    <input type="radio" name="ch_filters[{$el_id}]" id="{$prefix}ranges_{$el_id}_none" value="" {if !$search.ch_filters[$el_id]}checked="checked"{/if} />
                    {__("none")}</label>

                    <label for="{$prefix}ranges_{$el_id}_yes" class="radio">
                    <input type="radio" name="ch_filters[{$el_id}]" id="{$prefix}ranges_{$el_id}_yes" value="Y" {if $search.ch_filters[$el_id] == "Y"}checked="checked"{/if} />
                    {__("yes")}</label>

                    <label for="{$prefix}ranges_{$el_id}_no" class="radio">
                    <input type="radio" name="ch_filters[{$el_id}]" id="{$prefix}ranges_{$el_id}_no" value="N" {if $search.ch_filters[$el_id] == "N"}checked="checked"{/if} />
                    {__("no")}</label>

                {if !$filter.condition_type}
                    <label for="{$prefix}ranges_{$el_id}_any" class="radio">
                    <input type="radio" name="ch_filters[{$el_id}]" id="{$prefix}ranges_{$el_id}_any" value="A" {if $search.ch_filters[$el_id] == "A"}checked="checked"{/if} />
                    {__("any")}</label>
                {/if}

            {elseif $filter.feature_type == "T"}
                {$filter.prefix}<input type="text" name="tx_features[{$filter.feature_id}]" class="input-mini" value="{$search.tx_features[$filter.feature_id]}" />{$filter.suffix}
            {/if}
        </td>
    {/if}
{/foreach}
</tr>
{/foreach}
</table>