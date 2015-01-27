{if $addons.suppliers.display_supplier == "Y" && $product.supplier_id}
    <div class="control-group{if !$capture_options_vs_qty} product-list-field{/if}">
        <label>{__("supplier")}:</label>
        <span><a href="{"suppliers.view?supplier_id=`$product.supplier_id`"|fn_url}">{$product.supplier_id|fn_get_supplier_name}</a></span>
    </div>
{/if}