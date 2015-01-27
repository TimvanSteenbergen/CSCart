{if $cart.gift_certificates}

{assign var="c_url" value=$config.current_url|escape:url}

{foreach from=$cart.gift_certificates item="gift" key="gift_key" name="f_gift_certificates"}
{assign var="obj_id" value=$gift.object_id|default:$gift_key}
{if !$smarty.capture.prods}
    {capture name="prods"}Y{/capture}
{/if}
<tr>
    <td class="ty-cart-content__product-elem ty-cart-content__image-block">
        {if $runtime.mode == "cart" || $show_images}
            <div class="ty-cart-content__image cm-reload-{$obj_id}" id="product_image_update_{$obj_id}">
                {if !$gift.extra.exclude_from_calculate}
                    <a href="{"gift_certificates.update?gift_cert_id=`$gift_key`"|fn_url}">
                    {include file="addons/gift_certificates/views/gift_certificates/components/gift_certificates_cart_icon.tpl" width=$settings.Thumbnails.product_cart_thumbnail_width height=$settings.Thumbnails.product_cart_thumbnail_height}
                    </a>
                    <div class="ty-mtb-xs ty-center">{include file="buttons/button.tpl" but_text=__("edit") but_href="gift_certificates.update?gift_cert_id=$gift_key" but_role="text"}</div>
                {else}
                    {include file="addons/gift_certificates/views/gift_certificates/components/gift_certificates_cart_icon.tpl" width=$settings.Thumbnails.product_cart_thumbnail_width height=$settings.Thumbnails.product_cart_thumbnail_height}
                {/if}
            <!--product_image_update_{$obj_id}--></div>
        {/if}
    </td>

    <td class="ty-cart-content__product-elem ty-cart-content__description" style="width: 50%;">
        {if !$gift.extra.exclude_from_calculate}
            <a href="{"gift_certificates.update?gift_cert_id=`$gift_key`"|fn_url}" class="ty-cart-content__product-title">
                {__("gift_certificate")}
            </a>
            {if !$gift.extra.exclude_from_calculate}
                <a class="{$ajax_class} ty-delete-big" href="{"gift_certificates.delete?gift_cert_id=`$gift_key`&redirect_url=`$c_url`"|fn_url}"  data-ca-target-id="cart_items,checkout_totals,cart_status*,checkout_steps,checkout_cart" title="{__("remove")}">
                    <i class="ty-delete-big__icon ty-icon-cancel-circle"></i>
                </a>
            {/if}
            {if $use_ajax == true && $cart.amount != 1}
                {assign var="ajax_class" value="cm-ajax"}
            {/if}
        {else}
            <strong>{__("gift_certificate")}</strong>
        {/if}
        <div class="ty-control-group">
            <label class="ty-control-group__label">{__("gift_cert_to")}:</label><span class="ty-control-group__item">{$gift.recipient}</span>
        </div>
        <div class="ty-control-group">
            <label class="ty-control-group__label">{__("gift_cert_from")}:</label><span class="ty-control-group__item">{$gift.sender}</span>
        </div>
        <div class="ty-control-group">
            <label class="ty-control-group__label">{__("amount")}:</label><span class="ty-control-group__item">{include file="common/price.tpl" value=$gift.amount}</span>
        </div>
        <div class="ty-control-group">
            <label class="ty-control-group__label">{__("send_via")}:</label><span class="ty-control-group__item">{if $gift.send_via == "E"}{__("email")}{else}{__("postal_mail")}{/if}</span>
        </div>
        {if $gift.products && $addons.gift_certificates.free_products_allow == "Y" && !$gift.extra.exclude_from_calculate}
        
        <a id="sw_gift_products_{$gift_key}" class="cm-combination ty-cart-content__detailed-link">{__("free_products")}</a>

        <div id="gift_products_{$gift_key}" class="hidden">
            <div class="ty-cart-content-products">
            <div class="ty-caret-info"><span class="ty-caret-outer"></span> <span class="ty-caret-inner"></span></div>
            {foreach from=$cart_products item="product" key="key"}
            {if $cart.products.$key.extra.parent.certificate == $gift_key}
                <div class="ty-cart-content-products__item">
                    <div>
                        <a href="{"products.view?product_id=`$product.product_id`"|fn_url}" title="{$product.product nofilter}">{$product.product|strip_tags|truncate:70:"...":true nofilter}</a>
                        {if $use_ajax == true}
                            {assign var="ajax_class" value="cm-ajax"}
                        {/if}
                        <a class="{$ajax_class} ty-delete-big" href="{"checkout.delete?cart_id=`$key`&redirect_url=`$c_url`"|fn_url}" data-ca-target-id="cart_items,checkout_totals,cart_status*,checkout_steps" title="{__("remove")}"><i class="ty-delete-big__icon ty-icon-cancel-circle"></i></a>
                        {include file="common/options_info.tpl" product_options=$cart.products.$key.product_options|fn_get_selected_product_options_info fields_prefix="cart_products[`$key`][product_options]"}
                        {hook name="checkout:product_info"}{/hook}
                        <input type="hidden" name="cart_products[{$key}][extra][parent][certificate]" value="{$gift_key}" />
                    </div>
                    <div class="ty-control-group">
                        <strong class="ty-control-group__label">{__("price")}</strong>
                        <span class="ty-control-group__item">
                            {include file="common/price.tpl" value=$product.original_price}
                        </span>
                    </div>
                    <div class="ty-control-group">
                        <strong class="ty-control-group__label">{__("qty")}</strong>
                        <input type="text" size="3" name="cart_products[{$key}][amount]" value="{$product.amount}" class="ty-input-text-short" {if $product.is_edp == "Y"}readonly="readonly"{/if} />
                        <input type="hidden" name="cart_products[{$key}][product_id]" value="{$product.product_id}" />
                    </div>
                    {if $cart.use_discount}
                    <div class="ty-control-group">
                        <strong class="ty-control-group__label">{__("discount")}</strong>
                        <span class="ty-control-group__item">
                            {if $product.discount|floatval}{include file="common/price.tpl" value=$product.discount}{else}-{/if}
                        </span>
                    </div>
                    {/if}
                    {if $cart.taxes && $settings.General.tax_calculation != "subtotal"}
                    <div class="ty-control-group">
                        <strong class="ty-control-group__label">{__("tax")}</strong>
                        <span class="ty-control-group__item">
                            {include file="common/price.tpl" value=$product.tax_summary.total}
                        </span>
                    </div>
                    {/if}
                    <div class="ty-control-group">
                        <strong class="ty-control-group__label">{__("subtotal")}</strong>
                        <span class="ty-control-group__item">
                            {include file="common/price.tpl" value=$product.display_subtotal}
                        </span>
                    </div>
                </div>
            {/if}
            {/foreach}
            </div>
            <div class="ty-control-group ty-float-right">
                <strong>{__("price_summary")}:&nbsp;</strong>
                {if !$gift.extra.exclude_from_calculate}{include file="common/price.tpl" value=$gift.display_subtotal class="ty-price"}{else}<span class="ty-price">{__("free")}</span>{/if}
            </div>
            <div class="clearfix"></div>
        </div>
        {/if}
    </td>
    <td class="ty-cart-content__product-elem ty-cart-content__price cm-reload-{$obj_id}" id="price_display_update_{$obj_id}">
        {if !$gift.extra.exclude_from_calculate}{include file="common/price.tpl" value=$gift.display_subtotal class="ty-sub-price"}{else}<span class="ty-price">{__("free")}</span>{/if}
    <!--price_display_update_{$obj_id}--></td>
    <td class="ty-cart-content__product-elem ty-cart-content__qty">
    </td>
    <td class="ty-cart-content__product-elem ty-cart-content__price cm-reload-{$obj_id}" id="price_subtotal_update_{$obj_id}">
        {if !$gift.extra.exclude_from_calculate}{include file="common/price.tpl" value=$gift.display_subtotal class="price"}{else}<span class="ty-price">{__("free")}</span>{/if}
    <!--price_subtotal_update_{$obj_id}--></td>
</tr>
{/foreach}

{/if}
