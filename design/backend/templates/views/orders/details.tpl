{if $order_info.shipping}
    {foreach from=$order_info.shipping item="shipping" key="shipping_id" name="f_shipp"}
        {if $use_shipments}
            {capture name="add_new_picker"}
                {include file="views/shipments/components/new_shipment.tpl" group_key=$shipping.group_key}
            {/capture}
            {include file="common/popupbox.tpl" id="add_shipment_`$shipping.group_key`" content=$smarty.capture.add_new_picker text=__("new_shipment") act="hidden"}
        {/if}
    {/foreach}
{else}
    {foreach from=$order_info.product_groups item="group" key="group_id"}
        {if $group.all_free_shipping}
            {if $use_shipments}
                {capture name="add_new_picker"}
                    {include file="views/shipments/components/new_shipment.tpl" group_key=0}
                {/capture}
                {include file="common/popupbox.tpl" id="add_shipment_0" content=$smarty.capture.add_new_picker text=__("new_shipment") act="hidden"}
            {/if}
        {/if}
    {/foreach}
{/if}
<form action="{""|fn_url}" method="post" name="order_info_form" class="form-horizontal form-edit form-table">
<input type="hidden" name="order_id" value="{$smarty.request.order_id}" />
<input type="hidden" name="order_status" value="{$order_info.status}" />
<input type="hidden" name="result_ids" value="content_general" />
<input type="hidden" name="selected_section" value="{$smarty.request.selected_section}" />

