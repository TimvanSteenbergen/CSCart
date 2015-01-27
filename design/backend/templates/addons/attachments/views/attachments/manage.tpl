{if "ULTIMATE"|fn_allowed_for}
    {if ($runtime.company_id && $product_data.shared_product == "Y" && $product_data.company_id != $runtime.company_id)}
        {assign var="hide_for_vendor" value=true}
        {assign var="skip_delete" value=true}
        {assign var="hide_inputs" value="cm-hide-inputs"}
        {assign var="edit_link_text" value=__("view")}
    {/if}
{/if}

<div class="items-container cm-sortable" data-ca-sortable-table="attachments" data-ca-sortable-id-name="attachment_id" id="attachments_list">

{if !$hide_for_vendor}
<div class="btn-toolbar clearfix">
    <div class="pull-right">
    {capture name="add_new_picker"}
        {include file="addons/attachments/views/attachments/update.tpl" attachment=[] object_id=$object_id object_type=$object_type}
    {/capture}
    {include file="common/popupbox.tpl" id="add_new_attachments_files" text=__("new_attachment") link_text=__("add_attachment") content=$smarty.capture.add_new_picker act="general" icon="icon-plus"}
    </div>
</div>
{/if}

{if $attachments}
    <table class="table table-middle table-objects">
    {foreach from=$attachments item="a"}
        {capture name="object_group"}
            {include file="addons/attachments/views/attachments/update.tpl" attachment=$a object_id=$object_id object_type=$object_type hide_inputs=$hide_inputs}
        {/capture}
        {include file="common/object_group.tpl" content=$smarty.capture.object_group id=$a.attachment_id text=$a.description status=$a.status object_id_name="attachment_id" table="attachments" href_delete="attachments.delete?attachment_id=`$a.attachment_id`&object_id=`$object_id`&object_type=`$object_type`" delete_target_id="attachments_list" header_text="{__("editing_attachment")}: `$a.description`" additional_class="cm-sortable-row cm-sortable-id-`$a.attachment_id`" id_prefix="_attachments_" prefix="attachments" hide_for_vendor=$hide_for_vendor skip_delete=$skip_delete no_table="true" link_text=$edit_link_text draggable=true}
    {/foreach}
    </table>
{else}
    <p>{__("no_data")}</p>
{/if}

<!--attachments_list--></div>