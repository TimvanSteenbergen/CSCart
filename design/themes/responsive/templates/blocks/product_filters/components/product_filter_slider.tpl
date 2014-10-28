{$placeholder = "nnn-nnn"}
{$min = $filter.range_values.min}
{$max = $filter.range_values.max}
{$left = $filter.range_values.left|default:$min}
{$right = $filter.range_values.right|default:$max}

{if $max-$min <= $filter.round_to}
    {$disable_slider = true}
{elseif $max-$min >= (4 * $filter.round_to)}
    {math equation="min + round((max - min) * 0.25 / rto) * rto" max=$max min=$min rto=$filter.round_to assign="num_25"}
    {math equation="min + round((max - min) * 0.50 / rto) * rto" max=$max min=$min rto=$filter.round_to assign="num_50"}
    {math equation="min + round((max - min) * 0.75 / rto) * rto" max=$max min=$min rto=$filter.round_to assign="num_75"}
{/if}

{if $filter.range_values.left|fn_string_not_empty || $filter.range_values.right|fn_string_not_empty}
    {capture name="has_selected"}Y{/capture}
{/if}

{if $dynamic}
    {assign var="filter_slider_hash" value=$smarty.request.features_hash|fn_add_range_to_url_hash:$placeholder:$filter.field_type}
    {assign var="filter_slider_url" value=$filter_qstring|fn_link_attach:"features_hash=`$filter_slider_hash`"|fn_url}
    {assign var="use_ajax" value=$filter_slider_url|fn_compare_dispatch:$config.current_url}
{else}
    {assign var="filter_slider_hash" value=""|fn_add_range_to_url_hash:$placeholder:$filter.field_type}
    {assign var="filter_slider_url" value="products.search?features_hash=`$filter_slider_hash`"|fn_url}
    {assign var="use_ajax" value=false}
{/if}

<div id="content_{$filter_uid}" class="ty-price-slider {if $collapse}hidden{/if} {$extra_class}">
    <input type="text" class="ty-price-slider__input-text" id="{$id}_left" name="left_{$id}" value="{$left}"{if $disable_slider} disabled="disabled"{/if} />
    &nbsp;–&nbsp;
    <input type="text" class="ty-price-slider__input-text" id="{$id}_right" name="right_{$id}" value="{$right}"{if $disable_slider} disabled="disabled"{/if} />
    {if $filter.field_type == 'P'}
        &nbsp;{$currencies.$secondary_currency.symbol nofilter}
    {/if}

    <div id="{$id}" class="ty-range-slider cm-range-slider">
        <ul class="ty-range-slider__wrapper">
            <li class="ty-range-slider__item" style="left: 0%;"><span class="ty-range-slider__num">{$min}</span></li>
            {if $num_25}
                <li class="ty-range-slider__item" style="left: 25%;"><span class="ty-range-slider__num">{$num_25}</span></li>
                <li class="ty-range-slider__item" style="left: 50%;"><span class="ty-range-slider__num">{$num_50}</span></li>
                <li class="ty-range-slider__item" style="left: 75%;"><span class="ty-range-slider__num">{$num_75}</span></li>
            {/if}
            <li class="ty-range-slider__item" style="left: 100%;"><span class="ty-range-slider__num">{$max}</span></li>
        </ul>
    </div>

    {if $right == $left}
        {math equation="left + rto" left=$left rto=$filter.round_to assign="_right"}
    {else}
        {assign var="_right" value=$right}
    {/if}
    {* Slider params *}
    <input type="hidden" id="{$id}_json" value='{ldelim}
        "disabled": {$disable_slider|default:"false"},
        "min": {$min},
        "max": {$max},
        "left": {$left},
        "right": {$_right},
        "step": {$filter.round_to},
        "url": "{$filter_slider_url}",
        "type": "{$filter.field_type}",
        "currency": "{$smarty.const.CART_SECONDARY_CURRENCY}",
        "ajax": {if $allow_ajax && $use_ajax}true{else}false{/if},
        "result_ids": "{$ajax_div_ids}",
        "scroll": ".cm-pagination-container"
    {rdelim}' />
    {* /Slider params *}
</div>
