{if !$smarty.session.auth.age && $product.age_verification == "Y"}
    <tr class="valign {cycle values=',table-row'}">
        <td class="compact" colspan="{if $show_add_to_cart}5{else}4{/if}">
            <div class="product-description">
                {assign var="name" value="name_$obj_id"}{$smarty.capture.$name nofilter}
            </div>
            <div class="box margin-top">
                {__("product_need_age_verification")}
                <div class="buttons-container">
                    {include file="buttons/button.tpl" but_text=__("verify") but_href="products.view?product_id=`$product.product_id`" but_role="text"}
                </div>
            </div>
        </td>
    </tr>
{/if}