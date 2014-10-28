{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="localizations_form"  class="cm-comet cm-ajax">
<input type="hidden" name="result_ids" value="localizations_table" />

<div id="localizations_table">

{if $localizations}
<table width="100%" class="table table-middle">
<thead>
<tr>
    <th width="1%">
        {include file="common/check_items.tpl"}</th>
    <th width="40%">{__("name")}</th>
    <th width="10%" class="center">{__("default")}</th>
    <th width="5%">&nbsp;</th>
    <th width="10%" class="right">{__("status")}</th>
</tr>
</thead>
{foreach from=$localizations item=localization}
<tr class="cm-row-status-{$localization.status|lower}">
    <td align="left">
        <input name="localization_ids[]" type="checkbox" class=" cm-item" value="{$localization.localization_id}" /></td>
    <td>
         <a href="{"localizations.update?localization_id=`$localization.localization_id`"|fn_url}">{$localization.localization}</a>
    </td>
    <td class="center">
        {if $localization.is_default == "Y"}
            {__("default")}
        {else}
            {__("no")}
        {/if}
    </td>
    <td class="nowrap right">
        {capture name="tools_list"}
            <li>{btn type="list" text=__("edit") href="localizations.update?localization_id=`$localization.localization_id`"}</li>
            <li>{btn type="text" text=__("delete") href="localizations.delete?localization_id=`$localization.localization_id`" class="cm-confirm cm-ajax cm-comet" data=['data-ca-target-id'=>'localizations_table']}</li>
        {/capture}
        <div class="hidden-tools">
            {dropdown content=$smarty.capture.tools_list}
        </div>
    </td>
    <td class="right">
        {include file="common/select_popup.tpl" id=$localization.localization_id status=$localization.status object_id_name="localization_id" table="localizations"}</td>
</tr>
{/foreach}
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}

<!--localizations_table--></div>
</form>

{capture name="adv_buttons"}
    {include file="common/tools.tpl" tool_href="localizations.add" prefix="top" title=__("add_localization") icon="icon-plus"}
{/capture}

{capture name="buttons"}
    {capture name="tools_list"}
        {if $localizations}
            <li>{btn type="delete_selected" dispatch="dispatch[localizations.m_delete]" form="localizations_form"}</li>
        {/if}
    {/capture}
    {dropdown content=$smarty.capture.tools_list}
{/capture}

{/capture}
{include file="common/mainbox.tpl" title=__("localizations") content=$smarty.capture.mainbox adv_buttons=$smarty.capture.adv_buttons buttons=$smarty.capture.buttons select_languages=true}