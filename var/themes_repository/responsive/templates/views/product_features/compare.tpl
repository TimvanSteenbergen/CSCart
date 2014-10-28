{if !$comparison_data}
    <p class="ty-no-items ty-compare__no-items">{__("no_products_selected")}</p>
    <div class="buttons-container ty-compare__button-empty">
        {include file="buttons/continue_shopping.tpl" but_href=$continue_url|fn_url but_role="text"}
    </div>
{else}
    {script src="js/tygh/exceptions.js"}
    {assign var="return_current_url" value=$config.current_url|escape:url}
    <div class="ty-compare">
        <div class="ty-compare__wrapper">
            <table class="ty-compare-products">
                <tr>
                    <td class="ty-compare-products__menu">
                        <ul class="ty-compare-menu">
                            <li class="ty-compare-menu__item">{if $action != "show_all"}<a class="ty-compare-menu__a" href="{"product_features.compare.show_all"|fn_url}">{__("all_features")}</a>{else}<span class="ty-compare-menu__elem">{__("all_features")}</span>{/if}</li>
                            <li class="ty-compare-menu__item">{if $action != "similar_only"}<a class="ty-compare-menu__a" href="{"product_features.compare.similar_only"|fn_url}">{__("similar_only")}</a>{else}<span class="ty-compare-menu__elem">{__("similar_only")}</span>{/if}</li>
                            <li class="ty-compare-menu__item">{if $action != "different_only"}<a class="ty-compare-menu__a" href="{"product_features.compare.different_only"|fn_url}">{__("different_only")}</a>{else}<span class="ty-compare-menu__elem">{__("different_only")}</span>{/if}</li>
                        </ul>
                    </td>
                    {foreach from=$comparison_data.products item=product}
                    <td class="ty-compare-products__product">
                        <div class="ty-compare-products__item">
                            <div class="ty-compare-products__delete"><a href="{"product_features.delete_product?product_id=`$product.product_id`&redirect_url=`$return_current_url`"|fn_url}" class="ty-remove"  title="{__("remove")}"><i class="ty-remove__icon ty-icon-cancel-circle"></i><span class="ty-remove__txt">{__("remove")}</span></a></div>
                            <div class="ty-compare-products__img"><a href="{"products.view?product_id=`$product.product_id`"|fn_url}">{include file="common/image.tpl" image_width=$settings.Thumbnails.product_lists_thumbnail_width obj_id=$product.product_id images=$product.main_pair no_ids=true}</a></div>
                        </div>

                        <div class="ty-compare-products__item">
                            <a href="{"products.view?product_id=`$product.product_id`"|fn_url}">{$product.product nofilter}</a>
                        </div>

                        {assign var="obj_id" value=$product.product_id}
                        {include file="common/product_data.tpl" product=$product show_old_price=true show_price_values=true show_price=true show_clean_price=true}
                        <div class="ty-compare-products__item">
                            {assign var="old_price" value="old_price_`$obj_id`"}
                            {if $smarty.capture.$old_price|trim}{$smarty.capture.$old_price nofilter}{/if}

                            {assign var="price" value="price_`$obj_id`"}
                            {$smarty.capture.$price nofilter}

                            {assign var="clean_price" value="clean_price_`$obj_id`"}
                            {$smarty.capture.$clean_price nofilter}
                        </div>

                        <div class="ty-compare-products__item">{include file="blocks/list_templates/simple_list.tpl" min_qty=true product=$product show_add_to_cart=true but_role="action" hide_price=true}</div>
                    </td>
                    {/foreach}
                </tr>
            </table>
    
            <div class="ty-compare-feature">
                <table class="ty-compare-feature__table">
                {foreach from=$comparison_data.product_features item="group_features" key="group_id" name="feature_groups"}
                    {foreach from=$group_features item="_feature" key=id name="product_features"}
                        <tr class="ty-compare-feature__row">
                            <td class="ty-compare-feature__item ty-compare-sort">
                                <strong class="ty-compare-sort__title">{$_feature}:</strong>
                                    <a href="{"product_features.delete_feature?feature_id=`$id`&redirect_url=`$return_current_url`"|fn_url}" class="ty-compare-sort__a ty-icon-cancel-circle" title="{__("remove")}"></a>
                            </td>
                            {foreach from=$comparison_data.products item=product}
                                <td class="ty-compare-feature__item ty-compare-feature_item_size">

                                {if $product.product_features.$id}
                                    {assign var="feature" value=$product.product_features.$id}
                                {else}
                                    {assign var="feature" value=$product.product_features[$group_id].subfeatures.$id}
                                {/if}

                                {strip}
                                    {if $feature.prefix}{$feature.prefix}{/if}
                                    {if $feature.feature_type == "C"}
                                        <span class="ty-compare-checkbox" title="{$feature.value}">{if $feature.value == "Y"}<i class="ty-compare-checkbox__icon ty-icon-ok"></i>{/if}</span>
                                    {elseif $feature.feature_type == "D"}
                                        {$feature.value_int|date_format:"`$settings.Appearance.date_format`"}
                                    {elseif $feature.feature_type == "M" && $feature.variants}
                                        <ul class="ty-compare-list">
                                        {foreach from=$feature.variants item="var"}
                                        {if $var.selected}
                                        <li class="ty-compare-list__item"><span class="ty-compare-checkbox" title="{$var.variant}"><i class="ty-compare-checkbox__icon ty-icon-ok"></i></span>{$var.variant}</li>
                                        {/if}
                                        {/foreach}
                                        </ul>
                                    {elseif $feature.feature_type == "S" || $feature.feature_type == "E"}
                                        {foreach from=$feature.variants item="var"}
                                            {if $var.selected}{$var.variant}{/if}
                                        {/foreach}
                                    {elseif $feature.feature_type == "N" || $feature.feature_type == "O"}
                                        {$feature.value_int|floatval|default:"-"}
                                    {else}
                                        {$feature.value|default:"-"}
                                    {/if}
                                    {if $feature.suffix}{$feature.suffix}{/if}
                                {/strip}
                            {/foreach}
                        </tr>
                    {/foreach}
                {/foreach}
                </table>
            </div>
        </div>
    </div>

    <div class="buttons-container ty-compare__buttons">
        {assign var="r_url" value=""|fn_url}
        {include file="buttons/continue_shopping.tpl" but_href=$continue_url|fn_url but_role="text"}
        {include file="buttons/button.tpl" but_text=__("clear_list") but_href="product_features.clear_list?redirect_url=`$r_url`" but_meta="ty-btn__tertiary"}
    </div>

    {if $comparison_data.hidden_features}
    {include file="common/subheader.tpl" title=__("add_feature")}
    <form action="{""|fn_url}" method="post" name="add_feature_form">
        <input type="hidden" name="redirect_url" value="{$config.current_url}" />
        {html_checkboxes name="add_features" options=$comparison_data.hidden_features columns="4"}
        <div class="buttons-container ty-mt-s">
            {include file="buttons/button.tpl" but_text=__("add") but_name="dispatch[product_features.add_feature]"}
        </div>
    </form>
    {/if}
{/if}

{capture name="mainbox_title"}{__("compare")}{/capture}