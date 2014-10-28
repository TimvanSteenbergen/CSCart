{if $settings.General.user_multiple_profiles == "Y"}

<div class="control-group" id="profiles_list">
    <label class="control-label">{__("select_profile")}</label>
    <div class="controls">
    {foreach from=$user_profiles item="up" name="pfe"}
        {if $up.profile_id == $user_data.profile_id}
            <span>{$up.profile_name}</span>
        {else}
            <a href="{$config.current_url|fn_link_attach:"profile_id=`$up.profile_id`"|fn_url}#profiles_list">{$up.profile_name}</a>
        {/if}

        {if $up.profile_type != "P"}
            {include file="buttons/button.tpl" but_meta="cm-confirm" but_icon="icon-trash" but_href="profiles.delete_profile?user_id=`$user_data.user_id`&profile_id=`$up.profile_id`" but_role="delete_item"}
        {/if}

        {if !$smarty.foreach.pfe.last}&nbsp;|&nbsp;{/if}
    {/foreach}
    {if !$skip_create}
        &nbsp;&nbsp;{__("or")}&nbsp;&nbsp;&nbsp;<a class="lowercase" href="{$config.current_url|fn_query_remove:"profile_id"|fn_link_attach:"profile=new"|fn_url}#profiles_list">{__("create_profile")}</a>
    {/if}
    </div>
</div>

<div class="control-group">
    <label for="profile_name" class="control-label cm-required">{__("profile_name")}</label>
    <div class="controls">
    {if $smarty.request.profile == "new"}
        {assign var="profile_name" value=__("new")}
    {else}
        {assign var="profile_name" value=__("main")}
    {/if}
    <input type="hidden" id="profile_id" name="user_data[profile_id]" value="{$user_data.profile_id|default:"0"}" />
    <input type="text" id="profile_name" name="user_data[profile_name]" size="32" value="{$user_data.profile_name|default:$profile_name}" class="input-text" />
    </div>
</div>
{else}
<div>
    <input type="hidden" id="profile_name" name="user_data[profile_name]" value="{$user_data.profile_name|default:__("main")}" />
</div>
{/if}