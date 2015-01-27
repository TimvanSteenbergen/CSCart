{if $cart.gift_certificates}

{assign var="c_url" value=$config.current_url|escape:url}

{foreach from=$cart.gift_certificates item="gift" key="gift_key" name="f_gift_certificates"}
{assign var="obj_id" value=$gift.object_id|default:$gift_key}
{if !$smarty.capture.prods}
    {capture name="prods"}Y{/capture}
{/if}
<tr>
    <td class="product-image-cell">
    {if $runtime.mode == "cart" || $show_images}
    <div class="product-image cm-reload-{$obj_id}" id="product_image_update_{$obj_id}">
        {if !$gift.extra.exclude_from_calculate}
            <a href="{"gift_certificates.update?gift_cert_id=`$gift_key`"|fn_url}">
            {include file="addons/gift_certificates/views/gift_certificates/components/gift_certificates_cart_icon.tpl" width=$settings.Thumbnails.product_cart_thumbnail_width height=$settings.Thumbnails.product_cart_thumbnail_height}
            </a>
            <p class="center">{include file="buttons/button.tpl" but_text=__("edit") but_href="gift_certificates.update?gift_cert_id=$gift_key" but_role="text"}</p>
        {else}
            {include file="addons/gift_certificates/views/gift_certificates/components/gift_certificates_cart_icon.tpl" width=$settings.Thumbnails.product_cart_thumbnail_width height=$settings.Thumbnails.product_cart_thumbnail_height}
        {/if}
    <!--product_image_update_{$obj_id}--></div>
    </td>
    <td class="product-description" style="width: 50%">
    {/if}
        {if !$gift.extra.exclude_from_calculate}
            <a href="{"gift_certificates.update?gift_cert_id=`$gift_key`"|fn_url}" class="product-title">{__("gift_certificate")}{if !$gift.extra.exclude_from_calculate}<a class="{$ajax_class} icon-delete-big" href="{"gift_certificates.delete?gift_cert_id=`$gift_key`&redirect_url=`$c_url`"|fn_url}"  data-ca-target-id="cart_items,checkout_totals,cart_status*,checkout_steps,checkout_cart" title="{__("remove")}"><i class="icon-cancel-circle"></i></a>{/if}</a>&nbsp;
            {if $use_ajax == true && $cart.amount != 1}
                {assign var="ajax_class" value="cm-ajax"}
            {/if}
        {else}
            <strong>{__("gift_certificate")}</strong>
        {/if}
        <div class="control-group product-list-field">
            <label class="valign">{__("gift_cert_to")}:</label><span>{$gift.recipient}</span>
        </div>
        <div class="control-group product-list-field">
            <label class="valign">{__("gift_cert_from")}:</label><span>{$gift.sender}</span>
        </div>
        <div class="control-group product-list-field">
            <label class="valign">{__("amount")}:</label><span>{include file="common/price.tpl" value=$gift.amount}</span>
        </div>
        <div class="control-group product-list-field">
            <label class="valign">{__("send_via")}:</label><span>{if $gift.send_via == "E"}{__("email")}{else}{__("postal_mail")}{/if}</span>
        </div>
        {if $gift.products && $addons.gift_certificates.free_products_allow == "Y" && !$gift.extra.exclude_from_calculate}
        
        <a id="sw_gift_products_{$gift_key}" class="cm-combination detailed-link">{__("free_products")}</a>

        <div id="gift_products_{$gift_key}" class="product-options hidden">
            <div class="caret-info-wrapper">
                <span class="caret-info light"> <span class="caret-outer"></span> <span class="caret-inner"></span></span>
            </div>
            <table class="table fixed-layout table-width">
            <tr>
                <th style="width: 40%">{__("product")}</th>
                <th style="width: 15%">{__("price")}</th>
                <th style="width: 15%">{__("qty")}</th>
                {if $cart.use_discount}
                <th style="width: 15%">{__("discount")}</th>
                {/if}
                {if $cart.taxes && $settings.General.tax_calculation != "subtotal"}
                <th style="width: 15%">{__("tax")}</th>
                {/if}
                <th class="right" style="width: 16%">{__("subtotal")}</th>
            </tr>
            {foreach from=$cart_products item="product" key="key"}
            {if $cart.products.$key.extra.parent.certificate == $gift_key}
            <tr {cycle values=",class=\"table-row\""}>
                <td style="width: 30%">
                    <a href="{"products.view?product_id=`$product.product_id`"|fn_url}" title="{$product.product nofilter}">{$product.product|strip_tags|truncate:70:"...":true nofilter}</a>
                    {if $use_ajax == true}
                        {assign var="ajax_class" value="cm-ajax"}
                    {/if}
                    <a class="{$ajax_class} icon-delete-big" href="{"checkout.delete?cart_id=`$key`&redirect_url=`$c_url`"|fn_url}" data-ca-target-id="cart_items,checkout_totals,cart_status*,checkout_steps" title="{__("remove")}"><i class="icon-cancel-circle"></i></a>
                    <p>{include file="common/options_info.tpl" product_options=$cart.products.$key.product_options|fn_get_selected_product_options_info fields_prefix="cart_products[`$key`][product_options]"}</p>
                    {hook name="checkout:product_info"}{/hook}
                    <input type="hidden" name="cart_products[{$key}][extra][parent][certificate]" value="{$gift_key}" /></td>
                <td class="center">
                    {include file="common/price.tpl" value=$product.original_price}</td>
                <td class="center">
                    <input type="text" size="3" name="cart_products[{$key}][amount]" value="{$product.amount}" class="input-text-short" {if $product.is_edp == "Y"}readonly="readonly"{/if} />
                    <input type="hidden" name="cart_products[{$key}][product_id]" value="{$product.product_id}" /></td>
                {if $cart.use_discount}
                <td class="center">
                    {if $product.discount|floatval}{include file="common/price.tpl" value=$product.discount}{else}-{/if}</td>
                {/if}
                {if $cart.taxes && $settings.General.tax_calculation != "subtotal"}
                <td class="center">
                    {include file="common/price.tpl" value=$product.tax_summary.total}</td>
                {/if}
                <td class="right">
                    {include file="common/price.tpl" value=$product.display_subtotal}</td>
            </tr>
            {/if}
            {/foreach}
            </table>
            <div class="control-group product-list-field float-right nowrap">
                <p><label class="valign">{__("price_summary")}:</label>
                {if !$gift.extra.exclude_from_calculate}{include file="common/price.tpl" value=$gift.display_subtotal class="price"}{else}<span class="price">{__("free")}</span>{/if}</p>
            </div>
            <div class="clear"></div>
        </div>
        {/if}
    </td>
    <td class="right price-cell cm-reload-{$obj_id}" id="price_display_update_{$obj_id}">
        {if !$gift.extra.exclude_from_calculate}{include file="common/price.tpl" value=$gift.display_subtotal class="sub-price"}{else}<span class="price">{__("free")}</span>{/if}
    <!--price_display_update_{$obj_id}--></td>
    <td class="quantity-cell center">
    </td>
    <td class="right price-cell cm-reload-{$obj_id}" id="price_subtotal_update_{$obj_id}">
        {if !$gift.extra.exclude_from_calculate}{include file="common/price.tpl" value=$gift.display_subtotal class="price"}{else}<span class="price">{__("free")}</span>{/if}
    <!--price_subtotal_update_{$obj_id}--></td>
</tr>
{/foreach}

{/if}
