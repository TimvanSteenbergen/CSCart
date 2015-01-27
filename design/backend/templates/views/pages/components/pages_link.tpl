<div class="control-group">
    <label for="page_link" class="control-label cm-required">{__("page_link")}:</label>
    <div class="controls">
        <input type="text" name="page_data[link]" id="page_link" size="55" value="{$page_data.link}" class="input-large" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="page_link_new_window">{__("open_in_new_window")}:</label>
    <div class="controls">
    <input type="hidden" name="page_data[new_window]" value="0" />
    <input {if $page_data.new_window != "0"}checked="checked"{/if} type="checkbox" name="page_data[new_window]" id="page_link_new_window" size="55" value="1" />
    </div>
</div>