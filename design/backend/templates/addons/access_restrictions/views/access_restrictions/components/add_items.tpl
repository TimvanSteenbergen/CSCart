<form action="{""|fn_url}" method="post" name="update_rule_form" class="form-horizontal form-edit ">
<input type="hidden" name="selected_section" value="{$selected_section}" />
<input type="hidden" name="rule_data[section]" value="{$selected_section}" />

<div class="add-new-object-group">
    <div class="tabs cm-j-tabs">
        <ul class="nav nav-tabs">
            <li id="tab_add_rule_new" class="cm-js active"><a>{__("general")}</a></li>
        </ul>
    </div>

    <div class="cm-tabs-content" id="content_tab_add_rule_new">
    <fieldset>
        {if $object_name == "ip"}
            <div class="control-group">
                <label class="control-label cm-required" for="elm_ip_from">{__("ip_from")}</label>
                <div class="controls">
                    <input type="text" id="elm_ip_from" name="rule_data[range_from]" size="15"/>
                </div>
            </div>

            <div class="control-group">
                <label class="control-label cm-required" for="elm_ip_to">{__("ip_to")}</label>
                <div class="controls">
                    <input type="text" id="elm_ip_to" name="rule_data[range_to]" size="15" />
                </div>
            </div>
        {else}
            <div class="control-group">
                <label class="control-label cm-required" for="elm_value">{$object_name}</label>
                <div class="controls">
                <input type="text" id="elm_value" name="rule_data[value]" size="15" value="" onfocus="this.value = ''" />
                </div>
            </div>
        {/if}

        <div class="control-group">
            <label class="control-label" for="elm_reason">{__("reason")}</label>
            <div class="controls">
                <input type="text" id="elm_reason" name="rule_data[reason]" />
            </div>
        </div>

        {include file="common/select_status.tpl" input_name="rule_data[status]" id="elm_status"}
    </fieldset>
    </div>
</div>

<div class="buttons-container">
    {include file="buttons/save_cancel.tpl" but_name="dispatch[access_restrictions.update]" cancel_action="close"}
</div>

</form>
