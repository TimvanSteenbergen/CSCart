<tr {if !$clone}id="{$root_id}_{$delete_id}" {/if}class="cm-js-item{if $clone} cm-clone hidden{/if}">
<td>
    <ul>
        <li>{$product}
            {if !$view_only}
                <a href="javascript: Tygh.$.cePicker('delete_js_item', '{$root_id}', '{$delete_id}', 'p');" class="ty-delete-big" title="{__("remove")}"><i class="ty-delete-big__icon ty-icon-cancel-circle"></i></a>
            {/if}
        </li>
        {if $options}
        <li>{$options}</li>
        {/if}
    </ul>
    {if $options_array|is_array}
        {foreach from=$options_array item="option" key="option_id"}
        <input type="hidden" name="{$input_name}[product_options][{$option_id}]" value="{$option}"{if $clone} disabled="disabled"{/if} />
        {/foreach}
    {/if}
    {if $product_id}
        <input type="hidden" name="{$input_name}[product_id]" value="{$product_id}"{if $clone} disabled="disabled"{/if} />
    {/if}
    {if $amount_input == "hidden"}
    <input type="hidden" name="{$input_name}[amount]" value="{$amount}"{if $clone} disabled="disabled"{/if} />
    {/if}
</td>
    {if $amount_input == "text"}
<td class="ty-center">
    <input type="text" name="{$input_name}[amount]" value="{$amount}" size="3" class="short"{if $clone} disabled="disabled"{/if} />
</td>
    {/if}
</tr>
