{include file="addons/twigmo/settings/components/contact_twigmo_support.tpl"}

{if $admin_access_id}
    {include file="common/subheader.tpl" title=__("twgadmin_manage_settings")}
{else}
    {include file="common/subheader.tpl" title=__("twgadmin_connect_your_store")}
{/if}

<fieldset>

<div id="connect_settings">

    <input type="hidden" name="result_ids" value="connect_settings,storefront_settings,addon_upgrade"/>

    {assign var="tw_email" value=$tw_settings.email|default:$user_info.email}

    <div class="control-group">
        <label {if !$admin_access_id}class="cm-required cm-email"{/if} for="elm_tw_email">{__("email")}:</label>
        <div class="controls">
            {if $admin_access_id}
                <div class="twg-text-value">{$tw_email}</div>
            {else}
                <input type="text" id="elm_tw_email" name="tw_register[email]" value="{$tw_email}" class="input-text-large" size="60" />
            {/if}
        </div>
    </div>

    {if !$admin_access_id}
        <div class="control-group">
            <label for="elm_tw_password" class="cm-required">{__("password")}:</label>
            <div class="controls">
                <input type="password" id="elm_tw_password" name="tw_register[password]" class="input-text-large" size="32" maxlength="32" value="" autocomplete="off" />
                {include file="buttons/button.tpl" but_text=__("forgot_password_question") but_href=$reset_pass_link but_id="elm_reset_tw_password" but_role="link" but_target="_blank"}
            </div>
        </div>
    {/if}

    <div class="control-group">
        <label {if !$twg_all_stores_connected}class="cm-required cm-multiple-checkboxes"{/if} for="stores_list">{__("twgadmin_stores")}:</label>
        <div class="controls">
            <table class="table twg-stores" cellpadding="0" cellspacing="0" id="stores_list">
                <tr>
                    {if !$twg_all_stores_connected}
                        <th>
                            <input type="checkbox" class="checkbox cm-check-items" id="check_all_twg_stores" name="check_all" checked="checked" title="{__("check_uncheck_all")}">
                        </th>
                    {/if}
                    <th>
                        {__("store")}
                    </th>
                    {if $twg_is_connected}
                        <th>
                            {__("twgadmin_access_id")}
                        </th>
                        {if !$is_on_saas}
                            <th>
                                {__("plan")}
                            </th>
                        {/if}
                    {/if}
                    <th>
                        {__("status")}
                    </th>
                    {if $is_disconnect_mode}
                        <th>
                            {__("twgadmin_disconnect")}
                        </th>
                    {/if}
                </tr>
                {foreach from=$stores item='store'}
                    <tr class="table-row">
                        {if !$twg_all_stores_connected}
                            <td>
                                {if !$store.is_connected}
                                    <input type="checkbox" id="store_{$store.company_id}" checked="checked" class="checkbox cm-item cm-required form-checkbox" name="tw_register[stores][]" value="{$store.company_id}">
                                {/if}
                            </td>
                        {/if}
                        <td title="{$store.clear_url}">
                            {$store.company}
                        </td>
                        {if $twg_is_connected}
                            <td>
                                {$store.access_id}
                            </td>
                            {if !$is_on_saas}
                                <td>
                                    {$store.plan_display_name|escape:false}
                                </td>
                            {/if}
                        {/if}
                        <td>
                            {if $store.is_connected}
                                <span class="twg-connected">{__("twgadmin_connected")}</span>
                            {else}
                                <span class="twg-disconnected">{__("twgadmin_disconnected")}</span>
                            {/if}
                        </td>
                        {if $is_disconnect_mode}
                            <td>
                                {if $store.is_connected}
                                    <input type="checkbox" class="checkbox" name="disconnect_stores[]" value="{$store.company_id}">
                                {/if}
                            </td>
                        {/if}
                    </tr>
                {/foreach}
            </table>
        </div>
    </div>

    {if !$admin_access_id}
        {include file="addons/twigmo/settings/components/connect/license.tpl"}
    {/if}


    {if !$twg_all_stores_connected}
        <div class="control-group">
            <div class="controls">
                {include file="buttons/button.tpl" but_role="submit" but_meta="btn-primary cm-skip-avail-switch" but_name="dispatch[addons.tw_connect]" but_text=__("twgadmin_connect") but_target_id="connect_settings"}
            </div>
        </div>
    {/if}

    {if $is_disconnect_mode}
        <div class="control-group">
            <div class="controls">
                <label for="elm_tw_disconnect_admin">{__("twgadmin_disconnect_whole")}:</label>
                <input type="hidden" name="disconnect_admin" value="N" />
                <input type="checkbox" class="checkbox" id="elm_tw_disconnect_admin" name="disconnect_admin" value="Y" />
            </div>
        </div>

        <div class="control-group">
            <div class="controls">
                {include file="buttons/button.tpl" but_role="submit" but_meta="cm-confirm cm-skip-avail-switch" but_name="dispatch[addons.tw_disconnect]" but_text=__("twgadmin_disconnect") but_target_id="connect_settings"}
            </div>
        </div>
    {/if}


    {if $admin_access_id}
        <div class="control-group">
            <div class="controls">
                {include file="buttons/button.tpl" but_role="submit" but_meta="btn-primary cm-new-window" but_name="dispatch[addons.tw_svc_auth_cp]" but_text=__("twgadmin_open_cp")}
            </div>
        </div>
    {/if}

    {include file="common/subheader.tpl" title=__("twgadmin_about")}

    <div class="control-group">
        <label for="version">{__("twgadmin_addon_version")}:</label>
        <div class="controls">
            <div class="twg-text-value" id="version">{$tw_settings.version|default:$smarty.const.TWIGMO_VERSION}</div>
        </div>
    </div>

    <div class="control-group">
        <label for="social_links">{__("twgadmin_on_social")}:</label>
        <div id="social_links" class="controls">
            <a target="_blank" href="//facebook.com/twigmo">
                <span class="facebook-btn"><img src="{$images_dir}/addons/twigmo/images/buttons/facebook.png"></span>
            </a>
            <a target="_blank" href="//twitter.com/twigmo">
                <span class="twitter-btn"><img src="{$images_dir}/addons/twigmo/images/buttons/twitter.png"></span>
            </a>
        </div>
    </div>

    <script type="text/javascript">
    //<![CDATA[
    var twg_is_connected = {if $twg_is_connected}true{else}false{/if};
    {literal}
        $(document).ready(function () {
            $('#twigmo_storefront').toggle(twg_is_connected);
            var $store_checkboxes = $('#stores_list input.form-checkbox');
            var $select_all_checkbox = $('#check_all_twg_stores');
            $store_checkboxes.on('click', function() {
                if (!$store_checkboxes.filter(':checked').size()) {
                    $select_all_checkbox.attr('checked', false);
                }
            });
        });
    {/literal}
    //]]>
    </script>
<!--connect_settings--></div>

</fieldset>
