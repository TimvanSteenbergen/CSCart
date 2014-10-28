<div class="orders">

{if $order_info}

{capture name="order_actions"}
{if $view_only != "Y"}    
        <div class="orders-print">
            {hook name="orders:details_tools"}
            {assign var="print_order" value=__("print_invoice")}
            {assign var="print_pdf_order" value=__("print_pdf_invoice")}
            {if $status_settings.appearance_type == "C" && $order_info.doc_ids[$status_settings.appearance_type]}
                {assign var="print_order" value=__("print_credit_memo")}
                {assign var="print_pdf_order" value=__("print_pdf_credit_memo")}
            {elseif $status_settings.appearance_type == "O"}
                {assign var="print_order" value=__("print_order_details")}
                {assign var="print_pdf_order" value=__("print_pdf_order_details")}
            {/if}
            
            <span><i class="icon-print"></i>{include file="buttons/button.tpl" but_role="text" but_text=$print_order but_href="orders.print_invoice?order_id=`$order_info.order_id`" but_meta="cm-new-window"}</span>
            <span><i class="icon-doc-text"></i>{include file="buttons/button.tpl" but_role="text" but_meta="pdf cm-no-ajax" but_text=$print_pdf_order but_href="orders.print_invoice?order_id=`$order_info.order_id`&format=pdf"}</span>        
            {/hook}
            <ul class="orders-actions right">
            {if $view_only != "Y"}
                {hook name="orders:details_bullets"}
                {/hook}
            {/if}
            <li><i class="icon-cw"></i>{include file="buttons/button.tpl" but_role="text" but_text=__("re_order") but_href="orders.reorder?order_id=`$order_info.order_id`"}</li>
            </ul>
        </div>
    {/if}
{/capture}

{capture name="tabsbox"}
    
<div id="content_general" class="{if $selected_section && $selected_section != "general"}hidden{/if}">

    {if $without_customer != "Y"}
    {* Customer info *}
        <div class="orders-customer">
        {include file="views/profiles/components/profiles_info.tpl" user_data=$order_info location="I"}
        </div>
    {* /Customer info *}
    {/if}


{capture name="group"}

{include file="common/subheader.tpl" title=__("products_information")}

<table class="table table-width">
{hook name="orders:items_list_header"}
<thead>
<tr>
    <th class="product">{__("product")}</th>
    <th class="price" class="align-right">{__("price")}</th>
    <th class="quantity">{__("quantity")}</th>
    {if $order_info.use_discount}
        <th>{__("discount")}</th>
    {/if}
    {if $order_info.taxes && $settings.General.tax_calculation != "subtotal"}
        <th>{__("tax")}</th>
    {/if}
    <th class="subtotal">{__("subtotal")}</th>
</tr>
</thead>
{/hook}
{foreach from=$order_info.products item="product" key="key"}
{hook name="orders:items_list_row"}
{if !$product.extra.parent}
{cycle values=",class=\"table-row\"" name="class_cycle" assign="_class"}
<tr {$_class} style="vertical-align: top;">
    <td>{if $product.is_accessible}<a href="{"products.view?product_id=`$product.product_id`"|fn_url}" class="product-title">{/if}{$product.product nofilter}{if $product.is_accessible}</a>{/if}
        {if $product.extra.is_edp == "Y"}
        <div class="right"><a href="{"orders.order_downloads?order_id=`$order_info.order_id`"|fn_url}"><strong>[{__("download")}]</strong></a></div>
        {/if}
        {if $product.product_code}
        <p class="code">{__("sku")}:&nbsp;{$product.product_code}</p>
        {/if}
        {hook name="orders:product_info"}
        {if $product.product_options}{include file="common/options_info.tpl" product_options=$product.product_options inline_option=true}{/if}
        {/hook}
    </td>
    <td class="right nowrap">
        {if $product.extra.exclude_from_calculate}{__("free")}{else}{include file="common/price.tpl" value=$product.original_price}{/if}</td>
    <td class="center">&nbsp;{$product.amount}</td>
    {if $order_info.use_discount}
        <td class="right nowrap">
            {if $product.extra.discount|floatval}{include file="common/price.tpl" value=$product.extra.discount}{else}-{/if}
        </td>
    {/if}
    {if $order_info.taxes && $settings.General.tax_calculation != "subtotal"}
        <td class="center nowrap">
            {if $product.tax_value|floatval}{include file="common/price.tpl" value=$product.tax_value}{else}-{/if}
        </td>
    {/if}
    <td class="right">
         &nbsp;<strong>{if $product.extra.exclude_from_calculate}{__("free")}{else}{include file="common/price.tpl" value=$product.display_subtotal}{/if}</strong></td>
</tr>
{/if}
{/hook}
{/foreach}

{hook name="orders:extra_list"}
    {assign var="colsp" value=5}
    {if $order_info.use_discount}{assign var="colsp" value=$colsp+1}{/if}
    {if $order_info.taxes && $settings.General.tax_calculation != "subtotal"}{assign var="colsp" value=$colsp+1}{/if}
{/hook}

