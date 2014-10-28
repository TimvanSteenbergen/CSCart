{capture name="mainbox"}

    {capture name="tabsbox"}
        {** /Item menu section **}

        {assign var="categories_company_id" value=$product_data.company_id}
        {assign var="allow_save" value=$product_data|fn_allow_save_object:"product"}

        {if "ULTIMATE"|fn_allowed_for}
            {assign var="categories_company_id" value=""}
            {if $runtime.company_id && $product_data.shared_product == "Y" && $product_data.company_id != $runtime.company_id}
                {assign var="no_hide_input_if_shared_product" value="cm-no-hide-input"}
            {/if}

            {if !$runtime.company_id && $product_data.shared_product == "Y"}
                {assign var="show_update_for_all" value=true}
            {/if}
        {/if}

        {if $product_data.product_id}
            {assign var="id" value=$product_data.product_id}
        {else}
            {assign var="id" value=0}
        {/if}
        <form id='form' action="{""|fn_url}" method="post" name="product_update_form" class="form-horizontal form-edit  cm-disable-empty-files {if ""|fn_check_form_permissions || ($runtime.company_id && $product_data.shared_product == "Y" && $product_data.company_id != $runtime.company_id)} cm-hide-inputs{/if}" enctype="multipart/form-data"> {* product update form *}
            <input type="hidden" name="fake" value="1" />
            <input type="hidden" class="{$no_hide_input_if_shared_product}" name="selected_section" id="selected_section" value="{$smarty.request.selected_section}" />
            <input type="hidden" class="{$no_hide_input_if_shared_product}" name="product_id" value="{$id}" />

            {** Product description section **}

            <div class="product-manage" id="content_detailed"> {* content detailed *}

                {** General info section **}
                {include file="common/subheader.tpl" title=__("information") target="#acc_information"}

                <div id="acc_information" class="collapse in">

                    <div class="control-group {$no_hide_input_if_shared_product}">
                        <label for="product_description_product" class="control-label cm-required">{__("name")}</label>
                        <div class="controls">
                            <input class="input-large" form="form" type="text" name="product_data[product]" id="product_description_product" size="55" value="{$product_data.product}" />
                            {include file="buttons/update_for_all.tpl" display=$show_update_for_all object_id='product' name="update_all_vendors[product]"}
                        </div>
                    </div>

                    {assign var="result_ids" value="product_categories"}

                    {hook name="companies:product_details_fields"}
                    {if "MULTIVENDOR"|fn_allowed_for && $mode != "add"}
                        {assign var="reload_form" value=true}
                    {/if}
                    
                    {if "ULTIMATE"|fn_allowed_for}
                        {assign var="companies_tooltip" value=__("text_ult_product_store_field_tooltip")}
                    {/if}

                    {include file="views/companies/components/company_field.tpl"
                        name="product_data[company_id]"
                        id="product_data_company_id"
                        selected=$product_data.company_id
                        tooltip=$companies_tooltip
                        reload_form=$reload_form
                    }

                    {/hook}

                    <input type="hidden" value="{$result_ids}" name="result_ids">

                    <div class="control-group {$no_hide_input_if_shared_product}" id="product_categories">
                        {math equation="rand()" assign="rnd"}
                        {if $smarty.request.category_id}
                            {assign var="request_category_id" value=","|explode:$smarty.request.category_id}
                        {else}
                            {assign var="request_category_id" value=""}
                        {/if}
                        <label for="ccategories_{$rnd}_ids" class="control-label cm-required">{__("categories")}</label>
                        <div class="controls">
                            {include file="pickers/categories/picker.tpl" hide_input=$product_data.shared_product company_ids=$product_data.company_id rnd=$rnd data_id="categories" input_name="product_data[category_ids]" radio_input_name="product_data[main_category]" main_category=$product_data.main_category item_ids=$product_data.category_ids|default:$request_category_id hide_link=true hide_delete_button=true display_input_id="category_ids" disable_no_item_text=true view_mode="list" but_meta="btn"}
                        </div>
                    <!--product_categories--></div>

                    <div class="control-group {$no_hide_input_if_shared_product}">
                        <label for="elm_price_price" class="control-label cm-required">{__("price")} ({$currencies.$primary_currency.symbol nofilter}):</label>
                        <div class="controls">
                            <input type="text" name="product_data[price]" id="elm_price_price" size="10" value="{$product_data.price|default:"0.00"|fn_format_price:$primary_currency:null:false}" class="input-long" />
                            {include file="buttons/update_for_all.tpl" display=$show_update_for_all object_id='price' name="update_all_vendors[price]"}
                        </div>
                    </div>

                    <div class="control-group cm-no-hide-input">
                        <label class="control-label" for="elm_product_full_descr">{__("full_description")}:</label>
                        <div class="controls">
                            {include file="buttons/update_for_all.tpl" display=$show_update_for_all object_id='full_description' name="update_all_vendors[full_description]"}
                            <textarea id="elm_product_full_descr" name="product_data[full_description]" cols="55" rows="8" class="cm-wysiwyg input-large">{$product_data.full_description}</textarea>
                        </div>
                    </div>
                    {** /General info section **}

                    {include file="common/select_status.tpl" input_name="product_data[status]" id="elm_product_status" obj=$product_data hidden=true}

                    {hook name="products:update_detailed_images"}
                    <div class="control-group">
                        <label class="control-label">{__("images")}:</label>
                        <div class="controls">
                            {include file="common/attach_images.tpl" image_name="product_main" image_object_type="product" image_pair=$product_data.main_pair icon_text=__("text_product_thumbnail") detailed_text=__("text_product_detailed_image") no_thumbnail=true}
                        </div>
                    </div>
                    {/hook}

                </div>

                <hr>

                {include file="common/subheader.tpl" title=__("options_settings") target="#acc_options"}
                {if "ULTIMATE:FREE"|fn_allowed_for}
                    {$promo_class = "cm-promo-popup"}
                    {$disable_selectors = true}
                {/if}

                <div id="acc_options" class="collapse in">
                    <div class="control-group {$promo_class}">
                        <label class="control-label" for="elm_options_type">{__("options_type")}:</label>
                        <div class="controls">
                            <select class="span3" name="product_data[options_type]" id="elm_options_type" {if $disable_selectors}disabled="disabled"{/if}>
                                <option value="P" {if $product_data.options_type == "P"}selected="selected"{/if}>{__("simultaneous")}</option>
                                <option value="S" {if $product_data.options_type == "S"}selected="selected"{/if}>{__("sequential")}</option>
                            </select>
                        </div>
                    </div>
                    <div class="control-group {$promo_class}">
                        <label class="control-label" for="elm_exceptions_type">{__("exceptions_type")}:</label>
                        <div class="controls">
                            <select class="span3" name="product_data[exceptions_type]" id="elm_exceptions_type" {if $disable_selectors}disabled="disabled"{/if}>
                                <option value="F" {if $product_data.exceptions_type == "F"}selected="selected"{/if}>{__("forbidden")}</option>
                                <option value="A" {if $product_data.exceptions_type == "A"}selected="selected"{/if}>{__("allowed")}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <hr>

                {include file="common/subheader.tpl" title=__("pricing_inventory") target="#acc_pricing_inventory"}
                <div id="acc_pricing_inventory" class="collapse in">
                    <div class="control-group">
                        <label class="control-label" for="elm_product_code">{__("sku")}:</label>
                        <div class="controls">
                            <input type="text" name="product_data[product_code]" id="elm_product_code" size="20" maxlength="32"  value="{$product_data.product_code}" class="input-long" />
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="elm_list_price">{__("list_price")} ({$currencies.$primary_currency.symbol nofilter}) :</label>
                        <div class="controls">
                            <input type="text" name="product_data[list_price]" id="elm_list_price" size="10" value="{$product_data.list_price|default:"0.00"}" class="input-long" />
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="elm_in_stock">{__("in_stock")}:</label>
                        <div class="controls">
                            {if $product_data.tracking == "ProductTracking::TRACK_WITH_OPTIONS"|enum}
                                {include file="buttons/button.tpl" but_text=__("edit") but_href="product_options.inventory?product_id=`$id`" but_role="edit"}
                            {else}
                                <input type="text" name="product_data[amount]" id="elm_in_stock" size="10" value="{$product_data.amount|default:"1"}" class="input-small" />
                            {/if}
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="elm_zero_price_action">{__("zero_price_action")}:</label>
                        <div class="controls">
                            <select class="span5" name="product_data[zero_price_action]" id="elm_zero_price_action">
                                <option value="R" {if $product_data.zero_price_action == "R"}selected="selected"{/if}>{__("zpa_refuse")}</option>
                                <option value="P" {if $product_data.zero_price_action == "P"}selected="selected"{/if}>{__("zpa_permit")}</option>
                                <option value="A" {if $product_data.zero_price_action == "A"}selected="selected"{/if}>{__("zpa_ask_price")}</option>
                            </select>
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="elm_product_tracking">{__("inventory")}:</label>
                        <div class="controls">
                            <select class="span3" name="product_data[tracking]" id="elm_product_tracking" {if $settings.General.inventory_tracking == "N"}disabled="disabled"{/if}>
                                {if $product_options}
                                    <option value="{"ProductTracking::TRACK_WITH_OPTIONS"|enum}" {if $product_data.tracking == "ProductTracking::TRACK_WITH_OPTIONS"|enum && $settings.General.inventory_tracking == "Y"}selected="selected"{/if}>{__("track_with_options")}</option>
                                {/if}
                                <option value="{"ProductTracking::TRACK_WITHOUT_OPTIONS"|enum}" {if $product_data.tracking == "{"ProductTracking::TRACK_WITHOUT_OPTIONS"|enum}" && $settings.General.inventory_tracking == "Y"}selected="selected"{/if}>{__("track_without_options")}</option>
                                <option value="{"ProductTracking::DO_NOT_TRACK"|enum}" {if $product_data.tracking == "{"ProductTracking::DO_NOT_TRACK"|enum}" || $settings.General.inventory_tracking == "N"}selected="selected"{/if}>{__("dont_track")}</option>
                            </select>
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="elm_min_qty">{__("min_order_qty")}:</label>
                        <div class="controls">
                            <input type="text" name="product_data[min_qty]" size="10" id="elm_min_qty" value="{$product_data.min_qty|default:"0"}" class="input-small" />
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="elm_max_qty">{__("max_order_qty")}:</label>
                        <div class="controls">
                            <input type="text" name="product_data[max_qty]" id="elm_max_qty" size="10" value="{$product_data.max_qty|default:"0"}" class="input-small" />
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="elm_qty_step">{__("quantity_step")}:</label>
                        <div class="controls">
                            <input type="text" name="product_data[qty_step]" id="elm_qty_step" size="10" value="{$product_data.qty_step|default:"0"}" class="input-small" />
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="elm_list_qty_count">{__("list_quantity_count")}:</label>
                        <div class="controls">
                            <input type="text" name="product_data[list_qty_count]" id="elm_list_qty_count" size="10" value="{$product_data.list_qty_count|default:"0"}" class="input-small" />
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label">{__("taxes")}:</label>
                        <div class="controls">
                            <input type="hidden" name="product_data[tax_ids]" value="" />
                            {foreach from=$taxes item="tax"}
                                <label class="checkbox inline" for="elm_taxes_{$tax.tax_id}">
                                    <input type="checkbox" name="product_data[tax_ids][{$tax.tax_id}]" id="elm_taxes_{$tax.tax_id}" {if $tax.tax_id|in_array:$product_data.tax_ids}checked="checked"{/if} value="{$tax.tax_id}" />
                                    {$tax.tax}</label>
                                {foreachelse}
                                &ndash;
                            {/foreach}
                        </div>
                    </div>
                </div>

                <hr>
                {include file="common/subheader.tpl" title=__("availability") target="#acc_availability"}
                <div id="acc_availability" class="collapse in">
                    {if !"ULTIMATE:FREE"|fn_allowed_for}
                        <div class="control-group">
                            <label class="control-label">{__("usergroups")}:</label>
                            <div class="controls">
                                {include file="common/select_usergroups.tpl" id="ug_id" name="product_data[usergroup_ids]" usergroups="C"|fn_get_usergroups:$smarty.const.DESCR_SL usergroup_ids=$product_data.usergroup_ids input_extra="" list_mode=false}
                            </div>
                        </div>
                    {/if}

                    <div class="control-group">
                        <label class="control-label" for="elm_date_holder">{__("creation_date")}:</label>
                        <div class="controls">
                            {include file="common/calendar.tpl" date_id="elm_date_holder" date_name="product_data[timestamp]" date_val=$product_data.timestamp|default:$smarty.const.TIME start_year=$settings.Company.company_start_year}
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="elm_date_avail_holder">{__("available_since")}:</label>
                        <div class="controls">
                            {include file="common/calendar.tpl" date_id="elm_date_avail_holder" date_name="product_data[avail_since]" date_val=$product_data.avail_since|default:"" start_year=$settings.Company.company_start_year}
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="elm_out_of_stock_actions">{__("out_of_stock_actions")}:</label>
                        <div class="controls">
                            <select class="span3" name="product_data[out_of_stock_actions]" id="elm_out_of_stock_actions">
                                <option value="N" {if $product_data.out_of_stock_actions == "N"}selected="selected"{/if}>{__("none")}</option>
                                <option value="B" {if $product_data.out_of_stock_actions == "B"}selected="selected"{/if}>{__("buy_in_advance")}</option>
                                <option value="S" {if $product_data.out_of_stock_actions == "S"}selected="selected"{/if}>{__("sign_up_for_notification")}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <hr>

                {include file="common/subheader.tpl" title=__("extra") target="#acc_extra"}
                <div id="acc_extra" class="collapse in">
                    <div class="control-group">
                        <label class="control-label" for="elm_details_layout">{__("product_details_view")}:</label>
                        <div class="controls">
                            <select class="span5" id="elm_details_layout" name="product_data[details_layout]">
                                {foreach from=$id|fn_get_product_details_views key="layout" item="item"}
                                    <option {if $product_data.details_layout == $layout}selected="selected"{/if} value="{$layout}">{$item}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="elm_product_feature_comparison">{__("feature_comparison")}:</label>
                        <div class="controls">
                            <label class="checkbox">
                                <input type="hidden" name="product_data[feature_comparison]" value="N" />
                                <input type="checkbox" name="product_data[feature_comparison]" id="elm_product_feature_comparison" value="Y" {if $product_data.feature_comparison == "Y"}checked="checked"{/if}/>
                            </label>
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="elm_product_is_edp">{__("downloadable")}:</label>
                        <div class="controls">
                            <label class="checkbox">
                                <input type="hidden" name="product_data[is_edp]" value="N" />
                                <input type="checkbox" name="product_data[is_edp]" id="elm_product_is_edp" value="Y" {if $product_data.is_edp == "Y"}checked="checked"{/if} onclick="Tygh.$('#edp_shipping').toggleBy(); Tygh.$('#edp_unlimited').toggleBy();"/>
                            </label>
                        </div>
                    </div>

                    <div class="control-group {if $product_data.is_edp != "Y"}hidden{/if}" id="edp_shipping">
                        <label class="control-label" for="elm_product_edp_shipping">{__("edp_enable_shipping")}:</label>
                        <div class="controls">
                            <label class="checkbox">
                                <input type="hidden" name="product_data[edp_shipping]" value="N" />
                                <input type="checkbox" name="product_data[edp_shipping]" id="elm_product_edp_shipping" value="Y"{if $product_data.edp_shipping == "Y"}checked="checked"{/if} />
                            </label>
                        </div>
                    </div>

                    <div class="control-group {if $product_data.is_edp != "Y"}hidden{/if}" id="edp_unlimited">
                        <label class="control-label" for="elm_product_edp_unlimited">{__("time_unlimited_download")}:</label>
                        <div class="controls">
                            <label class="checkbox">
                                <input type="hidden" name="product_data[unlimited_download]" value="N" />
                                <input type="checkbox" name="product_data[unlimited_download]" id="elm_product_edp_unlimited" value="Y" {if $product_data.unlimited_download == "Y"}checked="checked"{/if} />
                            </label>
                        </div>
                    </div>

                    {include file="views/localizations/components/select.tpl" data_from=$product_data.localization data_name="product_data[localization]"}

                    <div class="control-group {$no_hide_input_if_shared_product}">
                        <label class="control-label" for="elm_product_short_descr">{__("short_description")}:</label>
                        <div class="controls">
                            <textarea id="elm_product_short_descr" name="product_data[short_description]" cols="55" rows="2" class="cm-wysiwyg input-large">{$product_data.short_description}</textarea>
                            {include file="buttons/update_for_all.tpl" display=$show_update_for_all object_id="short_description" name="update_all_vendors[short_description]"}
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="elm_product_popularity">{__("popularity")}:</label>
                        <div class="controls">
                            <input type="text" name="product_data[popularity]" id="elm_product_popularity" size="55" value="{$product_data.popularity|default:0}" class="input-long" />
                        </div>
                    </div>

                    <div class="control-group {$no_hide_input_if_shared_product}">
                        <label class="control-label" for="elm_product_search_words">{__("search_words")}:</label>
                        <div class="controls">
                            <textarea name="product_data[search_words]" id="elm_product_search_words" cols="55" rows="2" class="input-large">{$product_data.search_words}</textarea>
                            {include file="buttons/update_for_all.tpl" display=$show_update_for_all object_id='search_words' name="update_all_vendors[search_words]"}
                        </div>
                    </div>

                    <div class="control-group {$no_hide_input_if_shared_product}">
                        <label class="control-label" for="elm_product_promo_text">{__("promo_text")}:</label>
                        <div class="controls">
                            <textarea id="elm_product_promo_text" name="product_data[promo_text]" cols="55" rows="2" class="cm-wysiwyg input-large">{$product_data.promo_text}</textarea>
                            {include file="buttons/update_for_all.tpl" display=$show_update_for_all object_id="promo_text" name="update_all_vendors[promo_text]"}
                        </div>
                    </div>
                </div>
                <!--content_detailed--></div> {* /content detailed *}

            {** /Product description section **}

            {** Product images section **}
            <div id="content_images" class="hidden clearfix"> {* content images *}
                {include file="common/subheader.tpl" title=__("additional_images")}
                {if $product_data.image_pairs}
                    <div class="cm-sortable sortable-box" data-ca-sortable-table="images_links" data-ca-sortable-id-name="pair_id" id="additional_images">
                        {assign var="new_image_position" value="0"}
                        {foreach from=$product_data.image_pairs item=pair name="detailed_images"}
                            <div class="cm-row-item cm-sortable-id-{$pair.pair_id} cm-sortable-box">
                                <div class="cm-sortable-handle sortable-bar"><img src="{$images_dir}/icon_sort_bar.gif" width="26" height="25" border="0" title="{__("sort_images")}" alt="{__("sort")}" class="valign" /></div>
                                <div class="sortable-item">
                                    {include file="common/attach_images.tpl" image_name="product_additional" image_object_type="product" image_key=$pair.pair_id image_type="A" image_pair=$pair icon_title=__("additional_thumbnail") detailed_title=__("additional_popup_larger_image") icon_text=__("text_additional_thumbnail") detailed_text=__("text_additional_detailed_image") delete_pair=true no_thumbnail=true}
                                </div>
                                <div class="clear"></div>
                            </div>
                            {if $new_image_position <= $pair.position}
                                {assign var="new_image_position" value=$pair.position}
                            {/if}
                        {/foreach}
                    </div>
                {/if}

                <div id="box_new_image">
                    <div class="clear cm-row-item">
                        <input type="hidden" name="product_add_additional_image_data[0][position]" value="{$new_image_position}" class="cm-image-field" />
                        <div class="image-upload-wrap pull-left">{include file="common/attach_images.tpl" image_name="product_add_additional" image_object_type="product" image_type="A" icon_title=__("additional_thumbnail") detailed_title=__("additional_popup_larger_image") icon_text=__("text_additional_thumbnail") detailed_text=__("text_additional_detailed_image") no_thumbnail=true}</div>
                        <div class="pull-right">{include file="buttons/multiple_buttons.tpl" item_id="new_image"}</div>
                    </div>
                </div>

            </div> {* /content images *}
            {** /Product images section **}

            {** SEO settings section **}
            <div id="content_seo" class="hidden">

                {hook name="products:update_seo"}
                {include file="common/subheader.tpl" title=__("seo_meta_data") target="#acc_seo_meta"}
                <div id="acc_seo_meta" class="collapse in">
                    <div class="control-group {$no_hide_input_if_shared_product}">
                        <label class="control-label" for="elm_product_page_title">{__("page_title")}:</label>
                        <div class="controls">
                            <input type="text" name="product_data[page_title]" id="elm_product_page_title" size="55" value="{$product_data.page_title}" class="input-large" />
                            {include file="buttons/update_for_all.tpl" display=$show_update_for_all object_id="page_title" name="update_all_vendors[page_title]"}
                        </div>
                    </div>

                    <div class="control-group {$no_hide_input_if_shared_product}">
                        <label class="control-label" for="elm_product_meta_descr">{__("meta_description")}:</label>
                        <div class="controls">
                            <textarea name="product_data[meta_description]" id="elm_product_meta_descr" cols="55" rows="2" class="input-large">{$product_data.meta_description}</textarea>
                            {include file="buttons/update_for_all.tpl" display=$show_update_for_all object_id="meta_description" name="update_all_vendors[meta_description]"}
                        </div>
                    </div>

                    <div class="control-group {$no_hide_input_if_shared_product}">
                        <label class="control-label" for="elm_product_meta_keywords">{__("meta_keywords")}:</label>
                        <div class="controls">
                            <textarea name="product_data[meta_keywords]" id="elm_product_meta_keywords" cols="55" rows="2" class="input-large">{$product_data.meta_keywords}</textarea>
                            {include file="buttons/update_for_all.tpl" display=$show_update_for_all object_id="meta_keywords" name="update_all_vendors[meta_keywords]" }
                        </div>
                    </div>
                </div>
                {/hook}
            </div>
            {** /SEO settings section **}

            {** Shipping settings section **}
            <div id="content_shippings" class="hidden"> {* content shippings *}
                {include file="views/products/components/products_shipping_settings.tpl"}
            </div> {* /content shippings *}
            {** /Shipping settings section **}

            {** Quantity discounts section **}
            {hook name="products:update_qty_discounts"}
            {include file="views/products/components/products_update_qty_discounts.tpl"}
            {/hook}
            {** /Quantity discounts section **}
            {** Product features section **}
            {include file="views/products/components/products_update_features.tpl"}
            {** /Product features section **}


            <div id="content_addons">
                {hook name="products:detailed_content"}
                {/hook}
            </div>


            {hook name="products:tabs_content"}
            {/hook}

            {** Form submit section **}
            {capture name="buttons"}
                {include file="common/view_tools.tpl" url="products.update?product_id="}

                {if $id}
                    {$view_uri = "products.view?product_id=`$id`"|fn_get_preview_url:$product_data:$auth.user_id}

                    {capture name="tools_list"}
                        {hook name="products:update_tools_list"}
                            <li>{btn type="list" target="_blank" text=__("preview") href=$view_uri}</li>
                            <li class="divider"></li>
                            <li>{btn type="list" text=__("clone") href="products.clone?product_id=`$id`"}</li>
                            {if $allow_save}
                                <li>{btn type="list" text=__("delete") class="cm-confirm" href="products.delete?product_id=`$id`"}</li>
                            {/if}
                        {/hook}
                    {/capture}
                    {dropdown content=$smarty.capture.tools_list}
                {/if}
                {include file="buttons/save_cancel.tpl" but_role="submit-link" but_name="dispatch[products.update]" but_target_form="product_update_form" save=$id}
            {/capture}
            {** /Form submit section **}

        </form> {* /product update form *}

        {hook name="products:tabs_extra"}{/hook}

        {if $id}
            {** Product options section **}
            <div class="cm-hide-save-button hidden" id="content_options">
                {include file="views/products/components/products_update_options.tpl"}
            </div>
            {** /Product options section **}

            {** Products files section **}
            <div id="content_files" class="cm-hide-save-button hidden">
                {hook name="products:content_files"}
                {include file="views/products/components/products_update_files.tpl"}
                {/hook}
            </div>
            {** /Products files section **}

            {** Subscribers section **}
            <div id="content_subscribers" class="cm-hide-save-button hidden">
                {include file="views/products/components/product_subscribers.tpl" product_id=$id}
            </div>
            {** /Subscribers section **}
        {/if}

    {/capture}
    {include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox group_name=$runtime.controller active_tab=$smarty.request.selected_section track=true}

{/capture}

{hook name="products:update_mainbox_params"}

{if $id}
    {capture name="mainbox_title"}
        {"{__("editing_product")}: `$product_data.product`"|strip_tags}
    {/capture}
{else}
    {capture name="mainbox_title"}
        {__("new_product")}
    {/capture}
{/if}

{/hook}

{include file="common/mainbox.tpl"
    title=$smarty.capture.mainbox_title
    content=$smarty.capture.mainbox
    select_languages=$id
    buttons=$smarty.capture.buttons
    adv_buttons=$smarty.capture.adv_buttons
}