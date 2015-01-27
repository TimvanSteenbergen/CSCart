<div id="content_suppliers" class="hidden">
    <div class="control-group">
        <label for="suppiers_group" class="control-label">{__("available_for_supplier")}</label>

        <div class="controls">
            <div id="suppiers_group">
                <label for="supplier_none" class="checkbox">
                    <input type="hidden" name="shipping_data[suppliers][0]" value="N">
                    <input type="checkbox" class="cm-combo-checkbox" id="supplier_none" name="shipping_data[suppliers][0]" value="Y" {if 0|in_array:$linked_suppliers}checked="checked"{/if}>
                    {__("none")}
                </label>
            </div>
            {foreach $suppliers as $supplier}
                <div>
                    <label for="supplier_{$supplier.supplier_id}" class="checkbox">
                        <input type="hidden" name="shipping_data[suppliers][{$supplier.supplier_id}]" value="N">
                        <input type="checkbox" class="cm-combo-checkbox" id="supplier_{$supplier.supplier_id}" name="shipping_data[suppliers][{$supplier.supplier_id}]" value="Y" {if $supplier.supplier_id|in_array:$linked_suppliers}checked="checked"{/if}>
                        {$supplier.name}
                    </label>
                </div>
            {/foreach}
        </div>
    </div>
</div>