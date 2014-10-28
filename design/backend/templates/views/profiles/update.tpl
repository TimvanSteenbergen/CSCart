{if $user_data}
    {assign var="id" value=$user_data.user_id}
{else}
    {assign var="id" value=0}
{/if}

{include file="views/profiles/components/profiles_scripts.tpl"}

<form name="profile_form" action="{""|fn_url}" method="post" class="form-horizontal form-edit form-table {if ($runtime.company_id && $id && $user_data.company_id != $runtime.company_id && $id != $auth.user_id) || $hide_inputs} cm-hide-inputs{/if}">
{capture name="mainbox"}

{capture name="tabsbox"}
    {$hide_inputs=false}

    {if $user_data.user_type == $auth.user_type && $user_data.is_root == 'Y' && $user_data.user_id != $auth.user_id && (!$user_data.company_id || $user_data.company_id == $auth.company_id)}
        {$hide_inputs=true}
    {/if}

    {if "ULTIMATE"|fn_allowed_for && !$user_data|fn_allow_save_object:"users" && $id && !$id|fn_ult_check_users_usergroup_companies && $user_data.user_id != $auth.user_id}
        {$hide_inputs=true}
    {/if}

    {if "MULTIVENDOR"|fn_allowed_for && (!$user_data|fn_allow_save_object:"users" || $runtime.company_id && ($smarty.request.user_type == 'C' || $user_data.company_id|fn_string_not_empty && $user_data.company_id != $runtime.company_id)) && $user_data.user_id != $auth.user_id}
        {$hide_inputs=true}
    {/if}

    <input type="hidden" name="user_id" value="{$id}" />
    <input type="hidden" class="cm-no-hide-input" name="selected_section" id="selected_section" value="{$selected_section}" />
    <input type="hidden" class="cm-no-hide-input" name="user_type" value="{$smarty.request.user_type}" />
    
    <div id="content_general">
        {hook name="profiles:general_content"}
            {include file="views/profiles/components/profiles_account.tpl"}

            {if ("ULTIMATE"|fn_allowed_for || $user_type == "V") && $id != $auth.user_id}

                {$zero_company_id_name_lang_var = false}
                {if "ULTIMATE"|fn_allowed_for && $user_type|fn_check_user_type_admin_area}
                    {$zero_company_id_name_lang_var = 'all_vendors'}
                {/if}

                {include file="views/companies/components/company_field.tpl"
                    name="user_data[company_id]"
                    id="user_data_company_id"
                    selected=$user_data.company_id
                    zero_company_id_name_lang_var=$zero_company_id_name_lang_var
                    disable_company_picker=$hide_inputs
                }

            {else}
                <input type="hidden" name="user_data[company_id]" value="{$user_data.company_id|default:0}">
            {/if}
        {/hook}
        
        {include file="views/profiles/components/profile_fields.tpl" section="C" title=__("contact_information")}

        {if $settings.General.user_multiple_profiles == "Y" && $id}
            {include file="common/subheader.tpl" title=__("user_profile_info")}
            <p class="form-note">{__("text_multiprofile_notice")}</p>
            {include file="views/profiles/components/multiple_profiles.tpl"}
        {/if}

        {if $profile_fields.B}
            {include file="views/profiles/components/profile_fields.tpl" section="B" title=__("billing_address")}
            {include file="views/profiles/components/profile_fields.tpl" section="S" title=__("shipping_address") body_id="sa" shipping_flag=$profile_fields|fn_compare_shipping_billing}
        {else}
            {include file="views/profiles/components/profile_fields.tpl" section="S" title=__("shipping_address") shipping_flag=false}
        {/if}
        </div>
    {if !"ULTIMATE:FREE"|fn_allowed_for}
        {if $id && (((!$user_data|fn_check_user_type_admin_area || !$user_data.user_id) && !$runtime.company_id) || ($user_data|fn_check_user_type_admin_area && $user_data.user_id && $usergroups && !$runtime.company_id && $auth.is_root == 'Y' && ($user_data.company_id != 0 || ($user_data.company_id == 0 && $user_data.is_root != 'Y'))) || ($user_data.user_type == 'V' && $runtime.company_id && $auth.is_root == 'Y' && $user_data.user_id != $auth.user_id && $user_data.company_id == $runtime.company_id))}

            <div id="content_usergroups" class="cm-hide-save-button">
                {if $usergroups}
                <table width="100%" class="table table-middle">
                <thead>
                <tr>
                    <th width="50%">{__("usergroup")}</th>
                    <th class="right" width="10%">{__("status")}</th>
                </tr>
                </thead>
                {foreach from=$usergroups item=usergroup}
                    <tr>
                        <td><a href="{"usergroups.manage#group`$usergroup.usergroup_id`"|fn_url}">{$usergroup.usergroup}</a></td>
                        <td class="right">
                            {if $user_data.usergroups[$usergroup.usergroup_id]}
                                {assign var="ug_status" value=$user_data.usergroups[$usergroup.usergroup_id].status}
                            {else}
                                {assign var="ug_status" value="F"}
                            {/if}
                            {include file="common/select_popup.tpl" id=$usergroup.usergroup_id status=$ug_status hidden="" items_status="profiles"|fn_get_predefined_statuses extra="&user_id=`$id`" update_controller="usergroups" notify=true hide_for_vendor=$runtime.company_id}
                        </td>
                    </tr>
                {/foreach}
                </table>
                {else}
                    <p class="no-items">{__("no_data")}</p>
                {/if}
            </div>
        {/if}
    {/if}

    <div id="content_addons">
        {hook name="profiles:detailed_content"}
        {/hook}
    </div>
    {if $show_api_tab}
        <div id="content_api">
            <div class="control-group {if $hide_api_checkbox}hidden{/if}">
                <div class="controls">
                    <label class="checkbox" for="sw_api_container">
                    <input {if $user_data.api_key != ""}checked="checked"{/if} class="cm-combination" type="checkbox" name="user_api_status" value="Y" id="sw_api_container" />{__("allow_api_access")}</label>
                </div>
            </div>

            <div id="api_container" {if $user_data.api_key == ""}class="hidden"{/if}>
                <div class="control-group">
                    <label class="control-label">{__("api_key")}</label>
                    <div class="controls">
                        <input type="text" class="input-large" name="user_data[api_key]" value="{if $user_data.api_key}{$user_data.api_key}{else}{$new_api_key}{/if}" readonly="readonly"/>
                    </div>
                </div>
            </div>
        </div>
    {/if}

    {hook name="profiles:tabs_content"}
    {/hook}
    {if !$user_data|fn_allow_save_object:"users" && $id && $user_data.user_id != $auth.user_id || $hide_inputs}
        {assign var="hide_first_button" value=true}
    {/if}

    {if $id}
        {hook name="profiles:tabs_extra"}
        {/hook}
    {/if}
{/capture}

