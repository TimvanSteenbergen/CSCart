{** block-description:tmpl_scroller **}

{if $block.properties.enable_quick_view == "Y"}
    {$quick_nav_ids = $items|fn_fields_from_multi_level:"product_id":"product_id"}
{/if}

{if $block.properties.hide_add_to_cart_button == "Y"}
        {assign var="_show_add_to_cart" value=false}
    {else}
        {assign var="_show_add_to_cart" value=true}
    {/if}
    {if $block.properties.show_price == "Y"}
        {assign var="_hide_price" value=false}
    {else}
        {assign var="_hide_price" value=true}
{/if}

{assign var="obj_prefix" value="`$block.block_id`000"}
    <div id="scroll_list_{$block.block_id}" class="owl-carousel">
        {foreach from=$items item="product" name="for_products"}
            {hook name="products:product_scroller_list"}
            <div class="jscroll-item"> 
                {assign var="obj_id" value="scr_`$block.block_id`000`$product.product_id`"}                
                <div class="center scroll-image">
                    {include file="common/image.tpl" assign="object_img" images=$product.main_pair image_width=$block.properties.thumbnail_width image_height=$block.properties.thumbnail_width no_ids=true}
                    <a href="{"products.view?product_id=`$product.product_id`"|fn_url}">{$object_img nofilter}</a>
                    {if $block.properties.enable_quick_view == "Y"}
                        {include file="views/products/components/quick_view_link.tpl" quick_nav_ids=$quick_nav_ids}
                    {/if}
                </div>
                <div class="center compact">
                    {strip}
                        {include file="blocks/list_templates/simple_list.tpl" product=$product show_trunc_name=true show_price=true show_add_to_cart=$_show_add_to_cart but_role="action" hide_price=$_hide_price hide_qty=true}
                    {/strip}
                </div>
            </div>
            {/hook}
        {/foreach}
    </div>

{include file="common/scroller_init.tpl"}