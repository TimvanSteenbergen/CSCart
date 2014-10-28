{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="countries_form" class="{if ""|fn_check_form_permissions} cm-hide-inputs{/if}">

{include file="common/pagination.tpl" save_current_page=true save_current_url=true}
<table width="100%" class="table table-middle">
<thead>
<tr>
    <th class="left">{__("code")}</th>
    <th class="center">{__("code")}&nbsp;A3</th>
    <th class="center">{__("code")}&nbsp;N3</th>
    <th>{__("country")}</th>
    <th class="center">{__("region")}</th>
    <th class="right" width="10%">{__("status")}</th>
</tr>
</thead>
{foreach from=$countries item=country}
<tr class="cm-row-status-{$country.status|lower}">
{*    <td>
        <input type="checkbox" name="delete[{$country.code}]" id="delete_checkbox" value="Y" class="checkbox cm-item" /></td>
*}
    <td class="center row-status">
        {* <input type="text" name="country_data[{$country.code}][code]" size="2" value="{$country.code}" class="input-small input-hidden" />*}{$country.code}</td>
    <td class="center row-status">
        {*<input type="text" name="country_data[{$country.code}][code_A3]" size="3" value="{$country.code_A3}" class="input-small input-hidden" />*}{$country.code_A3}</td>
    <td class="center row-status">
        {*<input type="text" name="country_data[{$country.code}][code_N3]" size="5" value="{$country.code_N3}" class="input-small input-hidden" />*}{$country.code_N3}</td>
    <td> 
        <input type="text" name="country_data[{$country.code}][country]" size="55" value="{$country.country}" class="span4 input-hidden" /></td>
    <td class="center row-status">
        {*<input type="text" name="country_data[{$country.code}][region]" size="3" value="{$country.region}" class="input-medium input-hidden" />*}{$country.region}</td>
    <td class="right">
        {$has_permission = fn_check_permissions("tools", "update_status", "admin", "GET", ["table" => "countries"])}
        {include file="common/select_popup.tpl" id=$country.code status=$country.status hidden="" object_id_name="code" table="countries" non_editable=!$has_permission}
    </td>
</tr>
{/foreach}
</table>
{include file="common/pagination.tpl"}

</form>

{capture name="buttons"}
{include file="buttons/save.tpl" but_name="dispatch[countries.m_update]" but_role="submit-link" but_target_form="countries_form"}

{* Deletion of existent countries functionality is disabled by default *}
    {*capture name="tools_list"}
        <li><a data-ca-dispatch="dispatch[countries.delete]" class="cm-process-items cm-submit cm-confirm" data-ca-target-form="countries_form">{__("delete_selected")}</a></li>
    {/capture}
{*include file="common/tools.tpl" prefix="main" hide_actions=true tools_list=$smarty.capture.tools_list display="inline" link_text=__("choose_action")*}
{/capture}
 {* Add new country functionality is disabled by default *}

{/capture}
{include file="common/mainbox.tpl" title=__("countries") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons select_languages=true}