{include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox group_name=$runtime.controller active_tab=$selected_section track=true}

{/capture}

{if !$id}
    {assign var="_user_desc" value=$user_type|fn_get_user_type_description}
    {assign var="_title" value="{__("new_profile")} (`$_user_desc`)"}
{else}
    {if $user_data.firstname}
        {assign var="_title" value="{__("editing_profile")}: `$user_data.firstname` `$user_data.lastname`"}
        {elseif $user_data.b_firstname}
        {assign var="_title" value="{__("editing_profile")}: `$user_data.b_firstname` `$user_data.b_lastname`"}
        {else}
        {assign var="_title" value="{__("editing_profile")}: `$user_data.email`"}
    {/if}
{/if}

{$_title = $_title|strip_tags}

{capture name="buttons"}
    {capture name="tools_list"}
        {hook name="profiles:update_tools_list"}
        {if $user_data.user_type == "C"}
            <li>{btn type="list" text=__("view_all_orders") href="orders.manage?user_id=`$id`"}</li>
        {/if}
        {if $user_data.user_type|fn_user_need_login && (!$runtime.company_id || $runtime.company_id == $auth.company_id) && $user_data.user_id != $auth.user_id && !($user_data.user_type == 'A' && $user_data.is_root == 'Y' && !$user_data.company_id)}
            <li>{btn type="list" target="_blank" text=__("act_on_behalf") href="profiles.act_as_user?user_id=`$id`"}</li>
        {/if}
        {/hook}
    {/capture}
    {if $id && $smarty.capture.tools_list|trim !==""}
        {dropdown content=$smarty.capture.tools_list}
    {/if}
<div class="btn-group btn-hover dropleft">
    {if $id}
        {include file="buttons/save_changes.tpl" but_meta="dropdown-toggle" but_role="submit-link" but_name="dispatch[profiles.`$runtime.mode`]" but_target_form="profile_form" save=$id}
    {else}
        {include file="buttons/button.tpl" but_text=__("create") but_meta="dropdown-toggle" but_role="submit-link" but_name="dispatch[profiles.`$runtime.mode`]" but_target_form="profile_form" save=$id}
    {/if}
    <ul class="dropdown-menu">
        <li><a><input type="checkbox" name="notify_customer" value="Y" checked="checked"  id="notify_customer" />
            {__("notify_user")}</a></li>
    </ul>
</div>

{/capture}

{include file="common/mainbox.tpl" title=$_title content=$smarty.capture.mainbox buttons=$smarty.capture.buttons}
</form>