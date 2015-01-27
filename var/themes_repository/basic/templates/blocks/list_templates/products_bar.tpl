{if $products}    
    <div class="template-products-bar">
        <h3 class="title-block">{$title_block}</h3>
        {foreach from=$products item="product" name="products"}        
            {if $product}
                <div class="products-bar-item{if $smarty.foreach.products.last} last-item{/if}">
                    {assign var="obj_id" value=$product.product_id}
                    {assign var="obj_id_prefix" value="`$obj_prefix``$product.product_id`"}
                    {include file="common/product_data.tpl" product=$product}

                    {assign var="form_open" value="form_open_`$obj_id`"}
                    {$smarty.capture.$form_open nofilter}

                    <p>{assign var="name" value="name_$obj_id"}{$smarty.capture.$name nofilter}</p>

                    <a href="{"products.view?product_id=`$product.product_id`"|fn_url}">{include file="common/image.tpl" obj_id=$obj_id_prefix images=$product.main_pair image_width=140 image_height=140}</a>
                
                    <div class="price-wrap">
                        {assign var="old_price" value="old_price_`$obj_id`"}
                        {if $smarty.capture.$old_price|trim}{$smarty.capture.$old_price nofilter}&nbsp;{/if}

                        {assign var="price" value="price_`$obj_id`"}
                        {$smarty.capture.$price nofilter}

                        {assign var="clean_price" value="clean_price_`$obj_id`"}
                        {$smarty.capture.$clean_price nofilter}
                    </div>

                    {assign var="add_to_cart" value="add_to_cart_`$obj_id`"}
                    {$smarty.capture.$add_to_cart nofilter}

                    {assign var="form_close" value="form_close_`$obj_id`"}
                    {$smarty.capture.$form_close nofilter}
                </div>
        {/if}
        {/foreach}
    </div>
{/if}