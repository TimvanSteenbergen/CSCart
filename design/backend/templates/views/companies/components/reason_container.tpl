<div class="form-horizontal form-edit">
<div class="control-group">
    <label class="control-label">{__("reason")}</label>
    <div class="controls">
    <textarea class="span9" name="action_reason_{$type}" id="action_reason_{$type}" cols="50" rows="4"></textarea>
    </div>
</div>

<div class="cm-toggle-button">
    <div class="control-group notify-customer">
        <div class="controls">
        <label for="action_notification" class="checkbox">
        <input type="hidden" name="action_notification" value="N" />
        <input type="checkbox" name="action_notification" id="action_notification" value="Y" checked="checked" {if $mandatory_notification}disabled="disabled"{/if} />
        {__("notify_vendors_by_email")}</label>
        </div>
    </div>
</div>
</div>