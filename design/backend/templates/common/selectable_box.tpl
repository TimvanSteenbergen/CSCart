<table width="100%">
    <tr>
        <td class="center">
            <h4 class="nobold">{__("selected_fields")}</h4>
        </td>
        <td></td>
        <td class="center">
            <h4 class="nobold">{__("available_fields")}</h4>
        </td>
    </tr>
</table>

<hr>

<table width="100%">
    <tr>
        <td width="48%">
            <p>
                <label for="left_{$id}" class="cm-all hidden"></label>
                <select class="input-full" id="left_{$id}" name="{$name}[]" multiple="multiple" size="10" {if $disable_input}disabled="disabled"{/if}>
                    {if $selected_fields|is_array}
                        {foreach from=$selected_fields item="active" key="field_id"}
                            <option value="{$field_id}">{$fields.$field_id}</option>
                        {/foreach}
                    {/if}
                </select>
            </p>
            <p>
                <a onclick="Tygh.$('#left_{$id}').swapOptions('up');" class="icon-chevron-up"></a>
                <a onclick="Tygh.$('#left_{$id}').swapOptions('down');" class="icon-chevron-down"></a>
            </p>
        </td>
        <td width="4%" class="center chevron-icons">
            <a onclick="Tygh.$('#right_{$id}').moveOptions('#left_{$id}');" class="icon-chevron-left"></a>
            <br>
            <a onclick="Tygh.$('#left_{$id}').moveOptions('#right_{$id}', {$ldelim}check_required: true, message: Tygh.tr('error_exim_layout_missed_fields'){$rdelim});" class="icon-chevron-right"></a>
        </td>
        <td width="48%" valign="top">
            <select class="input-full" name="right_{$id}" id="right_{$id}" multiple="multiple" size="10" {if $disable_input}disabled="disabled"{/if}>
                {foreach from=$fields item="field_name" key="field_id"}
                    {if !$selected_fields.$field_id}
                        <option value="{$field_id}">{$field_name}</option>
                    {/if}
                {/foreach}
            </select>
            <div class="controls">
                <div class="right update-for-all">
                    {include file="buttons/update_for_all.tpl" display=$item.update_for_all object_id=$item.object_id name="update_all_vendors[`$item.object_id`]" hide_element=$html_id}
                </div>
            </div>
        </td>
    </tr>
</table>