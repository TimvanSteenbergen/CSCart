{function name="feature_value"}
    {strip}
        {if $feature.features_hash && $feature.feature_type == "E"}
            <a href="{"categories.view?category_id=`$product.main_category`&features_hash=`$feature.features_hash`"|fn_url}">
        {/if}
        {if $feature.prefix}{$feature.prefix}{/if}
        {if $feature.feature_type == "D"}
            {$feature.value_int|date_format:"`$settings.Appearance.date_format`"}
        {elseif $feature.feature_type == "M"}
            {foreach from=$feature.variants item="fvariant" name="ffev"}
                {$fvariant.variant|default:$fvariant.value}{if !$smarty.foreach.ffev.last}, {/if}
            {/foreach}
        {elseif $feature.feature_type == "S" || $feature.feature_type == "N" || $feature.feature_type == "E"}
            {$feature.variant|default:$feature.value}
        {elseif $feature.feature_type == "C"}
            {$feature.description}
        {elseif $feature.feature_type == "O"}
            {$feature.value_int|floatval}
        {else}
            {$feature.value}
        {/if}
        {if $feature.suffix}{$feature.suffix}{/if}
        {if $feature.feature_type == "E" && $feature.features_hash}
            </a>
        {/if}
    {/strip}
{/function}

{if $features}
    {strip}
        {if !$no_container}<p class="features-list description">{/if}
            {foreach from=$features name=features_list item=feature}
                {if $feature.feature_type == "D" || $feature.feature_type == "O" || $feature.feature_type == "N"}
                    {$feature.description nofilter}: 
                {/if}
                {feature_value feature=$feature}{if !$smarty.foreach.features_list.last}, {/if}
            {/foreach}
        {if !$no_container}</p>{/if}
    {/strip}
{/if}