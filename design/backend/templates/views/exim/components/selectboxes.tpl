<p class="muted">{__("text_exim_export_notice")}</p>
<table width="100%">
    <tr>
        <td class="center">
            <label for="{$left_id}" class="cm-required cm-all multiply-select-header">{__("exported_fields")}</label></td>
        <td>&nbsp;</td>
        <td class="center">
           <label for="{$left_id}_right">{__("available_fields")}</label>
        </td>
    </tr>
</table>
<hr>
<table width="100%">
    <tr>
        <td width="48%">
            <select id="{$left_id}" name="{$left_name}[]" multiple="multiple" class="input-full toll-select">
            {foreach from=$assigned_ids item=key}
                {if $items.$key}
                    <option value="{$key}" selected="selected" {if $items.$key.required}class=" selectbox-highlighted cm-required"{/if}>{$key}</option>
                {/if}
            {/foreach}
            {foreach from=$items item="item" key="key"}
                {if $item.required && !$key|in_array:$assigned_ids}
                    <option value="{$key}" selected="selected"  class="selectbox-highlighted cm-required">{$key}</option>
                {/if}
            {/foreach}
            </select>
            <p class="left">
                <span class="icon-chevron-up hand" onclick="Tygh.$('#{$left_id}').swapOptions('up');"></span>
                <span class="icon-chevron-down hand" onclick="Tygh.$('#{$left_id}').swapOptions('down');"></span>
            </p>
        </td>
        <td width="4%" class="center chevron-icons">
                    <span class="icon-chevron-left hand" onclick="Tygh.$('#{$left_id}_right').moveOptions('#{$left_id}');"></span>
                    <br>
                    <span class="icon-chevron-right hand" onclick="Tygh.$('#{$left_id}').moveOptions('#{$left_id}_right', {$ldelim}check_required: true, message: Tygh.tr('error_exim_layout_missed_fields'){$rdelim});"></span>
        </td>
        <td width="48%" class="top">
            <select id="{$left_id}_right" name="unset_mbox[]" multiple="multiple" class="input-full toll-select">
            {foreach from=$items item=item key=key}
                {if !$key|in_array:$assigned_ids && !$item.required}
                    <option value="{$key}" {if $item.required}class="selectbox-highlighted cm-required"{/if}>{$key}</option>
                {/if}
            {/foreach}
            </select>
        </td>
    </tr>
</table>