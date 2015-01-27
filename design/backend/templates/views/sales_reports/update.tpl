{if $report}
    {assign var="report_id" value=$report.report_id}
{else}
    {assign var="report_id" value=0}
{/if}

{capture name="mainbox"}

{capture name="tabsbox"}
<form action="{""|fn_url}" method="post" name="statistics_form" class=" form-horizontal">
<input type="hidden" name="report_id" value="{$report_id}">
<input type="hidden" name="selected_section" value="{$smarty.request.selected_section|default:"general"}">

<div id="content_general">

    <div class="control-group">
        <label for="description" class="cm-required control-label">{__("name")}:</label>
        <div class="controls">
            <input type="text" name="report_data[description]" id="description" value="{$report.description}" size="70">
        </div>
    </div>

    {include file="common/select_status.tpl" input_name="report_data[status]" id="report" obj=$report}
</div>

{if $report}
<div id="content_tables">
    {if $report.tables}
    <table class="table table-middle">
    <thead>
        <tr>
            <th class="center" width="1%">{include file="common/check_items.tpl"}</th>
            <th width="4%">{__("position_short")}</th>
            <th width="55%">{__("name")}</th>
            <th width="10%">{__("type")}</th>
            <th width="20%">{__("value_to_display")}</th>
            <th width="10%">&nbsp;</th>
        </tr>
    </thead>
    <tbody>
    {foreach from=$report.tables item=table}
    <tr>
        <td class="center">
            <input type="hidden" name="report_data[tables][{$table.table_id}][table_id]" value="{$table.table_id}">
            <input type="checkbox" name="del[{$table.table_id}]" id="delete_checkbox" value="Y" class="cm-item">
        </td>
        <td><input type="text" name="report_data[tables][{$table.table_id}][position]" value="{$table.position}" size="3" class="input-micro input-hidden"></td>
        <td><a href="{"sales_reports.update_table?report_id=`$report_id`&table_id=`$table.table_id`"|fn_url}">{$table.description}</a></td>
        <td>
            <select name="report_data[tables][{$table.table_id}][type]">
                <option value="T">{__("table")}</option>
                <option value="B" {if $table.type == "B"}selected="selected"{/if}>{__("graphic")} [{__("bar")}] </option>
                <option value="P" {if $table.type == "P"}selected="selected"{/if}>{__("graphic")} [{__("pie_3d")}] </option>
            </select>
        </td>
        <td>
        <select name="report_data[tables][{$table.table_id}][display]">
            {foreach from=$report_elements.values item=element}
            {assign var="element_id" value=$element.element_id}
            {assign var="element_name" value="reports_parameter_$element_id"}
                <option value="{$element.code}" {if $table.display == $element.code}selected="selected"{/if}>{__($element_name)}</option>
            {/foreach}
        </select></td>
        <td class="nowrap right">
            <div class="hidden-tools">
                {capture name="tools_list"}
                    {hook name="sales_reports:update_tools_list"}
                        <li>{btn type="list" text=__("edit") href="sales_reports.update_table?report_id=`$report_id`&table_id=`$table.table_id`"}</li>
                        <li>{btn type="delete" href="sales_reports.delete_table?table_id=`$table.table_id`&report_id=`$report.report_id`"}</li>
                    {/hook}
                {/capture}
                {dropdown content=$smarty.capture.tools_list}
            </div>
        </td>
    </tr>
    {/foreach}
    </tbody>
    </table>
    {else}
        <p class="no-items">{__("no_data")}</p>
    {/if}
</div>
{/if}
</form>
{/capture}
{include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox active_tab=$smarty.request.selected_section track=true}

{capture name="adv_buttons"}
    {if $report_id}
        {include file="common/tools.tpl" tool_href="sales_reports.update_table?report_id=`$report_id`" prefix="bottom" hide_tools=true title=__("add_chart")}
    {/if}
{/capture}

{capture name="buttons"}
    {capture name="tools_list"}
        {if $report.tables}
            <li>{btn type="delete_selected" dispatch="dispatch[sales_reports.m_delete_tables]" form="statistics_form"}</li>
        {/if}
    {/capture}
    {dropdown content=$smarty.capture.tools_list class="cm-tab-tools" id="tools_tables"}
    {include file="buttons/save_cancel.tpl" but_name="dispatch[sales_reports.update]" but_role="submit-link" but_target_form="statistics_form" save=$report_id}
{/capture}

{/capture}

{if $report_id}
    {assign var="title" value="{__("editing_report")}: `$report.description`"}
{else}
    {assign var="title" value=__("new_report")}
{/if}
{include file="common/mainbox.tpl" title=$title content=$smarty.capture.mainbox adv_buttons=$smarty.capture.adv_buttons buttons=$smarty.capture.buttons}
