{** block-description:buy_together **}

{script src="js/tygh/exceptions.js"}

{if $chains}

    {if !$config.tweaks.disable_dhtml && !$no_ajax}
        {assign var="is_ajax" value=true}
    {/if}
    
    <div id="content_buy_together">
    
    {foreach from=$chains key="key" item="chain"}
        {assign var="obj_prefix" value="bt_`$chain.chain_id`"}
        <form {if $is_ajax}class="cm-ajax cm-ajax-full-render"{/if} action="{""|fn_url}" method="post" name="chain_form_{$chain.chain_id}" enctype="multipart/form-data">
        <input type="hidden" name="redirect_url" value="{$config.current_url}" />
        <input type="hidden" name="result_ids" value="cart_status*,wish_list*" />
        {if !$stay_in_cart || $is_ajax}
            <input type="hidden" name="redirect_url" value="{$config.current_url}" />
        {/if}
        <input type="hidden" name="product_data[{$chain.product_id}_{$chain.chain_id}][chain]" value="{$chain.chain_id}" />
        <input type="hidden" name="product_data[{$chain.product_id}_{$chain.chain_id}][product_id]" value="{$chain.product_id}" />
        {include file="common/subheader.tpl" title=$chain.name}
        <div class="chain-content clearfix">
            <div class="chain-products scroll-x nowrap clearfix">
            {if $chain.products}
                <div class="chain-product">
                    <a href="{"products.view?product_id=`$chain.product_id`"|fn_url}">{include file="common/image.tpl" image_width=$settings.Thumbnails.product_lists_thumbnail_width image_height=$settings.Thumbnails.product_lists_thumbnail_height obj_id="`$chain.chain_id`_`$chain.product_id`" images=$chain.main_pair}</a>
                    <div class="chain-note"><a href="{"products.view?product_id=`$chain.product_id`"|fn_url}">{$chain.product_name}</a></div>
                    {if $chain.product_options}
                        {capture name="buy_together_product_options"}
                            <div id="buy_together_options_{$chain.chain_id}_{$key}" class="chain-product-options{if $settings.Appearance.default_product_details_view != "default_template"} long{/if}">
                                <div class="cm-reload-{$obj_prefix}{$chain.product_id}_{$chain.chain_id}" id="buy_together_options_update_{$chain.chain_id}_{$key}">
                                    <input type="hidden" name="appearance[show_product_options]" value="1" />
                                    <input type="hidden" name="appearance[bt_chain]" value="{$chain.chain_id}" />
                                    <input type="hidden" name="appearance[bt_id]" value="{$key}" />
                                    
                                    {include file="views/products/components/product_options.tpl" id="`$chain.product_id`_`$chain.chain_id`" product_options=$chain.product_options name="product_data" no_script=true extra_id="`$chain.product_id`_`$chain.chain_id`"}
                                </div>
                                <div class="buttons-container">
                                    {include file="buttons/button.tpl" but_id="add_item_close" but_name="" but_text=__("save_and_close") but_role="action" but_meta="cm-dialog-closer"}
                                </div>
                            </div>
                        {/capture}
                    <div class="chain-note">
                    {include file="common/popupbox.tpl" id="buy_together_options_`$chain.chain_id`_`$key`" link_meta="text-button" text=__("specify_options") content=$smarty.capture.buy_together_product_options link_text=__("specify_options") act="general"}
                    </div>
                    {/if}
                    <div class="chain-note">
                        {$chain.min_qty}&nbsp;x
                        {if !(!$auth.user_id && $settings.General.allow_anonymous_shopping == "hide_price_and_add_to_cart")}
                            {if $chain.price != $chain.discounted_price}
                                <span class="strike">{include file="common/price.tpl" value=$chain.price}</span>
                            {/if}
                            {include file="common/price.tpl" value=$chain.discounted_price}
                        {/if}
                    </div>
                </div>
            {/if}
            
            {foreach from=$chain.products key="_id" item="_product"}
                <div class="chain-plus">+</div>
                
                <div class="chain-product">
                    <input type="hidden" name="product_data[{$_product.product_id}][product_id]" value="{$_product.product_id}" />
                    <a href="{"products.view?product_id=`$_product.product_id`"|fn_url}">{include file="common/image.tpl" image_width=$settings.Thumbnails.product_lists_thumbnail_width image_height=$settings.Thumbnails.product_lists_thumbnail_height obj_id="`$chain.chain_id`_`$_product.product_id`" images=$_product.main_pair}</a>
                    <div class="chain-note"><a href="{"products.view?product_id=`$_product.product_id`"|fn_url}">{$_product.product_name}</a></div>
                    {if $_product.product_options}
                        {foreach from=$_product.product_options item="option"}
                            <div class="chain-note"><strong>{$option.option_name}</strong>: {$option.variant_name}</div>
                        {/foreach}
                    {elseif $_product.aoc}
                        {capture name="buy_together_product_options"}
                            <div id="buy_together_options_{$chain.chain_id}_{$_product.product_id}" class="chain-product-options{if $settings.Appearance.default_product_details_view != "default_template"} long{/if}">
                                <div class="cm-reload-{$obj_prefix}{$_product.product_id}" id="buy_together_options_update_{$chain.chain_id}_{$_id}">
                                    <input type="hidden" name="appearance[show_product_options]" value="1" />
                                    <input type="hidden" name="appearance[bt_chain]" value="{$chain.chain_id}" />
                                    <input type="hidden" name="appearance[bt_id]" value="{$_id}" />
                                    {include file="views/products/components/product_options.tpl" id=$_product.product_id product_options=$_product.options name="product_data" no_script=true product=$_product extra_id=$_product.product_id}
                                </div>
                                <div class="buttons-container">
                                    {include file="buttons/button.tpl" but_id="add_item_close" but_name="" but_text=__("save_and_close") but_role="action" but_meta="cm-dialog-closer"}
                                </div>
                            </div>
                        {/capture}
                        <div class="chain-note">
                        {include file="common/popupbox.tpl" id="buy_together_options_`$chain.chain_id`_`$_product.product_id`" link_meta="text-button" text=__("specify_options") content=$smarty.capture.buy_together_product_options link_text=__("specify_options") act="general"}
                        </div>
                    {/if}
                    <div class="chain-note">
                        {$_product.amount}&nbsp;x
                        {if !(!$auth.user_id && $settings.General.allow_anonymous_shopping == "hide_price_and_add_to_cart")}
                            {if $_product.price != $_product.discounted_price}
                                <span class="strike">{include file="common/price.tpl" value=$_product.price}</span>
                            {/if}
                            {include file="common/price.tpl" value=$_product.discounted_price}
                        {/if}
                    </div>
                </div>
            {/foreach}
            </div>
            
            {if $chain.description}
                <div class="chain-description">
                    {$chain.description nofilter}
                </div>
            {/if}
            
            {if !(!$auth.user_id && $settings.General.allow_anonymous_shopping == "hide_price_and_add_to_cart")}
            <div class="chain-price">
                <div class="chain-old-price">
                    <span class="chain-old">{__("total_list_price")}</span>
                    <span class="chain-old-line">{include file="common/price.tpl" value=$chain.total_price}</span>
                </div>
                <div class="chain-new-price">
                    <span class="chain-new">{__("price_for_all")}</span>
                    {include file="common/price.tpl" value=$chain.chain_price}
                </div>
            </div>
            {if !(!$auth.user_id && $settings.General.allow_anonymous_shopping == "hide_add_to_cart_button")}
                <div width="100%" class="buttons-container cm-buy-together-submit" id="wrap_chain_button_{$chain.chain_id}">
                        {include file="buttons/button.tpl" but_text=__("add_all_to_cart") but_id="chain_button_`$chain.chain_id`" but_name="dispatch[checkout.add]" but_role="action" obj_id=$obj_id}
                </div>
            {/if}
            {else}
            <p class="price">{__("sign_in_to_view_price")}</p>
            {/if}
        </div>
        
        </form>
    {/foreach}
    
    </div>
{/if}
