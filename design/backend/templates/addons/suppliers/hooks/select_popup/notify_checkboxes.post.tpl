{if $order_info.have_suppliers}
	<li><a><label for="{$prefix}_{$id}_notify_supplier">
        <input type="checkbox" name="__notify_supplier" id="{$prefix}_{$id}_notify_supplier" value="Y" checked="checked" onclick="Tygh.$('input[name=__notify_supplier]').prop('checked', this.checked);" />
        {__("notify_supplier")}</label></a>
    </li>
{/if}