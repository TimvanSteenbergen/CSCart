{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="states_form" class="{if $runtime.company_id} cm-hide-inputs{/if}">
<input type="hidden" name="country_code" value="{$search.country}" />

{include file="common/pagination.tpl" save_current_page=true save_current_url=true}

{if $states}
<table width="100%" class="table table-middle">
<thead>
<tr>
    <th width="1%">{include file="common/check_items.tpl"}</th>
    <th width="10%">{__("code")}</th>
    <th width="60%">{__("state")}</th>
    <th width="5%">&nbsp;</th>
    <th class="right" width="10%">{__("status")}</th>
</tr>
</thead>
{foreach from=$states item=state}
<tr class="cm-row-status-{$state.status|lower}">
    <td>
        <input type="checkbox" name="state_ids[]" value="{$state.state_id}" class="checkbox cm-item" /></td>
    <td class="left nowrap row-status">
        <span>{$state.code}</span>
        {*<input type="text" name="states[{$state.state_id}][code]" size="8" value="{$state.code}" class="input-text" />*}</td>
    <td>
        <input type="text" name="states[{$state.state_id}][state]" size="55" value="{$state.state}" class="input-hidden span8"/></td>
    <td class="nowrap">
        {capture name="tools_list"}
            <li>{btn type="list" class="cm-confirm" text=__("delete") href="states.delete?state_id=`$state.state_id`&country_code=`$search.country`"}</li>
        {/capture}
        <div class="hidden-tools">
            {dropdown content=$smarty.capture.tools_list}
        </div>
    </td>
    <td class="right">
        {$has_permission = fn_check_permissions("tools", "update_status", "admin", "GET", ["table" => "states"])}
        {include file="common/select_popup.tpl" id=$state.state_id status=$state.status hidden="" object_id_name="state_id" table="states" non_editable=!$has_permission}
    </td>
</tr>
{/foreach}
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}

{include file="common/pagination.tpl"}

</form>

{capture name="tools"}
    {capture name="add_new_picker"}

    <form action="{""|fn_url}" method="post" name="add_states_form" class="form-horizontal form-edit">
    <input type="hidden" name="state_data[country_code]" value="{$search.country_code}" />
    <input type="hidden" name="country_code" value="{$search.country_code}" />
    <input type="hidden" name="state_id" value="0" />

    {foreach from=$countries item="country" key="code"}
        {if $code == $search.country_code}
            {assign var="title" value="{__("new_states")} (`$country`)"}
        {/if}
    {/foreach}

    <div class="cm-j-tabs">
        <ul class="nav nav-tabs">
            <li id="tab_new_states" class="cm-js active"><a>{__("general")}</a></li>
        </ul>
    </div>

    <div class="cm-tabs-content">
    <fieldset>
        <div class="control-group">
            <label class="cm-required control-label" for="elm_state_code">{__("code")}:</label>
            <div class="controls">
            <input type="text" id="elm_state_code" name="state_data[code]" size="8" value="" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_state_name">{__("state")}:</label>
            <div class="controls">
            <input type="text" id="elm_state_name" name="state_data[state]" size="55" value="" />
            </div>
        </div>

        {include file="common/select_status.tpl" input_name="state_data[status]" id="elm_state_status"}
    </fieldset>
    </div>

    <div class="buttons-container">
        {include file="buttons/save_cancel.tpl" create=true but_name="dispatch[states.update]" cancel_action="close"}
    </div>

</form>

{/capture}
{/capture}

{capture name="buttons"}
    {capture name="tools_list"}
        {hook name="states:manage_tools_list"}
            {if $states}
                <li>{btn type="delete_selected" dispatch="dispatch[states.m_delete]" form="states_form"}</li>
            {/if}
        {/hook}
    {/capture}
    {dropdown content=$smarty.capture.tools_list}

    {if $states}
        {include file="buttons/save.tpl" but_name="dispatch[states.m_update]" but_role="submit-link" but_target_form="states_form"}
    {/if}
{/capture}

{capture name="adv_buttons"}
    {include file="common/popupbox.tpl" id="new_state" action="states.add" text=$title content=$smarty.capture.add_new_picker title=__("add_state") act="general" icon="icon-plus"}
{/capture}

{capture name="sidebar"}
<div class="sidebar-row">
<h6>{__("search")}</h6>
<form action="{""|fn_url}" name="states_filter_form" method="get">
<div class="sidebar-field">
    <label>{__("country")}:</label>
        <select name="country_code">
            {foreach from=$countries item="country" key="code"}
                <option {if $code == $search.country_code}selected="selected"{/if} value="{$code}">{$country}</option>
            {/foreach}
        </select>
</div>
    {include file="buttons/search.tpl" but_name="dispatch[states.manage]"}
</form>
</div>
{/capture}


{/capture}
{include file="common/mainbox.tpl" title=__("states") content=$smarty.capture.mainbox adv_buttons=$smarty.capture.adv_buttons buttons=$smarty.capture.buttons sidebar=$smarty.capture.sidebar select_languages=true}