{if $addons.suppliers.display_supplier == "Y" && $product.supplier_id}
    <div class="ty-control-group{if !$capture_options_vs_qty} product-list-field{/if}">
        <label class="ty-control-group__label">{__("supplier")}:</label>
        <span class="ty-control-group__item"><a href="{"suppliers.view?supplier_id=`$product.supplier_id`"|fn_url}">{$product.supplier_id|fn_get_supplier_name}</a></span>
    </div>
{/if}