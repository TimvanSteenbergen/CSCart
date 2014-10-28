{if $status_data}
    {assign var="id" value=$status_data.status|lower}
{else}
    {assign var="id" value="0"}
{/if}

{if "ULTIMATE"|fn_allowed_for && !$runtime.company_id}
    {assign var="show_update_for_all" value=true}
{/if}

{if "ULTIMATE"|fn_allowed_for && $settings.Stores.default_state_update_for_all == 'not_active' && !$runtime.simple_ultimate && !$runtime.company_id}
    {assign var="disable_input" value=true}
{/if}

<div id="content_group{$st}">

<form action="{""|fn_url}" method="post" name="update_status_{$st}_form" class="form-horizontal">
<input type="hidden" name="type" value="{$type|default:"O"}">
<input type="hidden" name="status" value="{$status_data.status}">

<div class="tabs cm-j-tabs">
    <ul class="nav nav-tabs">
        <li class="cm-js active"><a>{__("general")}</a></li>
    </ul>
</div>

<div class="cm-tabs-content">
<fieldset>
    <div class="control-group{if $runtime.company_id} cm-hide-inputs{/if}">
        <label for="description_{$id}" class="cm-required control-label">{__("name")}:</label>
        <div class="controls">
            <input type="text" size="70" id="description_{$id}" name="status_data[description]" value="{$status_data.description}" class="input-large">
        </div>
    </div>

    {if $id}
        <div class="control-group">
            <label for="status_{$id}" class="cm-required control-label">{__("status")}:</label>            
                <div class="controls">
                    <input type="hidden" name="status_data[status]" value="{$status_data.status}">
                    <p class="shift-top">{$status_data.status}</p>
                </div>
        </div>
    {/if}

    <div class="control-group">
        <label for="email_subj_{$id}" class="control-label">{__("email_subject")}:</label>
        <div class="controls cm-no-hide-input">
            <input type="text" size="40" name="status_data[email_subj]" id="email_subj_{$id}" value="{$status_data.email_subj}" {if $disable_input}disabled="disabled"{/if}>
            {if "ULTIMATE"|fn_allowed_for}
            {include file="buttons/update_for_all.tpl" display=$show_update_for_all object_id="`$id`_email_subj" name="update_all_vendors[email_subj]" hide_element="email_subj_`$id`"}
            {/if}
        </div>
    </div>

    <div class="control-group">
        <label for="email_header_{$id}" class="control-label">{__("email_header")}:</label>
        <div class="controls cm-no-hide-input">
            <textarea id="email_header_{$id}" name="status_data[email_header]" class="cm-wysiwyg input-textarea-long" {if $disable_input}disabled="disabled"{/if}>{$status_data.email_header}</textarea>
            {if "ULTIMATE"|fn_allowed_for}
            {include file="buttons/update_for_all.tpl" display=$show_update_for_all object_id="`$id`_email_header" name="update_all_vendors[email_header]" hide_element="email_header_`$id`"}
            {/if}
        </div>
    </div>

    {foreach from=$status_params key="name" item="data"}
        <div class="control-group{if $runtime.company_id} cm-hide-inputs{/if}">
            <label for="status_param_{$id}_{$name}" class="control-label{if $data.type == "color"} cm-color{/if}">{__($data.label)}</label>

            <div class="controls">
                {if $id}
                    {assign var="param_value" value=$status_data.params.$name}
                {else}
                    {assign var="param_value" value=""}
                {/if}

                {if $data.not_default == true && $status_data.is_default === "Y"}
                    {assign var="lbl" value=$data.variants.$param_value}
                    <p class="shift-top">{__($lbl)}</p>
                
                {elseif $data.type == "select"}
                    <select id="status_param_{$id}_{$name}" name="status_data[params][{$name}]">
                        {foreach from=$data.variants key="v_name" item="v_data"}
                        <option value="{$v_name}" {if $param_value == $v_name}selected="selected"{/if}>{__($v_data)}</option>
                        {/foreach}
                    </select>
                
                {elseif $data.type == "checkbox"}
                    <input type="hidden" name="status_data[params][{$name}]" value="N">
                    <input type="checkbox" name="status_data[params][{$name}]" id="status_param_{$id}_{$name}" value="Y" {if ($status_data && $status_data.params.$name == "Y") || (!$status_data && $data.default_value == "Y")} checked="checked"{/if} class="checkbox">

                {elseif $data.type == "status"}
                    {include file="common/status.tpl" status=$param_value display="select" name="status_data[params][`$name`]" status_type=$data.status_type select_id="status_param_`$id`_`$name`"}

                {elseif $data.type == "color"}
                    {include file="common/colorpicker.tpl" cp_name="status_data[params][`$name`]" cp_id="status_param_`$id`_`$name`" cp_value=$param_value}
                {/if}

                {hook name="statuses:status_type"}{/hook}

            </div>
        </div>
    {/foreach}
</fieldset>
</div>


<div class="buttons-container">
    {include file="buttons/save_cancel.tpl" but_name="dispatch[statuses.update]" cancel_action="close" save=$id}
</div>

</form>
<!--content_group{$id}--></div>
