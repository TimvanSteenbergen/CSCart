<div class="control-group setting-wide">
    <label for="text" class="control-label">{__(api_key)}<a class="cm-tooltip" title="<a href='https://developers.google.com/maps/documentation/javascript/tutorial?#api_key'>{__(get_api_key)}</a>"><i class="icon-question-sign"></i></a>:</label>
    <div class="controls">
        <input type="text" name="sl_settings[google_key]" id="text" value="{$sl_settings.google_key}" size="40" class="input-text-large">
    </div>
</div>
<div class="control-group setting-wide">
    <label for="text" class="control-label">{__(zoom)}:</label>
    <div class="controls">
        <input type="text" name="sl_settings[google_zoom]" id="text" value="{$sl_settings.google_zoom}" size="40" class="input-text-large">
    </div>
</div>
<div class="control-group setting-wide">
    <label for="text" class="control-label">{__(zoom_control)}:</label>
    <div class="controls">
        <input type="checkbox" name="sl_settings[google_zoom_control]" id="google_zoom_control"{if $sl_settings.google_zoom_control == 'Y'} checked= "true"{/if} value="Y">
    </div>
</div>
<div class="control-group setting-wide">
    <label for="text" class="control-label">{__(map_type_control)}:</label>
    <div class="controls">
        <input type="checkbox" name="sl_settings[google_map_type_control]" id="google_map_type_control"{if $sl_settings.google_map_type_control == 'Y'} checked= "true"{/if} value="Y">
    </div>
</div>
<div class="control-group setting-wide">
    <label for="text" class="control-label">{__(scale_control)}:</label>
    <div class="controls">
        <input type="checkbox" name="sl_settings[google_scale_control]" id="google_scale_control"{if $sl_settings.google_scale_control == 'Y'} checked= "true"{/if} value="Y">
    </div>
</div>
