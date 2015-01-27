{assign var="key" value="0"}

<table class="table">
<thead class="cm-first-sibling">
<tr>
    <th>{__("position_short")}</th>
    <th>{__("field_name")}</th>
    <th>{__("field_type")}</th>
    <th class="center">{__("active")}</th>
    <th>&nbsp;</th>
</tr>
</thead>

<tbody>
{if $datafeed_data.fields}
{foreach from=$datafeed_data.fields item="field" key="key"}
<tr class="cm-row-item">
    <td>
        <input type="text" name="datafeed_data[fields][{$key}][position]" value="{$field.position|default:"0"}" class="input-mini">
    </td>
    <td>
        <input type="text" name="datafeed_data[fields][{$key}][export_field_name]" value="{$field.export_field_name}" size="60">
    </td>
    <td>
        {if $export_fields}
            <select name="datafeed_data[fields][{$key}][field]">
                <optgroup label="{__("fields")}">
                {foreach from=$export_fields item="params" key="_field"}
                    <option {if $field.field == $_field}selected="selected"{/if} value="{$_field}">{$_field}</option>
                {/foreach}
                </optgroup>
                
                {if $feature_fields}
                    <optgroup label="{__("features")}">
                    {foreach from=$feature_fields item="params" key="_field"}
                        <option {if $field.field == $_field}selected="selected"{/if} value="{$_field}">{$_field}</option>
                    {/foreach}
                    </optgroup>
                {/if}
            </select>
        {/if}
    </td>

    <td class="center">
        <input type="hidden" name="datafeed_data[fields][{$key}][avail]" value="N" />
        <input type="checkbox" name="datafeed_data[fields][{$key}][avail]" value="Y" {if $field.avail == "Y"}checked="checked"{/if} /></td>

    <td>{include file="buttons/clone_delete.tpl" microformats="cm-delete-row" no_confirm=true}</td>
</tr>
{/foreach}
{/if}

{math equation="x + 1" x=$key assign="key"}

<tr id="box_add_datafeed_fields">
    <td>
        <input type="text" name="datafeed_data[fields][{$key}][position]" value="" class="input-mini">
    </td>
    <td>
        <input type="text" name="datafeed_data[fields][{$key}][export_field_name]" value="" size="60"></td>
    <td>
        {if $export_fields}
            <select name="datafeed_data[fields][{$key}][field]">
                <optgroup label="{__("fields")}">
                {foreach from=$export_fields item="params" key="_field"}
                    <option value="{$_field}">{$_field}</option>
                {/foreach}
                </optgroup>
                
                {if $feature_fields}
                    <optgroup label="{__("features")}">
                    {foreach from=$feature_fields item="params" key="_field"}
                        <option value="{$_field}">{$_field}</option>
                    {/foreach}
                    </optgroup>
                {/if}
            </select>
        {/if}
    </td>

    <td class="center">
        <input type="hidden" name="datafeed_data[fields][{$key}][avail]" value="N" />
        <input type="checkbox" name="datafeed_data[fields][{$key}][avail]" value="Y" checked="checked" />
    </td>

    <td>
        {include file="buttons/multiple_buttons.tpl" item_id="add_datafeed_fields"}
    </td>
</tr>
</tbody>
</table>