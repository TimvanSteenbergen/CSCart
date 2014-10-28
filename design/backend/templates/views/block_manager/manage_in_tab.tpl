<div id="content_blocks">
    {if $layouts|count > 1}
    <div class="form-horizontal form-edit">
        <div class="control-group">
            <label class="control-label">{__("switch_layout")}</label>
            <div class="controls">
                {include file="common/select_object.tpl" style="graphic" link_tpl=$config.current_url|fn_link_attach:"s_layout=" items=$layouts selected_id=$runtime.layout.layout_id key_name="name" display_icons=false target_id="content_blocks"}
            </div>
        </div>
    </div>
    {/if}
    {include file="views/block_manager/manage.tpl"}
</div>