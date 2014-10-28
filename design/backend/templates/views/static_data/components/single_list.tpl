<div class="items-container cm-sortable" data-ca-sortable-table="static_data" data-ca-sortable-id-name="param_id" >
    {if $static_data}
    <table class="table table-middle table-objects table-striped">
        <tbody>
            {foreach from=$static_data item="s"}
                {if ""|fn_allow_save_object:"static_data":$section_data.skip_edition_checking}
                    {assign var="href_delete" value="static_data.delete?param_id=`$s.param_id`&section=$section"}
                {else}
                    {assign var="href_delete" value=""}
                {/if}
                {include file="common/object_group.tpl" id=$s.param_id text=$s.descr status=$s.status hidden=false href="static_data.update?param_id=`$s.param_id`&section=$section" object_id_name="param_id" table="static_data" href_delete=$href_delete delete_target_id="static_data_list" header_text="{__($section_data.edit_title)}: `$s.descr`" link_text="" additional_class="cm-sortable-row cm-sortable-id-`$s.param_id`" no_table=true draggable=true}
            {/foreach}
        </tbody>
    </table>
    {else}
        <p class="no-items">{__("no_data")}</p>
    {/if}
</div>