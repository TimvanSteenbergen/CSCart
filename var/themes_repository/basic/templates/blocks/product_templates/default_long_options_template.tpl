
{script src="js/tygh/exceptions.js"}

<div class="product-main-info long">
<div class="clearfix">
{hook name="products:view_main_info"}

    {if $product}
    {assign var="obj_id" value=$product.product_id}
    {include file="common/product_data.tpl" product=$product separate_buttons=$separate_buttons|default:true but_role="big" but_text=__("add_to_cart")}
        <div class="image-wrap float-left">
            {hook name="products:image_wrap"}
                {if !$no_images}
                    <div class="image-border center cm-reload-{$product.product_id}" id="product_images_{$product.product_id}_update">

                        {assign var="discount_label" value="discount_label_`$obj_prefix``$obj_id`"}
                        {$smarty.capture.$discount_label nofilter}

                        {include file="views/products/components/product_images.tpl" product=$product show_detailed_link="Y" image_width=$settings.Thumbnails.product_details_thumbnail_width image_height=$settings.Thumbnails.product_details_thumbnail_height}
                    <!--product_images_{$product.product_id}_update--></div>
                {/if}
            {/hook}
        </div>
        <div class="product-info">
            {assign var="form_open" value="form_open_`$obj_id`"}
            {$smarty.capture.$form_open nofilter}

            {hook name="products:main_info_title"}
            {if !$hide_title}<h1 class="mainbox-title">{$product.product nofilter}</h1>{/if}

            <div class="brand-wrapper">
                {include file="views/products/components/product_features_short_list.tpl" features=$product.header_features}
            </div>
            {/hook}

            <hr class="indented" />
            {assign var="old_price" value="old_price_`$obj_id`"}
            {assign var="price" value="price_`$obj_id`"}
            {assign var="clean_price" value="clean_price_`$obj_id`"}
            {assign var="list_discount" value="list_discount_`$obj_id`"}
            {assign var="discount_label" value="discount_label_`$obj_id`"}

            {hook name="products:promo_text"}
            <div class="product-note">
                {$product.promo_text nofilter}
            </div>
            {/hook}

            <div class="{if $smarty.capture.$old_price|trim || $smarty.capture.$clean_price|trim || $smarty.capture.$list_discount|trim}prices-container {/if}price-wrap clearfix product-detail-price">
            {if $smarty.capture.$old_price|trim || $smarty.capture.$clean_price|trim || $smarty.capture.$list_discount|trim}
                <div class="float-left product-prices">
                    {if $smarty.capture.$old_price|trim}{$smarty.capture.$old_price nofilter}&nbsp;{/if}
            {/if}
            
            {if !$smarty.capture.$old_price|trim || $details_page}<p class="actual-price">{/if}
                    {$smarty.capture.$price nofilter}
            {if !$smarty.capture.$old_price|trim || $details_page}</p>{/if}
        
            {if $smarty.capture.$old_price|trim || $smarty.capture.$clean_price|trim || $smarty.capture.$list_discount|trim}
                    {$smarty.capture.$clean_price nofilter}
                    {$smarty.capture.$list_discount nofilter}
                </div>
            {/if}

            </div>
            {if $capture_options_vs_qty}{capture name="product_options"}{$smarty.capture.product_options nofilter}{/if}
                <div class="options-wrapper indented qty-option-wrapper">
                    {assign var="product_options" value="product_options_`$obj_id`"}
                    {$smarty.capture.$product_options nofilter}
                </div>
            {if $capture_options_vs_qty}{/capture}{/if}
                <div class="indented product-option-wrapper">
            {if $capture_options_vs_qty}{capture name="product_options"}{$smarty.capture.product_options nofilter}{/if}
                {assign var="advanced_options" value="advanced_options_`$obj_id`"}
                {$smarty.capture.$advanced_options nofilter}
            {if $capture_options_vs_qty}{/capture}{/if}
                    {$sku = "sku_`$obj_id`"}
                    {$smarty.capture.$sku nofilter}
                </div>         

            {if $capture_options_vs_qty}{capture name="product_options"}{$smarty.capture.product_options nofilter}{/if}              
            <div class="indented fields-option-wrapper">
                <div class="product-fields-group">
                    {assign var="product_amount" value="product_amount_`$obj_id`"}
                    {$smarty.capture.$product_amount nofilter}

                    {assign var="qty" value="qty_`$obj_id`"}
                    {$smarty.capture.$qty nofilter}

                    {assign var="min_qty" value="min_qty_`$obj_id`"}
                    {$smarty.capture.$min_qty nofilter}
                </div>
            </div>
            {if $capture_options_vs_qty}{/capture}{/if}                   

            {assign var="product_edp" value="product_edp_`$obj_id`"}
            {$smarty.capture.$product_edp nofilter}

            {if $show_descr}
            {assign var="prod_descr" value="prod_descr_`$obj_id`"}
            <h2 class="description-title">{__("description")}</h2>
            <p class="product-description">{$smarty.capture.$prod_descr nofilter}</p>
            {/if}

            {if $capture_buttons}{capture name="buttons"}{/if}
                <div class="buttons-container">
                    
                    {if $show_details_button}
                        {include file="buttons/button.tpl" but_href="products.view?product_id=`$product.product_id`" but_text=__("view_details") but_role="submit"}
                    {/if}
                    
                    {assign var="add_to_cart" value="add_to_cart_`$obj_id`"}
                    {$smarty.capture.$add_to_cart nofilter}

                    {assign var="list_buttons" value="list_buttons_`$obj_id`"}
                    {$smarty.capture.$list_buttons nofilter}

                </div>
            {if $capture_buttons}{/capture}{/if}

            {assign var="form_close" value="form_close_`$obj_id`"}
            {$smarty.capture.$form_close nofilter}

            {if $show_product_tabs}
            {include file="views/tabs/components/product_popup_tabs.tpl"}
            {$smarty.capture.popupsbox_content nofilter}
            {/if}
        </div>
    {/if}
    
{/hook}
</div>

{if $smarty.capture.hide_form_changed == "Y"}
    {assign var="hide_form" value=$smarty.capture.orig_val_hide_form}
{/if}

{if $show_product_tabs}

{include file="views/tabs/components/product_tabs.tpl"}

{if $blocks.$tabs_block_id.properties.wrapper}
    {include file=$blocks.$tabs_block_id.properties.wrapper content=$smarty.capture.tabsbox_content title=$blocks.$tabs_block_id.description}
{else}
    {$smarty.capture.tabsbox_content nofilter}
{/if}

{/if}
</div>

<div class="product-details">
</div>

{capture name="mainbox_title"}{assign var="details_page" value=true}{/capture}
