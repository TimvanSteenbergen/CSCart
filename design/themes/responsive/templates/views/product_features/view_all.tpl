{if $view_all_filter}
{assign var="filter_qstring" value=$smarty.request.q|fn_url}
{assign var="filter_qstring" value=$filter_qstring|fn_query_remove:"result_ids":"filter_id":"features_hash"}
{split data=$view_all_filter size="4" assign="splitted_filter" preverse_keys=true}
<div class="ty-features-all">
{foreach from=$splitted_filter item="group"}
    {foreach from=$group item="ranges" key="index"}
    {strip}
    <div class="ty-features-all__group ty-column6">
            {if $ranges}
                {include file="common/subheader.tpl" title=$index}
                <ul class="ty-features-all__list">
                {foreach from=$ranges item="range"}
                    {assign var="_features_hash" value=$params.features_hash|fn_add_range_to_url_hash:$range}
                    {$href = $filter_qstring|fn_link_attach:"features_hash=`$_features_hash`"}
                    <li class="ty-features-all__list-item"><a href="{if $range.feature_type == "E"}{"product_features.view?variant_id=`$range.range_id``$cur_features_hash`"|fn_url}{else}{$href|fn_url}{/if}" class="ty-features-all__list-a">{$range.range_name|fn_text_placeholders}</a></li>
                {/foreach}
            </ul>
            {else}&nbsp;{/if}
    </div>
    {strip}
    {/foreach}
{/foreach}
</div>
{/if}