                <ul class="product-filters {if $collapse}hidden{/if}" id="content_{$filter_uid}">

                    {* Selected variants *}
                    {foreach from=$filter.selected_ranges name="selected_ranges" item="selected_range"}
                        {capture name="has_selected"}Y{/capture}
                        <li>
                            {strip}
                                {assign var="fh" value=$smarty.request.features_hash|fn_delete_range_from_url:$selected_range:$filter.field_type}
                                {if $fh}
                                    {assign var="attach_query" value="features_hash=`$fh`"}
                                {/if}
                                {if $filter.feature_type == "E" && $selected_range.range_id == $smarty.request.variant_id}
                                    {assign var="reset_lnk" value=$reset_qstring}
                                {else}
                                    {assign var="reset_lnk" value=$filter_qstring}
                                {/if}
                                {if $fh}
                                    {assign var="href" value=$reset_lnk|fn_link_attach:$attach_query|fn_url}
                                {else}
                                    {assign var="href" value=$reset_lnk|fn_url}
                                {/if}
                                {assign var="use_ajax" value=$href|fn_compare_dispatch:$config.current_url}
                                <a href="{$href}" class="filter-item checked{if $allow_ajax && $use_ajax} cm-ajax cm-ajax-full-render cm-history"{/if} data-ca-scroll=".cm-pagination-container" data-ca-target-id="{$ajax_div_ids}" rel="nofollow"><span class="filter-icon"><i class="icon-ok"></i><i class="icon-cancel"></i></span>{$filter.prefix}{$selected_range.range_name|fn_text_placeholders}{$filter.suffix}</a>
                            {/strip}
                        </li>
                    {/foreach}

                    {* Variants before the more link *}
                    {if $filter.ranges|fn_is_not_empty}
                        <ul id="ranges_{$filter_uid}">

                            {foreach from=$filter.ranges item="range"}
                                {include file="blocks/product_filters/components/variant_item.tpl" range=$range filter=$filter ajax_div_ids=$ajax_div_ids filter_qstring=$filter_qstring reset_qstring=$reset_qstring allow_ajax=$allow_ajax}
                            {/foreach}

                        </ul>
                    {/if}


                    {* View all link *}
                    {capture name="view_all"}
                        {if $filter.more_cut}
                            {capture name="q"}{$filter_qstring nofilter}&filter_id={$filter.filter_id}&{if $smarty.request.features_hash}&features_hash={$smarty.request.features_hash}{/if}{/capture}
                            <li id="view_all_{$filter_uid}">
                                {assign var="capture_q" value=$smarty.capture.q|escape:url}
                                <a href="{"product_features.view_all?q=`$capture_q`"|fn_url}" rel="nofollow" class="extra-link">{__("view_all")}</a>
                            </li>
                        {/if}
                    {/capture}

                    {* Variants under the more link *}
                    {if $filter.more_ranges|fn_is_not_empty}
                        {assign var="cookie_name_show_more" value="more_ranges_`$filter_uid`"}
                        {assign var="more_collapse" value=true}
                        {if $smarty.cookies.$cookie_name_show_more}
                            {assign var="more_collapse" value=false}
                        {/if}

                        <ul id="more_ranges_{$filter_uid}" {if $more_collapse}class="hidden"{/if}>

                            {foreach from=$filter.more_ranges item="range"}
                                {include file="blocks/product_filters/components/variant_item.tpl" range=$range filter=$filter ajax_div_ids=$ajax_div_ids filter_qstring=$filter_qstring reset_qstring=$reset_qstring allow_ajax=$allow_ajax}
                            {/foreach}

                            {$smarty.capture.view_all nofilter}

                        </ul>

                        <li class="extra-link-wrap">
                            <a id="on_more_ranges_{$filter_uid}" class="extra-link cm-save-state cm-combination-more_{$filter_uid}{if !$more_collapse} hidden{/if}">{__("more")}</a>
                            <a id="off_more_ranges_{$filter_uid}" class="extra-link cm-save-state cm-combination-more_{$filter_uid}{if $more_collapse} hidden{/if}">{__("less")}</a>
                        </li>
                    {else}
                        {$smarty.capture.view_all nofilter}
                    {/if}
                </ul>