</table>

{*Customer notes*}
    {if $order_info.notes}
    <div class="orders-notes">
        {include file="common/subheader.tpl" title=__("customer_notes")}
        <div class="orders-notes-body">
            <span class="caret"> <span class="caret-outer"></span> <span class="caret-inner"></span></span>
            {$order_info.notes}
        </div>
    </div>
    {/if}
{*/Customer notes*}

<div class="orders-summary">
{include file="common/subheader.tpl" title=__("summary")}

<div class="float-right">
    {hook name="orders:info"}{/hook}
</div>

<div class="orders-summary-wrap">
<table>
{hook name="orders:totals"}
    {if $order_info.payment_id}
    <tr>
        <td>{__("payment_method")}:&nbsp;</td>
        <td style="width: 57%" data-ct-orders-summary="summary-payment">
        {hook name="orders:totals_payment"}
            {$order_info.payment_method.payment}&nbsp;{if $order_info.payment_method.description}({$order_info.payment_method.description}){/if}
        {/hook}
        </td>
    </tr>
    {/if}

    {if $order_info.shipping}
        <tr>
            <td>{__("shipping_method")}:</td>
            <td data-ct-orders-summary="summary-ship">
            {hook name="orders:totals_shipping"}
            {if $use_shipments}
                <ul>
                    {foreach from=$order_info.shipping item="shipping_method"}
                        <li>{if $shipping_method.shipping} {$shipping_method.shipping} {else} – {/if}</li>
                    {/foreach}
                </ul>
            {else}
                {foreach from=$order_info.shipping item="shipping" name="f_shipp"}
                    {if $shipments[$shipping.group_key].carrier && $shipments[$shipping.group_key].tracking_number}
                        {include file="common/carriers.tpl" carrier=$shipments[$shipping.group_key].carrier tracking_number=$shipments[$shipping.group_key].tracking_number}

                        {$shipping.shipping}&nbsp;({__("tracking_num")}<a {if $smarty.capture.carrier_url|strpos:"://"}target="_blank"{/if} href="{$smarty.capture.carrier_url nofilter}">{$shipments[$shipping.group_key].tracking_number}</a>)

                        {$smarty.capture.carrier_info nofilter}
                    {else}
                        {$shipping.shipping}
                    {/if}
                    {if !$smarty.foreach.f_shipp.last}<br>{/if}
                {/foreach}
            {/if}
            {/hook}
            </td>
        </tr>
    {/if}

    <tr>
        <td>{__("subtotal")}:&nbsp;</td>
        <td data-ct-orders-summary="summary-subtotal">{include file="common/price.tpl" value=$order_info.display_subtotal}</td>
    </tr>
    {if $order_info.display_shipping_cost|floatval}
    <tr>
        <td>{__("shipping_cost")}:&nbsp;</td>
        <td data-ct-orders-summary="summary-shipcost">{include file="common/price.tpl" value=$order_info.display_shipping_cost}</td>
    </tr>
    {/if}

    {if $order_info.discount|floatval}
    <tr>
        <td class="nowrap strong">{__("including_discount")}:</td>
        <td class="nowrap" data-ct-orders-summary="summary-discount">
            {include file="common/price.tpl" value=$order_info.discount}</td>
    </tr>
    {/if}

    {if $order_info.subtotal_discount|floatval}
    <tr>
        <td class="nowrap strong">{__("order_discount")}:</td>
        <td class="nowrap" data-ct-orders-summary="summary-sub-discount">
            {include file="common/price.tpl" value=$order_info.subtotal_discount}</td>
    </tr>
    {/if}

    {if $order_info.coupons}
    {foreach from=$order_info.coupons item="coupon" key="key"}
    <tr>
        <td class="nowrap">{__("coupon")}:</td>
        <td data-ct-orders-summary="summary-coupons">{$key}</td>
    </tr>
    {/foreach}
    {/if}

    {if $order_info.taxes}
    <tr class="taxes">
        <td><strong>{__("taxes")}:</strong></td>
        <td>&nbsp;</td>
    </tr>
    {foreach from=$order_info.taxes item=tax_data}
    <tr class="taxes-desc">
        <td class="summary-taxes">{$tax_data.description}&nbsp;{include file="common/modifier.tpl" mod_value=$tax_data.rate_value mod_type=$tax_data.rate_type}{if $tax_data.price_includes_tax == "Y" && ($settings.Appearance.cart_prices_w_taxes != "Y" || $settings.General.tax_calculation == "subtotal")}&nbsp;{__("included")}{/if}{if $tax_data.regnumber}&nbsp;({$tax_data.regnumber}){/if}&nbsp;</td>
        <td data-ct-orders-summary="summary-tax-sub">{include file="common/price.tpl" value=$tax_data.tax_subtotal}</td>
    </tr>
    {/foreach}
    {/if}
    {if $order_info.tax_exempt == "Y"}
    <tr>
        <td>{__("tax_exempt")}</td>
        <td>&nbsp;</td>
    <tr>
    {/if}

    {if $order_info.payment_surcharge|floatval && !$take_surcharge_from_vendor}
    <tr>
        <td>{$order_info.payment_method.surcharge_title|default:__("payment_surcharge")}:&nbsp;</td>
        <td data-ct-orders-summary="summary-surchange">{include file="common/price.tpl" value=$order_info.payment_surcharge}</td>
    </tr>
    {/if}
    <tr class="total">
        <td>{__("total")}:&nbsp;</td>
        <td data-ct-orders-summary="summary-total">{include file="common/price.tpl" value=$order_info.total}</td>
    </tr>
{/hook}