{capture name="mainbox"}
{capture name="tabsbox"}
<div id="content_general">
    <div class="row-fluid">
        <div class="span8">
            {* Products info *}
            <table width="100%" class="table table-middle">
            <thead>
                <tr>
                    <th width="50%">{__("product")}</th>
                    <th width="10%">{__("price")}</th>
                    <th class="center" width="10%">{__("quantity")}</th>
                    {if $order_info.use_discount}
                    <th width="5%">{__("discount")}</th>
                    {/if}
                    {if $order_info.taxes && $settings.General.tax_calculation != "subtotal"}
                    <th width="10%">&nbsp;{__("tax")}</th>
                    {/if}
                    <th width="10%" class="right">&nbsp;{__("subtotal")}</th>
                </tr>
            </thead>
            {foreach from=$order_info.products item="oi" key="key"}
            {hook name="orders:items_list_row"}
            {if !$oi.extra.parent}
            <tr>
                <td>
                    {if !$oi.deleted_product}<a href="{"products.update?product_id=`$oi.product_id`"|fn_url}">{/if}{$oi.product nofilter}{if !$oi.deleted_product}</a>{/if}
                    <div class="products-hint">
                    {hook name="orders:product_info"}
                        {if $oi.product_code}<p>{__("sku")}:{$oi.product_code}</p>{/if}
                    {/hook}
                    </div>
                    {if $oi.product_options}<div class="options-info">{include file="common/options_info.tpl" product_options=$oi.product_options}</div>{/if}
                </td>
                <td class="nowrap">
                    {if $oi.extra.exclude_from_calculate}{__("free")}{else}{include file="common/price.tpl" value=$oi.original_price}{/if}</td>
                <td class="center">
                    &nbsp;{$oi.amount}<br />
                    {if !"ULTIMATE:FREE"|fn_allowed_for && $use_shipments && $oi.shipped_amount > 0}
                        &nbsp;<span class="muted"><small>({$oi.shipped_amount}&nbsp;{__("shipped")})</small></span>
                    {/if}
                </td>
                {if $order_info.use_discount}
                <td class="nowrap">
                    {if $oi.extra.discount|floatval}{include file="common/price.tpl" value=$oi.extra.discount}{else}-{/if}</td>
                {/if}
                {if $order_info.taxes && $settings.General.tax_calculation != "subtotal"}
                <td class="nowrap">
                    {if $oi.tax_value|floatval}{include file="common/price.tpl" value=$oi.tax_value}{else}-{/if}</td>
                {/if}
                <td class="right">&nbsp;<span>{if $oi.extra.exclude_from_calculate}{__("free")}{else}{include file="common/price.tpl" value=$oi.display_subtotal}{/if}</span></td>
            </tr>
            {/if}
            {/hook}
            {/foreach}
            {hook name="orders:extra_list"}
            {/hook}
            </table>

            <!--{***** Customer note, Staff note & Statistics *****}-->
            {hook name="orders:totals"}
            <div class="order-notes statistic">

            <div class="clearfix">
            <table class="pull-right">
                <tr class="totals">
                    <td>&nbsp;</td>
                    <td width="100px"><h4>{__("totals")}</h4></td>
                </tr>

                <tr>
                    <td>{__("subtotal")}:</td>
                    <td data-ct-totals="subtotal">{include file="common/price.tpl" value=$order_info.display_subtotal}</td>
                </tr>

                {if $order_info.display_shipping_cost|floatval}
                    <tr>
                        <td>{__("shipping_cost")}:</td>
                        <td data-ct-totals="shipping_cost">{include file="common/price.tpl" value=$order_info.display_shipping_cost}</td>
                    </tr>
                {/if}

                {if $order_info.discount|floatval}
                    <tr>
                        <td>{__("including_discount")}:</td>
                        <td data-ct-totals="including_discount">{include file="common/price.tpl" value=$order_info.discount}</td>
                    </tr>
                {/if}

                {if $order_info.subtotal_discount|floatval}
                    <tr>
                        <td>{__("order_discount")}:</td>
                        <td data-ct-totals="order_discount">{include file="common/price.tpl" value=$order_info.subtotal_discount}</td>
                    </tr>
                {/if}

                {if $order_info.coupons}
                    {foreach from=$order_info.coupons key="coupon" item="_c"}
                        <tr>
                            <td>{__("discount_coupon")}:</td>
                            <td data-ct-totals="discount_coupon">{$coupon}</td>
                        </tr>
                    {/foreach}
                {/if}

                {if $order_info.taxes}
                    <tr>
                        <td>{__("taxes")}:</td>
                        <td>&nbsp;</td>
                    </tr>

                    {foreach from=$order_info.taxes item="tax_data"}
                        <tr>
                            <td>&nbsp;<span>&middot;</span>&nbsp;{$tax_data.description}&nbsp;{include file="common/modifier.tpl" mod_value=$tax_data.rate_value mod_type=$tax_data.rate_type}{if $tax_data.price_includes_tax == "Y" && ($settings.Appearance.cart_prices_w_taxes != "Y" || $settings.General.tax_calculation == "subtotal")}&nbsp;{__("included")}{/if}{if $tax_data.regnumber}&nbsp;({$tax_data.regnumber}){/if}</td>
                            <td data-ct-totals="taxes-{$tax_data.description}">{include file="common/price.tpl" value=$tax_data.tax_subtotal}</td>
                        </tr>
                    {/foreach}
                {/if}

                {if $order_info.tax_exempt == "Y"}
                    <tr>
                        <td>{__("tax_exempt")}</td>
                        <td>&nbsp;</td>
                    </tr>
                {/if}

                {if $order_info.payment_surcharge|floatval && !$take_surcharge_from_vendor}
                    <tr>
                        <td>{$order_info.payment_method.surcharge_title|default:__("payment_surcharge")}:</td>
                        <td data-ct-totals="payment_surcharge">{include file="common/price.tpl" value=$order_info.payment_surcharge}</td>
                    </tr>
                {/if}

                {hook name="orders:totals_content"}
                {/hook}
                <tr>
                    <td><h4>{__("total")}:</h4></td>
                    <td class="price" data-ct-totals="total">{include file="common/price.tpl" value=$order_info.total}</td>
                </tr>
            </table>
            </div>

            <div class="note clearfix">
                <div class="span6">
                    <label for="details">{__("customer_notes")}</label>
                    <textarea class="span12" name="update_order[notes]" id="notes" cols="40" rows="5">{$order_info.notes}</textarea>
                </div>
                <div class="span6">
                    <label for="details">{__("staff_only_notes")}</label>
                    <textarea class="span12" name="update_order[details]" id="details" cols="40" rows="5">{$order_info.details}</textarea>
                </div>
            </div>

            </div>
            {/hook}

            <!--{***** /Customer note, Staff note & Statistics *****}-->
    
    {hook name="orders:staff_only_note"}
    {/hook}

        </div>
        <div class="span4">
            <div class="well orders-right-pane form-horizontal">
                <div class="control-group">
                    <div class="control-label"><h4 class="subheader">{__("status")}</h4></div>
                    <div class="controls">
                        {hook name="orders:order_status"}
                            {if $order_info.status == $smarty.const.STATUS_INCOMPLETED_ORDER}
                                {assign var="get_additional_statuses" value=true}
                            {else}
                                {assign var="get_additional_statuses" value=false}
                            {/if}
                            {assign var="order_status_descr" value=$smarty.const.STATUSES_ORDER|fn_get_simple_statuses:$get_additional_statuses:true}
                            {assign var="extra_status" value=$config.current_url|escape:"url"}
                            {if "MULTIVENDOR"|fn_allowed_for}
                                {assign var="notify_vendor" value=true}
                            {else}
                                {assign var="notify_vendor" value=false}
                            {/if}

                            {$statuses = []}
                            {assign var="order_statuses" value=$smarty.const.STATUSES_ORDER|fn_get_statuses:$statuses:$get_additional_statuses:true}
                            {include file="common/select_popup.tpl" suffix="o" id=$order_info.order_id status=$order_info.status items_status=$order_status_descr update_controller="orders" notify=true notify_department=true notify_vendor=$notify_vendor status_target_id="content_downloads" extra="&return_url=`$extra_status`" statuses=$order_statuses popup_additional_class="dropleft"}
                        {/hook}
                    </div>
                </div>

                <div class="control-group shift-top">
                    <div class="control-label">
                        {include file="common/subheader.tpl" title=__("payment_information")}
                    </div>
                </div>
                {hook name="orders:payment_info"}
                {* Payment info *}
                {if $order_info.payment_id}
                    <div class="control-group">
                        <div class="control-label">{__("method")}</div>
                        <div id="tygh_payment_info" class="controls">{$order_info.payment_method.payment}&nbsp;{if $order_info.payment_method.description}({$order_info.payment_method.description}){/if}
                        </div>
                    </div>

                    {if $order_info.payment_info}
                        {foreach from=$order_info.payment_info item=item key=key}
                        <div class="control-group">
                            {if $item && $key != "expiry_year"}
                                <div class="control-label">
                                {if $key == "card_number"}{assign var="cc_exists" value=true}{__("credit_card")}{elseif $key == "expiry_month"}{__("expiry_date")}{else}{__($key)}{/if}
                                </div>
                                <div class="controls">
                                    {if $key == "order_status"}
                                        {include file="common/status.tpl" status=$item display="view" status_type=""}
                                    {elseif $key == "reason_text"}
                                        {$item|nl2br}
                                    {elseif $key == "expiry_month"}
                                        {$item}/{$order_info.payment_info.expiry_year}
                                    {else}
                                        {$item}
                                    {/if}
                                </div>
                            {/if}
                        </div>
                        {/foreach}

                        {if $cc_exists}
                        <div class="control-group">
                            <div class="control-label">
                                <input type="hidden" name="order_ids[]" value="{$order_info.order_id}" />
                                {include file="buttons/button.tpl" but_text=__("remove_cc_info") but_meta="cm-ajax cm-comet" but_name="dispatch[orders.remove_cc_info]"}
                            </div>
                        </div>
                        {/if}
                    {/if}
                   {/if}
                {/hook}

                {* Shipping info *}
                {if $order_info.shipping}
                    <div class="control-group shift-top">
                        <div class="control-label">
                            {include file="common/subheader.tpl" title=__("shipping_information")}
                        </div>
                    </div>
                    {assign var="is_group_shippings" value=count($order_info.shipping)>1}

                    {foreach from=$order_info.shipping item="shipping" key="shipping_id" name="f_shipp"}

                        <div class="control-group" >
                            <span> {$shipping.group_name|default:__("none")}</span>
                        </div>

                        <div class="control-group">
                            <div class="control-label">{__("method")}</div>
                                <div id="tygh_shipping_info" class="controls">
                                    {$shipping.shipping}
                                </div>
                        </div>

                        {if $use_shipments}
                            <div class="clearfix">
                                {if $shipping.need_shipment}
                                    {if !"ULTIMATE:FREE"|fn_allowed_for}
                                        <div class="pull-left">
                                            {include file="common/popupbox.tpl" id="add_shipment_`$shipping.group_key`" content="" but_text=__("new_shipment") act="create" but_meta="btn"}
                                        </div>
                                    {else}
                                        <div class="pull-left">
                                            {include file="buttons/button.tpl" but_role="action" but_meta="cm-promo-popup" allow_href=false but_text=__("new_shipment")}
                                        </div>
                                    {/if}
                                {/if}
                                {if !$is_group_shippings}
                                    <a class="pull-right" href="{"shipments.manage?order_id=`$order_info.order_id`"|fn_url}">{__("shipments")}&nbsp;({$order_info.shipment_ids|count})</a>
                                {/if}
                            </div>
                            {if $is_group_shippings}<hr>{/if}
                        {else}
                            <div class="control-group">
                                <label class="control-label" for="tracking_number">{__("tracking_number")}</label>
                                <div class="controls">
                                    <input id="tracking_number" class="input-small" type="text" name="update_shipping[{$shipping.group_key}][shipment_data][tracking_number]" size="45" value="{$shipments[$shipping.group_key].tracking_number}" />
                                    <input type="hidden" name="update_shipping[{$shipping.group_key}][shipment_id]" value="{$shipments[$shipping.group_key].shipment_id}" />
                                    <input type="hidden" name="update_shipping[{$shipping.group_key}][shipment_data][shipping_id]" value="{$shipping.shipping_id}" />
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label" for="carrier_key">{__("carrier")}</label>
                                <div class="controls">
                                    {include file="common/carriers.tpl" id="carrier_key" meta="input-small" name="update_shipping[`$shipping.group_key`][shipment_data][carrier]" carrier=$shipments[$shipping.group_key].carrier}
                                </div>
                            </div>
                        {/if}
                    {/foreach}

                    {if $is_group_shippings}
                    <div class="clearfix">
                        <a class="pull-right" href="{"shipments.manage?order_id=`$order_info.order_id`"|fn_url}">{__("shipments")}&nbsp;({$order_info.shipment_ids|count})</a>
                    </div>
                    {/if}
                {else}

                    {foreach from=$order_info.product_groups item="group" key="group_id"}
                         {if $group.all_free_shipping}

                             {if $use_shipments}
                                 <div class="clearfix">
                                     {if $order_info.need_shipment}
                                         {if !"ULTIMATE:FREE"|fn_allowed_for}
                                             <div class="pull-left">
                                                 {include file="common/popupbox.tpl" id="add_shipment_0" content="" but_text=__("new_shipment") act="create" but_meta="btn"}
                                             </div>
                                         {else}
                                             <div class="pull-left">
                                                 {include file="buttons/button.tpl" but_role="action" but_meta="cm-promo-popup" allow_href=false but_text=__("new_shipment")}
                                             </div>
                                         {/if}
                                     {/if}

                                     <a class="pull-right" href="{"shipments.manage?order_id=`$order_info.order_id`"|fn_url}">{__("shipments")}&nbsp;({$order_info.shipment_ids|count})</a>
                                 </div>
                             {/if}
                         {/if}
                    {/foreach}
                {/if}
            </div>
            {hook name="orders:customer_shot_info"}
            {/hook}
        </div>
    </div>
