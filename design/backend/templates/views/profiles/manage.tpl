{if "MULTIVENDOR"|fn_allowed_for}
    {assign var="no_hide_input" value="cm-no-hide-input"}
{/if}

{include file="views/profiles/components/profiles_scripts.tpl"}

{capture name="mainbox"}

{assign var="c_icon" value="<i class=\"exicon-`$search.sort_order_rev`\"></i>"}
{assign var="c_dummy" value="<i class=\"exicon-dummy\"></i>"}

<form action="{""|fn_url}" method="post" name="userlist_form" id="userlist_form" class="{if $runtime.company_id && !"ULTIMATE"|fn_allowed_for}cm-hide-inputs{/if}">
<input type="hidden" name="fake" value="1" />
<input type="hidden" name="user_type" value="{$smarty.request.user_type}" />

{include file="common/pagination.tpl" save_current_page=true save_current_url=true div_id=$smarty.request.content_id}

{assign var="c_url" value=$config.current_url|fn_query_remove:"sort_by":"sort_order"}

{assign var="rev" value=$smarty.request.content_id|default:"pagination_contents"}

{if $users}
<table width="100%" class="table table-middle">
<thead>
<tr>
    <th width="1%" class="center {$no_hide_input}">
        {include file="common/check_items.tpl"}</th>
    <th width="3%" class="nowrap"><a class="cm-ajax" href="{"`$c_url`&sort_by=id&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("id")}{if $search.sort_by == "id"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
    {if $settings.General.use_email_as_login != "Y"}
    <th width="18%"><a class="cm-ajax" href="{"`$c_url`&sort_by=username&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("username")}{if $search.sort_by == "username"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
    {/if}
    <th width="18%"><a class="cm-ajax" href="{"`$c_url`&sort_by=name&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("person_name")}{if $search.sort_by == "name"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
    <th width="20%"><a class="cm-ajax" href="{"`$c_url`&sort_by=email&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("email")}{if $search.sort_by == "email"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
    <th width="16%"><a class="cm-ajax" href="{"`$c_url`&sort_by=date&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("registered")}{if $search.sort_by == "date"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
    <th><a class="cm-ajax" href="{"`$c_url`&sort_by=type&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("type")}{if $search.sort_by == "type"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
    {hook name="profiles:manage_header"}{/hook}
    <th class="right">&nbsp;</th>
    <th width="10%" class="right"><a class="cm-ajax" href="{"`$c_url`&sort_by=status&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("status")}{if $search.sort_by == "status"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>

</tr>
</thead>
{foreach from=$users item=user}

{assign var="allow_save" value=$user|fn_allow_save_object:"users"}

{if !$allow_save && !"RESTRICTED_ADMIN"|defined && $auth.is_root != 'Y'}
    {assign var="link_text" value=__("view")}
    {assign var="popup_additional_class" value=""}
{elseif $allow_save || "RESTRICTED_ADMIN"|defined || $auth.is_root == 'Y'}
    {assign var="link_text" value=""}
    {assign var="popup_additional_class" value="cm-no-hide-input"}
{else}
    {assign var="popup_additional_class" value=""}
    {assign var="link_text" value=""}
{/if}
{if !"ULTIMATE"|fn_allowed_for}
    <tr class="cm-row-status-{$user.status|lower}">
{/if}

{if "ULTIMATE"|fn_allowed_for}
    <tr class="cm-row-status-{$user.status|lower}{if !$allow_save || ($user.user_id == $smarty.session.auth.user_id)} cm-hide-inputs{/if}">
{/if}
    <td class="center {$no_hide_input}">
        <input type="checkbox" name="user_ids[]" value="{$user.user_id}" class="checkbox cm-item" /></td>
    <td><a class="row-status" href="{"profiles.update?user_id=`$user.user_id`&user_type=`$user.user_type`"|fn_url}">{$user.user_id}</a></td>
    {if $settings.General.use_email_as_login != "Y"}
    <td><a class="row-status" href="{"profiles.update?user_id=`$user.user_id`&user_type=`$user.user_type`"|fn_url}">{$user.user_login}</a></td>
    {/if}
    <td class="row-status">{if $user.firstname || $user.lastname}<a href="{"profiles.update?user_id=`$user.user_id`&user_type=`$user.user_type`"|fn_url}">{$user.lastname} {$user.firstname}</a>{else}-{/if}{if $user.company_id}{include file="views/companies/components/company_name.tpl" object=$user}{/if}</td>
    <td><a class="row-status" href="mailto:{$user.email|escape:url}">{$user.email}</a></td>
    <td class="row-status">{$user.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}</td>
    <td class="row-status">{if $user.user_type == "A"}{__("administrator")}{elseif $user.user_type == "V"}{__("vendor_administrator")}{elseif $user.user_type == "C"}{__("customer")}{elseif $user.user_type == "P"}{__("affiliate")}{/if}</td>
    {hook name="profiles:manage_data"}{/hook}
    <td class="right nowrap">
        {capture name="tools_list"}
            {$list_extra_links = false}
            {hook name="profiles:list_extra_links"}
                {if $user.user_type == "C"}
                    <li>{btn type="list" text=__("view_all_orders") href="orders.manage?user_id=`$user.user_id`"}</li>
                    {$list_extra_links = true}
                {/if}
                {if $user.user_type|fn_user_need_login && (!$runtime.company_id || $runtime.company_id == $auth.company_id && fn_check_permission_act_as_user()) && $user.user_id != $auth.user_id && !($user.user_type == $auth.user_type && $user.is_root == 'Y' && (!$user.company_id || $user.company_id == $auth.company_id))}
                    <li>{btn type="list" target="_blank" text=__("act_on_behalf") href="profiles.act_as_user?user_id=`$user.user_id`"}</li>
                    {$list_extra_links = true}
                {/if}
                {assign var="return_current_url" value=$config.current_url|escape:url}
            {/hook}
            {if $list_extra_links}
                <li class="divider"></li>
            {/if}

            {if $smarty.request.user_type}
                {assign var="user_edit_link" value="profiles.update?user_id=`$user.user_id`&user_type=`$smarty.request.user_type`"}
            {else}
                {assign var="user_edit_link" value="profiles.update?user_id=`$user.user_id`&user_type=`$user.user_type`"}
            {/if}
            <li>{btn type="list" text=__("edit") href=$user_edit_link}</li>

            {capture name="tools_delete"}
                <li>{btn type="list" text=__("delete") class="cm-confirm" href="profiles.delete?user_id=`$user.user_id`&redirect_url=`$return_current_url`"}</li>
            {/capture}
            {if $user.user_id != $smarty.session.auth.user_id}
                {if !$runtime.company_id && !($user.user_type == "A" && $user.is_root == "Y")}
                    {$smarty.capture.tools_delete nofilter}
                {elseif $allow_save}
                    {if "MULTIVENDOR"|fn_allowed_for && $user.user_type == "V" && $user.is_root == "N"}
                        {$smarty.capture.tools_delete nofilter}
                    {/if}

                    {if "ULTIMATE"|fn_allowed_for}
                        {$smarty.capture.tools_delete nofilter}
                    {/if}
                {/if}
            {/if}
        {/capture}
        <div class="hidden-tools">
            {dropdown content=$smarty.capture.tools_list}
        </div>
    </td>
    <td class="right">
        <input type="hidden" name="user_types[{$user.user_id}]" value="{$user.user_type}" />
        {if $user.is_root == "Y" && ($user.user_type == "A" || $user.user_type == "V" && $runtime.company_id && $runtime.company_id == $user.company_id)}
            {assign var="u_id" value=""}           
        {else}
            {assign var="u_id" value=$user.user_id}
        {/if}

        {assign var="non_editable" value=false}

        {if $user.is_root == "Y" && $user.user_type == $auth.user_type && (!$user.company_id || $user.company_id == $auth.company_id) || $user.user_id == $auth.user_id || ("MULTIVENDOR"|fn_allowed_for && $runtime.company_id && ($user.user_type == 'C' || $user.company_id && $user.company_id != $runtime.company_id))}
            {assign var="non_editable" value=true}
        {/if}

        {include file="common/select_popup.tpl" id=$u_id status=$user.status hidden="" update_controller="profiles" notify=true notify_text=__("notify_user") popup_additional_class="`$popup_additional_class` dropleft" non_editable=$non_editable}
    </td>
</tr>
{/foreach}
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}

