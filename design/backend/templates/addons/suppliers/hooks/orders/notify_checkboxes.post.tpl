{$have_supplier=false}
{foreach from=$order_info.products item="item"}
	{if $item.extra.supplier_id}
		{$have_supplier=true}
	{/if}
{/foreach}
{if $have_supplier}
    <label for="notify_supplier" class="checkbox">
        <input type="checkbox" name="notify_supplier" id="notify_supplier" value="Y" />
        {__("notify_supplier")}
    </label>
{/if}