<!--content_general--></div>

<div id="content_addons">

{hook name="orders:customer_info"}
{/hook}

<!--content_addons--></div>

{if $downloads_exist}
<div id="content_downloads">
    <input type="hidden" name="order_id" value="{$smarty.request.order_id}" />
    <input type="hidden" name="order_status" value="{$order_info.status}" />
    {foreach from=$order_info.products item="oi"}
    {if $oi.extra.is_edp == "Y"}
    <p><a href="{"products.update?product_id=`$oi.product_id`"|fn_url}">{$oi.product}</a></p>
        {if $oi.files}
        <input type="hidden" name="files_exists[]" value="{$oi.product_id}" />
        <table cellpadding="5" cellspacing="0" border="0" class="table">
        <tr>
            <th>{__("filename")}</th>
            <th>{__("activation_mode")}</th>
            <th>{__("downloads_max_left")}</th>
            <th>{__("download_key_expiry")}</th>
            <th>{__("active")}</th>
        </tr>
        {foreach from=$oi.files item="file"}
        <tr>
            <td>{$file.file_name}</td>
            <td>
                {if $file.activation_type == "M"}{__("manually")}</label>{elseif $file.activation_type == "I"}{__("immediately")}{else}{__("after_full_payment")}{/if}
            </td>
            <td>{if $file.max_downloads}{$file.max_downloads} / <input type="text" name="edp_downloads[{$file.ekey}][{$file.file_id}]" value="{math equation="a-b" a=$file.max_downloads b=$file.downloads|default:0}" size="3" />{else}{__("none")}{/if}</td>
            <td>
                {if $oi.extra.unlimited_download == 'Y'}
                    {__("time_unlimited_download")}
                {elseif $file.ekey}
                <p><label>{__("download_key_expiry")}: </label><span>{$file.ttl|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"|default:"n/a"}</span></p>
                
                <p><label>{__("prolongate_download_key")}: </label>{include file="common/calendar.tpl" date_id="prolongate_date_`$file.file_id`" date_name="prolongate_data[`$file.ekey`]" date_val=$file.ttl|default:$smarty.const.TIME start_year=$settings.Company.company_start_year}</p>
                {else}{__("file_doesnt_have_key")}{/if}
            </td>
            <td>
                <select name="activate_files[{$oi.product_id}][{$file.file_id}]">
                    <option value="Y" {if $file.active == "Y"}selected="selected"{/if}>{__("active")}</option>
                    <option value="N" {if $file.active != "Y"}selected="selected"{/if}>{__("not_active")}</option>
                </select>
            </td>
        </tr>
        {/foreach}
        </table>
        {/if}
    {/if}
    {/foreach}
<!--content_downloads--></div>
{/if}

{if $order_info.promotions}
<div id="content_promotions">
    {include file="views/orders/components/promotions.tpl" promotions=$order_info.promotions}
<!--content_promotions--></div>
{/if}

{hook name="orders:tabs_content"}
{/hook}

{hook name="orders:tabs_extra"}
{/hook}

{/capture}
{include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox active_tab=$smarty.request.selected_section track=true}

{/capture}
{capture name="mainbox_title"}
    {__("order")} #{$order_info.order_id} <span class="f-middle">{__("total")}: <span>{include file="common/price.tpl" value=$order_info.total}</span>{if $order_info.company_id} / {$order_info.company_id|fn_get_company_name}{/if}</span>

    <span class="f-small">
    {if $status_settings.appearance_type == "I" && $order_info.doc_ids[$status_settings.appearance_type]}
        ({__("invoice")} #{$order_info.doc_ids[$status_settings.appearance_type]})
    {elseif $status_settings.appearance_type == "C" && $order_info.doc_ids[$status_settings.appearance_type]}
        ({__("credit_memo")} #{$order_info.doc_ids[$status_settings.appearance_type]})
    {/if}
    {assign var="timestamp" value=$order_info.timestamp|date_format:"`$settings.Appearance.date_format`"|escape:url}
    / {$order_info.timestamp|date_format:"`$settings.Appearance.date_format`"},{$order_info.timestamp|date_format:"`$settings.Appearance.time_format`"}
    </span>
{/capture}

{capture name="sidebar"}
    {* Issuer info *}
    {include file="views/order_management/components/issuer_info.tpl" user_data=$order_info.issuer_data}
    {* Customer info *}
    {include file="views/order_management/components/profiles_info.tpl" user_data=$order_info location="I"}
{/capture}

{capture name="buttons"}  
    {include file="common/view_tools.tpl" url="orders.details?order_id="}

    {if $status_settings.appearance_type == "C" && $order_info.doc_ids[$status_settings.appearance_type]}
        {assign var="print_order" value=__("print_credit_memo")}
        {assign var="print_pdf_order" value=__("print_pdf_credit_memo")}
    {elseif $status_settings.appearance_type == "O"}
        {assign var="print_order" value=__("print_order_details")}
        {assign var="print_pdf_order" value=__("print_pdf_order_details")}
    {else}
        {assign var="print_order" value=__("print_invoice")}
        {assign var="print_pdf_order" value=__("print_pdf_invoice")}
    {/if}
    {capture name="tools_list"}
        {hook name="orders:details_tools"}
            <li>{btn type="list" text=$print_order href="orders.print_invoice?order_id=`$order_info.order_id`" class="cm-new-window"}</li>
            <li>{btn type="list" text=$print_pdf_order href="orders.print_invoice?order_id=`$order_info.order_id`&format=pdf"}</li>
            <li>{btn type="list" text=__("print_packing_slip") href="orders.print_packing_slip?order_id=`$order_info.order_id`" class="cm-new-window"}</li>
            <li>{btn type="list" text=__("print_pdf_packing_slip") href="orders.print_packing_slip?order_id=`$order_info.order_id`&format=pdf" class="cm-new-window"}</li>
            <li>{btn type="list" text=__("edit_order") href="order_management.edit?order_id=`$order_info.order_id`"}</li>
            {$smarty.capture.adv_tools nofilter}
        {/hook}
    {/capture}
    {dropdown content=$smarty.capture.tools_list}

    <div class="btn-group btn-hover dropleft">
        {include file="buttons/save_changes.tpl" but_meta="cm-no-ajax dropdown-toggle" but_role="submit-link" but_target_form="order_info_form" but_name="dispatch[orders.update_details]" save=true}
        <ul class="dropdown-menu">
            <li><a><input type="checkbox" name="notify_user" id="notify_user" value="Y" />
                {__("notify_customer")}</a></li>
            <li><a><input type="checkbox" name="notify_department" id="notify_department" value="Y" />
                {__("notify_orders_department")}</a></li>
            {if "MULTIVENDOR"|fn_allowed_for}
            <li>
                <a><input type="checkbox" name="notify_vendor" id="notify_vendor" value="Y" />
                    {__("notify_vendor")}</a>
            </li>
            {/if}
            {hook name="orders:notify_checkboxes"}
            {/hook}
        </ul>
    </div>
{/capture}

{include file="common/mainbox.tpl" title=$smarty.capture.mainbox_title content=$smarty.capture.mainbox buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons sidebar=$smarty.capture.sidebar sidebar_position="left"}

</form>
