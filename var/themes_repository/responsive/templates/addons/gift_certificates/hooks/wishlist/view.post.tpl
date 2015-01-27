{if $wishlist.gift_certificates}

{foreach from=$wishlist.gift_certificates item="gift" key="gift_key" name="gift_certificates"}
{math equation="it + 1" assign="iteration" it=$iteration}

    <div class="ty-gift-certificate-wishlist ty-column{$columns}">

            <div class="ty-grid-list__item ty-quick-view-button__wrapper">
                <div class="ty-twishlist-item">
                    <a href="{"gift_certificates.wishlist_delete?gift_cert_wishlist_id=`$gift_key`"|fn_url}" class="ty-twishlist-item__remove ty-remove" title="{__("remove")}"><i class="ty-remove__icon ty-icon-cancel-circle"></i><span class="ty-remove__txt ty-twishlist-item__txt">{__("remove")}</span></a>
                </div>
                <div class="ty-grid-list__image">
                    <a href="{"gift_certificates.update?gift_cert_wishlist_id=`$gift_key`"|fn_url}">{include file="addons/gift_certificates/views/gift_certificates/components/gift_certificates_cart_icon.tpl" width=$settings.Thumbnails.product_lists_thumbnail_width height=$settings.Thumbnails.product_lists_thumbnail_height}</a>
                </div>
                <div class="ty-grid-list__item-name">
                    <a href="{"gift_certificates.update?gift_cert_wishlist_id=`$gift_key`"|fn_url}">{__("gift_certificate")}{if $gift.products} + {__("free_products")}{/if}</a>
                </div>
                <div class="ty-grid-list__price">
                    {include file="common/price.tpl" value=$gift.amount}
                </div>

                <div class="ty-grid-list__control">
                    <div class="ty-quick-view-button">
                        <a id="opener_gift_cert_picker_{$gift_key}" class="ty-btn ty-btn__secondary ty-btn__big cm-dialog-opener cm-dialog-auto-size" data-ca-target-id="gift_cert_quick_view_{$gift_key}" href="{"gift_certificates.update?gift_cert_wishlist_id=`$gift_key`"|fn_url}" rel="nofollow">{__("quick_view")}</a>
                    </div>
                </div>
            </div>

            <div class="hidden" id="gift_cert_quick_view_{$gift_key}" title="{__("gift_certificate")}">
                <form action="{""|fn_url}" {if !$config.tweaks.disable_dhtml}class="cm-ajax cm-form-dialog-closer"{/if} method="post" name="{$form_prefix}gift_cert_form_{$gift_key}">

                <input type="hidden" value="cart_status*,wish_list*" name="result_ids" />
                <input type="hidden" name="gift_cert_data[send_via]" value="{$gift.send_via}" />
                <input type="hidden" name="gift_cert_data[amount]" value="{$gift.amount}" />
                <input type="hidden" name="gift_cert_data[correct_amount]" value="N" />
                <input type="hidden" name="gift_cert_data[recipient]" value="{$gift.recipient}" />
                <input type="hidden" name="gift_cert_data[sender]" value="{$gift.sender}" />
                <input type="hidden" name="gift_cert_data[message]" value="{$gift.message}" />
                {if $gift.email}<input type="hidden" name="gift_cert_data[email]" value="{$gift.email}" />{/if}
                {if $gift.title}<input type="hidden" name="gift_cert_data[title]" value="{$gift.title}" />{/if}
                {if $gift.firstname}<input type="hidden" name="gift_cert_data[firstname]" value="{$gift.firstname}" />{/if}
                {if $gift.lastname}<input type="hidden" name="gift_cert_data[lastname]" value="{$gift.lastname}" />{/if}
                {if $gift.address}<input type="hidden" name="gift_cert_data[address]" value="{$gift.address}" />{/if}
                {if $gift.city}<input type="hidden" name="gift_cert_data[city]" value="{$gift.city}" />{/if}
                {if $gift.country}<input type="hidden" name="gift_cert_data[country]" value="{$gift.country}" />{/if}
                {if $gift.state}<input type="hidden" name="gift_cert_data[state]" value="{$gift.state}" />{/if}
                {if $gift.zipcode}<input type="hidden" name="gift_cert_data[zipcode]" value="{$gift.zipcode}" />{/if}

                <div class="ty-quick-view__wrapper ty-product-block">
                    <div class="ty-product-block__img">
                        <a href="{"gift_certificates.update?gift_cert_wishlist_id=`$gift_key`"|fn_url}">{include file="addons/gift_certificates/views/gift_certificates/components/gift_certificates_cart_icon.tpl" width="150" height="150"}</a>

                        <div class="ty-mtb-xs ty-center">{include file="buttons/button.tpl" but_text=__("edit") but_href="gift_certificates.update?gift_cert_wishlist_id=$gift_key" but_role="text"}</div>
                    </div>
                    <div class="ty-product-block__left">
                        <a href="{"gift_certificates.update?gift_cert_wishlist_id=`$gift_key`"|fn_url}" class="product-title">{__("gift_certificate")}</a>
                        <div class="ty-control-group product-list-field">
                            <label class="ty-control-group__label">{__("gift_cert_to")}:</label>
                            <span class="ty-control-group__item">{$gift.recipient}</span>
                        </div>
                        <div class="ty-control-group product-list-field">
                            <label class="ty-control-group__label">{__("gift_cert_from")}:</label>
                            <span class="ty-control-group__item">{$gift.sender}</span>
                        </div>
                        <div class="ty-control-group product-list-field">
                            <label class="ty-control-group__label">{__("amount")}:</label>
                            <span class="ty-control-group__item">{include file="common/price.tpl" value=$gift.amount}</span>
                        </div>
                        <div class="ty-control-group product-list-field">
                            <label class="ty-control-group__label">{__("send_via")}:</label>
                            <span class="ty-control-group__item">{if $gift.send_via == "E"}{__("email")}{else}{__("postal_mail")}{/if}</span>
                        </div>

                        <div class="clearfix"></div>
                        {if $gift.products && $addons.gift_certificates.free_products_allow == "Y"}
                        <div class="clearfix">

                            <p><strong>{__("free_products")}:</strong></p>

                            {assign var="gift_price" value=""}
                            <table class="ty-table">
                            <tr>
                                <th style="width: 50%">{__("product")}</th>
                                <th style="width: 10%">{__("price")}</th>
                                <th style="width: 10%">{__("quantity")}</th>
                                <th class="ty-right" style="width: 10%">{__("subtotal")}</th>
                            </tr>
                            {foreach from=$extra_products item="_product" key="key_cert_prod"}

                                {if $wishlist.products.$key_cert_prod.extra.parent.certificate == $gift_key}

                                <input type="hidden" name="gift_cert_data[products][{$key_cert_prod}][product_id]" value="{$wishlist.products.$key_cert_prod.product_id}" />
                                <input type="hidden" name="gift_cert_data[products][{$key_cert_prod}][amount]" value="{$wishlist.products.$key_cert_prod.amount}" />

                                {math equation="item_price + gift_" item_price=$_product.subtotal|default:"0" gift_=$gift_price|default:"0" assign="gift_price"}
                                <tr>
                                    <td>
                                        <a href="{"products.view?product_id=`$_product.product_id`"|fn_url}">{$_product.product}</a>
                                        {if $_product.product_options}
                                            {include file="common/options_info.tpl" product_options=$_product.product_options fields_prefix="gift_cert_data[products][`$key_cert_prod`][product_options]"}
                                        {/if}
                                    </td>
                                    <td class="ty-center">
                                        {include file="common/price.tpl" value=$_product.price}</td>
                                    <td class="ty-center ty-nowrap">
                                        {$gift.products.$key_cert_prod.amount}</td>
                                    <td class="ty-right ty-nowrap">
                                        {math equation="item_price*amount" item_price=$_product.price|default:"0" assign="subtotal" amount=$gift.products.$key_cert_prod.amount}
                                        {math equation="subtotal + gift_" subtotal=$subtotal|default:"0" gift_=$gift_price|default:"0" assign="gift_price"}
                                        {include file="common/price.tpl" value=$subtotal}</td>
                                </tr>
                                {/if}

                            {/foreach}
                            </table>

                            <div class="ty-control-group product-list-field ty-float-right">
                                <label class="ty-control-group__label">{__("price_summary")}:</label>
                                <span class="ty-control-group__item">
                                    {math equation="item_price + gift_" item_price=$gift_price|default:"0" gift_=$gift.amount|default:"0" assign="gift_price"}
                                    <strong>{include file="common/price.tpl" value=$gift_price}</strong>
                                </span>
                            </div>
                        </div>
                        {/if}

                        <div class="ty-product-block__button">
                            {include file="buttons/add_to_cart.tpl" but_role="big" but_name="dispatch[gift_certificates.add]"}
                        </div>
                    </div>
                </div>
            </form>
       </div>
    </div>

{/foreach}
{capture name="iteration"}{$iteration}{/capture}
{/if}