</table>
    </div>
    <div class="clear"></div>
</div>

{if $order_info.promotions}
    {include file="views/orders/components/promotions.tpl" promotions=$order_info.promotions}
{/if}

{if $view_only != "Y"}
<div class="orders-repay">
    {hook name="orders:repay"}
    {if $settings.General.repay == "Y" && $payment_methods}
        {include file="views/orders/components/order_repay.tpl"}
    {/if}
    {/hook}
    </div>
{/if}


{/capture}
<div class="orders-product">
{include file="common/group.tpl"  content=$smarty.capture.group}
</div>
</div><!-- main order info -->

{if !"ULTIMATE:FREE"|fn_allowed_for}
{if $use_shipments}
    <div id="content_shipment_info" class="orders-shipment {if $selected_section != "shipment_info"}hidden{/if}">
        {foreach from=$shipments key="id" item="shipment"}
            {math equation="id + 1" id=$id assign="shipment_display_id"}
            {include file="common/subheader.tpl" title="{__("shipment")} #`$shipment_display_id`"}

            <table class="table table-width">
            <thead>
            <tr>
                <th style="width: 90%">{__("product")}</th>
                <th>{__("quantity")}</th>
            </tr>
            </thead>
            {foreach from=$shipment.products key="product_hash" item="amount"}
            {if $order_info.products.$product_hash}
                {assign var="product" value=$order_info.products.$product_hash}
                {cycle values=",class=\"table-row\"" name="class_cycle" assign="_class"}
                <tr {$_class} style="vertical-align: top;">
                    <td>{if $product.is_accessible}<a href="{"products.view?product_id=`$product.product_id`"|fn_url}" class="product-title">{/if}{$product.product nofilter}{if $product.is_accessible}</a>{/if}
                        {if $product.extra.is_edp == "Y"}
                        <div class="right"><a href="{"orders.order_downloads?order_id=`$order_info.order_id`"|fn_url}"><strong>[{__("download")}]</strong></a></div>
                        {/if}
                        {if $product.product_code}
                        <p>{__("sku")}:&nbsp;{$product.product_code}</p>
                        {/if}
                        {if $product.product_options}{include file="common/options_info.tpl" product_options=$product.product_options inline_option=true}{/if}
                    </td>
                    <td class="center">&nbsp;{$amount}</td>
                </tr>
            {/if}
            {/foreach}
            </table>

            <div class="orders-shipment-info"><h2>{__("shipping_information")}</h2>

                <p>{$shipment.shipping}</p>

                {if $shipment.carrier}
                    {include file="common/carriers.tpl" carrier=$shipment.carrier tracking_number=$shipment.tracking_number shipment_id=$shipment.shipment_id}
                    <p>{__("carrier")}: {$smarty.capture.carrier_name nofilter}{if $shipment.tracking_number}({__("tracking_num")}{if $smarty.capture.carrier_url|trim != ""}<a {if $smarty.capture.carrier_url|strpos:"://"}target="_blank"{/if} href="{$smarty.capture.carrier_url nofilter}">{/if}{$shipment.tracking_number}{if $smarty.capture.carrier_url|trim != ""}</a>{/if}){/if}</p>

                    {$smarty.capture.carrier_info nofilter}
                {/if}
            </div>
            {if $shipment.comments}
                <div class="orders-shipment-comments"><h2>{__("comments")}</h2><br />
                    <div class="orders-notes-body">
                        <div class="orders-notes-arrow"></div>
                        {$shipment.comments}
                    </div>
                </div>
            {/if}
            
        {foreachelse}
            <p class="no-items">{__("text_no_shipments_found")}</p>
        {/foreach}
    </div>
{/if}
{/if}

{hook name="orders:tabs"}
{/hook}    

{/capture}
{include file="common/tabsbox.tpl" top_order_actions=$smarty.capture.order_actions content=$smarty.capture.tabsbox active_tab=$smarty.request.selected_section}

{/if}
</div>

{hook name="orders:details"}
{/hook}

{capture name="mainbox_title"}
    <em class="status">{__("status")}: {include file="common/status.tpl" status=$order_info.status display="view" name="update_order[status]"}</em>
    {__("order")}&nbsp;#{$order_info.order_id}
    <em class="date">({$order_info.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"})</em>
{/capture}
