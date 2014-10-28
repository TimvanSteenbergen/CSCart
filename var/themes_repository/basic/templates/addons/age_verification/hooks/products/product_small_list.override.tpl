{if !$smarty.session.auth.age && $product.age_verification == "Y"}
<table class="table-width">
<tr>
    <td style="width: {$cell_width}%" class="valign-top">
        <a href="{"products.view?product_id=`$product.product_id`"|fn_url}">{$product.product nofilter}</a>
        <div class="box margin-top">
            {__("product_need_age_verification")}
            <div class="buttons-container">
                {include file="buttons/button.tpl" but_text=__("verify") but_href="products.view?product_id=`$product.product_id`" but_role="text"}
            </div>
        </div>
    </td>
</tr>
</table>
{/if}