{script src="js/tygh/exceptions.js"}

{assign var="obj_id" value=$product.view_id|default:$product.product_id}
{assign var="obj_prefix" value="ajax"}
<div class="product-main-info product-quick-view long" id="product_main_info_{$obj_prefix}">
{hook name="products:view_main_info"}

{if $product}

<div class="quick-view-tools">
{include file="common/view_tools.tpl" quick_view=true}
</div>
    
{include file="common/product_data.tpl" obj_prefix=$obj_prefix obj_id=$obj_id product=$product but_role="big" but_text=__("add_to_cart") form_meta="cm-form-dialog-closer"}

{assign var="form_open" value="form_open_`$obj_id`"}
{$smarty.capture.$form_open nofilter}
<div class="clearfix">
    <div class ="left-side">
        {hook name="products:quick_view_image_wrap"}
            {if !$no_images}
                <div class="image-border cm-reload-{$obj_prefix}{$obj_id}" id="product_images_{$product.product_id}_update">

                    {assign var="discount_label" value="discount_label_`$obj_prefix``$obj_id`"}
                    {$smarty.capture.$discount_label nofilter}

                    {include file="views/products/components/product_images.tpl" product=$product show_detailed_link=true image_width=$settings.Thumbnails.product_quick_view_thumbnail_width image_height=$settings.Thumbnails.product_quick_view_thumbnail_height}
                <!--product_images_{$product.product_id}_update--></div>
            {/if}
        {/hook}
    </div>

    <div class="product-info">

            {hook name="products:quick_view_title"}
            {if !$hide_title}<h1><a href="{"products.view?product_id=`$product.product_id`"|fn_url}" class="quick-view-title">{$product.product nofilter}</a></h1>{/if}

            <div class="brand-wrapper">
                {include file="views/products/components/product_features_short_list.tpl" features=$product.header_features}
            </div>
            {/hook}

            <hr class="indented" />

            {assign var="prod_descr" value="prod_descr_`$obj_id`"}
            {if $smarty.capture.$prod_descr|trim}
                <div class="product-description indented">{$smarty.capture.$prod_descr nofilter}</div>
            {/if}

            <div class="product-note">
                {$product.promo_text nofilter}
            </div>

            <div class="{if $smarty.capture.$old_price|trim || $smarty.capture.$clean_price|trim || $smarty.capture.$list_discount|trim}prices-container {/if}price-wrap clearfix">
                {assign var="old_price" value="old_price_`$obj_id`"}
                {assign var="price" value="price_`$obj_id`"}
                {assign var="clean_price" value="clean_price_`$obj_id`"}
                {assign var="list_discount" value="list_discount_`$obj_id`"}
                {assign var="discount_label" value="discount_label_`$obj_id`"}

                 <div class="{if $smarty.capture.$old_price|trim || $smarty.capture.$clean_price|trim || $smarty.capture.$list_discount|trim}prices-container {/if}price-wrap clearfix">{strip}
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

                {/strip}</div>

                {if $show_discount_label && $smarty.capture.$discount_label|trim}
                    <div class="float-left">
                        {$smarty.capture.$discount_label nofilter}
                    </div>
                {/if}

            </div>
            
            {if $capture_options_vs_qty}{capture name="product_options"}{$smarty.capture.product_options nofilter}{/if}
                <div class="options-wrapper indented">
                    {assign var="product_options" value="product_options_`$obj_id`"}
                    {$smarty.capture.$product_options nofilter}
                </div>
            {if $capture_options_vs_qty}{/capture}{/if}
                <div class="indented">
            {if $capture_options_vs_qty}{capture name="product_options"}{$smarty.capture.product_options nofilter}{/if}
                {assign var="advanced_options" value="advanced_options_`$obj_id`"}
                {$smarty.capture.$advanced_options nofilter}
            {if $capture_options_vs_qty}{/capture}{/if}
                    {$sku = "sku_`$obj_id`"}
                    {$smarty.capture.$sku nofilter}
                </div>

            {assign var="product_amount" value="product_amount_`$obj_id`"}
            {$smarty.capture.$product_amount nofilter}

            {assign var="qty" value="qty_`$obj_id`"}
            {$smarty.capture.$qty nofilter}
        
            {assign var="min_qty" value="min_qty_`$obj_id`"}
            {$smarty.capture.$min_qty nofilter}
            
            {assign var="product_edp" value="product_edp_`$obj_id`"}
            {$smarty.capture.$product_edp nofilter}

            {if $capture_buttons}{capture name="buttons"}{/if}
                    
                    {assign var="add_to_cart" value="add_to_cart_`$obj_id`"}
                    {$smarty.capture.$add_to_cart nofilter}

                    {assign var="list_buttons" value="list_buttons_`$obj_id`"}
                    {$smarty.capture.$list_buttons nofilter}

                
            {if $capture_buttons}{/capture}{/if}
    </div>
</div>
{assign var="form_close" value="form_close_`$obj_id`"}
{$smarty.capture.$form_close nofilter}

{/if}
    
{if $smarty.capture.hide_form_changed == "Y"}
    {assign var="hide_form" value=$smarty.capture.orig_val_hide_form}
{/if}

{/hook}
<!--product_main_info_{$obj_prefix}--></div>

<div class="product-details">
</div>

<!-- {capture name="mainbox_title"}{assign var="details_page" value=true}{/capture} -->