{include file="common/pagination.tpl" div_id=$smarty.request.content_id}

{capture name="buttons"}
    {if $users}
        {capture name="tools_list"}
            {if "ULTIMATE"|fn_allowed_for || !$runtime.company_id}
                {hook name="profiles:list_tools"}
                    <li>{btn type="list" text=__("export_selected") dispatch="dispatch[profiles.export_range]" form="userlist_form"}</li>
                {/hook}
            {/if}
            <li>{btn type="delete_selected" dispatch="dispatch[profiles.m_delete]" form="userlist_form"}</li>
        {/capture}
        {dropdown content=$smarty.capture.tools_list}
    {/if}
{/capture}
</form>
{/capture}

{capture name="adv_buttons"}
    {if $smarty.request.user_type}
        {assign var="_title" value=$smarty.request.user_type|fn_get_user_type_description:true}
    {else}
        {assign var="_title" value=__("users")}
    {/if}

    {if $smarty.request.user_type}
        {if !($runtime.company_id && "MULTIVENDOR"|fn_allowed_for && ($smarty.request.user_type == 'C' || $auth.is_root != 'Y'))}
            <a class="btn cm-tooltip" href="{"profiles.add?user_type=`$smarty.request.user_type`"|fn_url}" title="{__("add_user")}"><i class="icon-plus"></i></a>
        {/if}
    {else}
        {if !empty($user_types)}
            {capture name="tools_list"}
                {foreach from=$user_types key="_k" item="_p"}
                    {if !($runtime.company_id && "MULTIVENDOR"|fn_allowed_for && ($smarty.request.user_type == 'C' || $auth.is_root != 'Y'))}
                        <li><a href="{"profiles.add?user_type=`$_k`"|fn_url}">{__($_p)}</a></li>
                    {/if}
                {/foreach}
            {/capture}
            {dropdown content=$smarty.capture.tools_list no_caret=true icon="icon-plus" placement="right"}
        {/if}
    {/if}
{/capture}

{capture name="sidebar"}
    {include file="common/saved_search.tpl" dispatch="profiles.manage" view_type="users"}
    {include file="views/profiles/components/users_search_form.tpl" dispatch="profiles.manage"}
{/capture}

{include file="common/mainbox.tpl" title=$_title content=$smarty.capture.mainbox sidebar=$smarty.capture.sidebar adv_buttons=$smarty.capture.adv_buttons buttons=$smarty.capture.buttons content_id="manage_users"}