
<form action="{""|fn_url}" method="post" class="form-horizontal form-edit " name="import_locations" enctype="multipart/form-data">

<div class="tabs cm-j-tabs">
    <ul class="nav nav-tabs">
        <li class="cm-js active"><a>{__("general")}</a></li>
    </ul>
</div>

<div class="cm-tabs-content">
    <div class="control-group">
        <label class="control-label">{__("select_file")}</label>
        <div class="controls">
            {include file="common/fileuploader.tpl" var_name="filename[0]"}
        </div>
    </div>

    <div class="control-group" id="create_new_or_update">
        <div class="controls">
            <label class="radio inline" for="import_style_create_{$location.location_id}">
            <input type="radio" id="import_style_create_{$location.location_id}" name="import_style" value="create" checked="checked" />
            {__("create_new_layout")}</label>

            <label class="radio inline" for="import_style_update_{$location.location_id}">
            <input type="radio" id="import_style_update_{$location.location_id}" name="import_style" value="update" />
            {__("update_current_layout")}</label>
        </div>
    </div>

    <div class="control-group hidden" id="update_adv_options">
        <div class="controls">
            <label class="checkbox" for="clean_up_export_{$location_id}">
            <input type="hidden" name="clean_up" value="N" />
            <input id="clean_up_export_{$location.location_id}" type="checkbox" name="clean_up" value="Y" />
            {__("clean_up_all_locations_on_import")}</label>
            <label class="checkbox" for="override_by_dispatch_{$location_id}">
            <input type="hidden" name="override_by_dispatch" value="N" />
            <input id="override_by_dispatch_{$location.location_id}" type="checkbox" name="override_by_dispatch" value="Y" checked="checked" />
            {__("override_by_dispatch")}</label>
        </div>
    </div>
</div>

<div class="buttons-container">
    {include file="buttons/save_cancel.tpl" but_text=__("import") but_name="dispatch[block_manager.import_layout]" cancel_action="close" but_meta="cm-dialog-closer"}
</div>

<script>
    $(function() {
      $('#create_new_or_update input[type=radio]').change(function() {
        if($(this).val() == 'update') {
            $('#update_adv_options').show();
        } else {
            $('#update_adv_options').hide();
        }
      });
    });
</script>

</form>