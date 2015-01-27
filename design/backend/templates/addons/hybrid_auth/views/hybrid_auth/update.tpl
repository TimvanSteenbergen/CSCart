<div id="content_group_{$id}">

    <form action="{""|fn_url}" method="post" name="provider_form" class="form-horizontal form-edit">
        <input type="hidden" name="provider_data[provider_id]" value="{$id}" />

        <div class="tabs cm-j-tabs">
            <ul class="nav nav-tabs">
                <li id="tab_general_{$id}" class="cm-js active"><a>{__("general")}</a></li>
            </ul>
        </div>

        <div class="cm-tabs-content" id="tabs_content_{$id}">
            <div id="content_tab_general_{$id}">

                <div class="control-group">
                    <label for="section_provider_{$id}" class="control-label cm-required">{__("provider")}:</label>
                    <div class="controls">
                        <select name="provider_data[provider]" id="provider" class="cm-select-provider">
                            {foreach from=$available_providers item="provider_code"}
                            <option value="{$provider_code}"{if $provider_code == $provider_data.provider} selected="selected"{/if} data-id="{$id}" data-provider="{$provider_code}">{$providers_schema.$provider_code.provider}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>

                {include file="addons/hybrid_auth/views/hybrid_auth/provider_keys.tpl" provider=$provider}
                {include file="addons/hybrid_auth/views/hybrid_auth/provider_params.tpl" provider=$provider}
                {include file="common/select_status.tpl" input_name="provider_data[status]" id="provider_status" obj=$section}
            </div>
        </div>

        <div class="buttons-container">
            {include file="buttons/save_cancel.tpl" but_name="dispatch[hybrid_auth.update_provider]" cancel_action="close" save=$id}
        </div>

    </form>
<!--content_group_{$id}--></div>
