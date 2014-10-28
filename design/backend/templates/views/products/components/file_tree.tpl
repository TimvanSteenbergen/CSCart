{math equation="x*30+5" x=$level|default:"0" assign="shift"}
<tr class="multiple-table-row cm-row-status-{$product_file.status|lower} ">
    <td>
        <div style="padding-left: {$shift}px;">
            <a class="row-status cm-external-click{if $non_editable} no-underline{/if}" data-ca-external-click-id="opener_group{$id_prefix}{$id}">{$product_file.file_name}</a>
        </div></td>

    <td width="10%" class="right nowrap">
        <div class="pull-right hidden-tools">
            {capture name="items_tools"}
                {if $tool_items}
                    {$tool_items nofilter}
                {/if}

                {if !$non_editable}
                    <li>{include file="common/popupbox.tpl" id="group`$id_prefix``$id`" text="{__("editing_file")}: `$product_file.file_name`" act="edit" opener_ajax_class="cm-ajax"}</li>
                {/if}

                {if !$non_editable && !$skip_delete}
                    <li>{btn type="text" text=__("delete") href={"products.delete_file?file_id=`$product_file.file_id`&product_id=`$product_data.product_id`"|fn_url} class="cm-confirm cm-tooltip cm-ajax cm-ajax-force cm-ajax-full-render cm-delete-row" data=["data-ca-target-id" => "product_files_list"]}</li>
                {/if}
            {/capture}
            {dropdown content=$smarty.capture.items_tools}
        </div></td>

    <td width="15%">
        <div class="pull-right nowrap">
            {if $non_editable == true}
                {assign var="display" value="text"}
            {/if}
            
            {include file="common/select_popup.tpl" popup_additional_class="dropleft" id=$id status=$product_file.status hidden=$hidden object_id_name="file_id" table="product_files" hide_for_vendor=$hide_for_vendor display=$display non_editable=$non_editable}
        </div></td>
</tr>