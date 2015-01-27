{if "ULTIMATE"|fn_allowed_for && !$runtime.company_id || "MULTIVENDOR"|fn_allowed_for && $runtime.company_id}
	{$readonly = true}
{else}
	{$readonly = false}
{/if}

{include file="addons/suppliers/views/suppliers/components/supplier_field.tpl" title=__("supplier") name="product_data[supplier_id]" id="product_data_supplier_id" selected=$product_data.supplier_id company_id=$product_data.company_id read_only=$readonly}