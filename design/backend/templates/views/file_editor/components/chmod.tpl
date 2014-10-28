<div id="file_editor_perms" class="te-permissions">
    {include file="common/subheader.tpl" title=__("owner") target="#acc_owner"}
    <div id="acc_owner" class="collapsed in">
        <div class="control-group checkbox-list">
            <div class="controls">
                <label><input id="o_read" type="checkbox" name="o_read" />{__("read")}</label>
                <label><input id="o_write" type="checkbox" name="o_write" />{__("write")}</label>
                <label><input id="o_exec" type="checkbox" name="o_exec" />{__("exec")}</label>
            </div>
        </div>
    </div>
    {include file="common/subheader.tpl" title=__("global") target="#acc_global"}
    <div id="acc_global" class="collapsed in">
        <div class="control-group checkbox-list">
            <div class="controls">
                <label><input id="g_read" type="checkbox" name="g_read" />{__("read")}</label>
                <label><input id="g_write" type="checkbox" name="g_write" />{__("write")}</label>
                <label><input id="g_exec" type="checkbox" name="g_exec" />{__("exec")}</label>
            </div>
        </div>
    </div>
    {include file="common/subheader.tpl" title=__("world") target="#acc_world"}
    <div id="acc_world" class="collapsed in">
        <div class="control-group checkbox-list">
            <div class="controls">
                <label><input id="w_read" type="checkbox" name="w_read" />{__("read")}</label>
                <label><input id="w_write" type="checkbox" name="w_write" />{__("write")}</label>
                <label><input id="w_exec" type="checkbox" name="w_exec" />{__("exec")}</label>
            </div>
        </div>
    </div>
    <hr />
    <label for="chmod_recursive" class="checkbox inline"><input id="chmod_recursive" type="checkbox" name="r" value="Y" />{__("recursively")}</label>
    <div class="buttons-container">
        {include file="buttons/save_cancel.tpl" but_type="button" but_meta="cm-dialog-closer cm-te-chmod cm-no-submit" cancel_action="close" save=true}
    </div>
</div>
