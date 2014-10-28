
<form action="{""|fn_url}" method="post" class="form-horizontal form-edit " name="export_locations">

<div class="tabs cm-j-tabs">
    <ul class="nav nav-tabs">
        <li class="cm-js active"><a>{__("general")}</a></li>
    </ul>
</div>

<div class="cm-tabs-content">

<div class="control-group cm-no-hide-input">
    <label for="locations_ids" class="control-label">{__("locations")}</label>
    <div class="controls">
        <div class="scroll-y">
            {foreach from=$locations key="location_id" item="location"}
                    <label for="location_export_{$location.location_id}" class="checkbox"><input id="location_export_{$location.location_id}" type="checkbox" name="location_ids[]" value="{$location.location_id}" checked="checked" class="cm-item" />
                    {$location.name}&nbsp;({$location.dispatch})</label>
            {/foreach}
        </div>
        {include file="common/check_items.tpl" style="links"}
    </div>
</div>

<div class="control-group">
    <label for="output" class="control-label">{__("output")}</label>
    <div class="controls">
    <select name="output" id="output">
        <option value="D">{__("direct_download")}</option>
        {if !$runtime.company_id}
            <option value="S">{__("server")}</option>
        {/if}
        <option value="C">{__("screen")}</option>
    </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="filename">{__("filename")}</label>
    <div class="controls">
        <input type="text" name="filename" id="filename" size="50" value="layouts_{$smarty.const.TIME|date_format:"%m%d%Y"}.xml" />
    </div>
</div>

</div>

<div class="buttons-container">
    {include file="buttons/save_cancel.tpl" but_text=__("export") but_name="dispatch[block_manager.export_layout]" cancel_action="close" but_meta="cm-dialog-closer"}
</div>
</form>