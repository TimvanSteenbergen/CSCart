                                <li>
                                    {if !$range.checked}
                                        {assign var="filter_query_elm" value=$smarty.request.features_hash|fn_add_range_to_url_hash:$range:$filter.field_type}
                                    {else}
                                        {assign var="filter_query_elm" value=$smarty.request.features_hash|fn_delete_range_from_url:$range:$filter.field_type}
                                    {/if}
                                    {if $smarty.request.features_hash}
                                        {assign var="cur_features_hash" value="&features_hash=`$smarty.request.features_hash`"}
                                    {/if}
                                    {if $filter.feature_type == "E" && (!$filter.simple_link || $filter.selected_ranges && $controller == "product_features")}
                                        {assign var="href" value="product_features.view?variant_id=`$range.range_id``$cur_features_hash`"|fn_url}
                                    {else}
                                        {assign var="href" value=$filter_qstring|fn_link_attach:"features_hash=`$filter_query_elm`"|fn_url}
                                    {/if}
                                    {assign var="use_ajax" value=$href|fn_compare_dispatch:$config.current_url}
                                    <a {if !$range.disabled || $range.checked}href="{$href}"{/if} {if $filter.feature_type != "E"}rel="nofollow"{/if} class="filter-item{if $range.checked} checked{/if}{if $range.disabled} disabled{elseif $allow_ajax && $use_ajax} cm-ajax cm-ajax-full-render cm-history{/if}" data-ca-scroll=".cm-pagination-container" data-ca-target-id="{$ajax_div_ids}"><span class="filter-icon"><i class="icon-ok"></i><i class="icon-cancel"></i></span>{$filter.prefix}{$range.range_name|fn_text_placeholders}{$filter.suffix}&nbsp;{if !$range.disabled}<span class="details">&nbsp;({$range.products})</span>{/if}</a>
                                </li>
