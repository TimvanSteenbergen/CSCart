<input type="hidden" name="selected_fields[object]" value="category" />

<table width="100%">
<tr valign="top">
    <td>
        <label class="checkbox" for="elm_status"><input type="hidden" value="status" name="selected_fields[data][]" />
        <input type="checkbox" value="status" name="selected_fields[data][]" id="elm_status" checked="checked" disabled="disabled" class="cm-item-s" />
        {__("status")}</label>
        
        <label class="checkbox" for="elm_meta_description"><input type="checkbox" value="meta_description" name="selected_fields[data][]" id="elm_meta_description" checked="checked" class="cm-item-s" />{__("meta_description")}</label>

        <label class="checkbox" for="elm_product_details_layout"><input type="checkbox" value="product_details_layout" name="selected_fields[data][]" id="elm_product_details_layout" checked="checked" class="cm-item-s" />{__("product_details_layout")}</label>

        <label for="elm_position" class="checkbox"><input type="checkbox" value="position" name="selected_fields[data][]" id="elm_position" checked="checked" class="cm-item-s" />{__("position")}</label>
    </td>
    <td>
        <label for="elm_name" class="checkbox"><input type="hidden" value="category" name="selected_fields[data][]" />
        <input type="checkbox" value="category" name="selected_fields[data][]" id="elm_category_name" checked="checked" disabled="disabled" class="cm-item-s" />
        {__("name")}</label>
        
        <label class="checkbox" for="elm_meta_keywords"><input type="checkbox" value="meta_keywords" name="selected_fields[data][]" id="elm_meta_keywords" checked="checked" class="checkbox cm-item-s" />{__("meta_keywords")}</label>

        <label for="elm_timestamp" class="checkbox"><input type="checkbox" value="timestamp" id="elm_timestamp" name="selected_fields[data][]" checked="checked" class="cm-item-s" />{__("creation_date")}</label>

        {if $config.tweaks.disable_localizations == false}
            <label class="checkbox" for="elm_localization"><input type="checkbox" id="elm_localization" value="localization" name="selected_fields[data][]" checked="checked" class="cm-item-s" />{__("localization")}</label>
        {/if}
    </td>
    <td>
        <label for="elm_description" class="checkbox"><input type="checkbox" value="description" name="selected_fields[data][]" id="elm_description" checked="checked" class="cm-item-s" />{__("category_description")}</label>

        <label class="checkbox" for="elm_image_pair"><input type="checkbox" value="image_pair" name="selected_fields[images][]" id="elm_image_pair" checked="checked" class="cm-item-s" />{__("image_pair")}</label>

        <label class="checkbox" for="elm_page_title"><input type="checkbox" value="page_title" id="elm_page_title" name="selected_fields[data][]" checked="checked" class="cm-item-s" />{__("title")}</label>

        {if !"ULTIMATE:FREE"|fn_allowed_for}
            <label class="checkbox" for="elm_usergroup_ids"><input type="checkbox" value="usergroup_ids" name="selected_fields[data][]" id="elm_usergroup_ids" checked="checked" class="cm-item-s" />{__("usergroups")}</label>
        {/if}
    </td>
    <td>
        {hook name="categories:fields_to_edit"}
        {/hook}
    </td>

</tr>
</table>
<p>
{include file="common/check_items.tpl" check_target="s" style="links"}
</p>