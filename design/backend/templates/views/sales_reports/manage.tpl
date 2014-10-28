{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="reports_list">
<div class="cm-sortable" data-ca-sortable-table="sales_reports" data-ca-sortable-id-name="report_id" id="manage_reports_list">
    {if $reports}
    <table class="table table-middle table-objects">
    {foreach from=$reports item=section}
        {include file="common/object_group.tpl"
        id=$section.report_id
        text=$section.description
        href="sales_reports.update?report_id=`$section.report_id`"
        href_delete="sales_reports.delete?report_id=`$section.report_id`"
        table="sales_reports"
        object_id_name="report_id"
        delete_target_id="manage_reports_list"
        status=$section.status
        additional_class="cm-sortable-row cm-sortable-id-`$section.report_id`"
        no_table=true
        no_popup=true
        is_view_link=false
        draggable=true}
    {/foreach}
    </table>
    {else}
        <p class="no-items">{__("no_data")}</p>
    {/if}
<!--manage_reports_list--></div>
</form>
{/capture}

{capture name="adv_buttons"}
    {include file="common/tools.tpl" tool_href="sales_reports.add" prefix="top" title=__("add_report") hide_tools=true}
{/capture}

{include file="common/mainbox.tpl" title=__("reports") content=$smarty.capture.mainbox adv_buttons=$smarty.capture.adv_buttons}