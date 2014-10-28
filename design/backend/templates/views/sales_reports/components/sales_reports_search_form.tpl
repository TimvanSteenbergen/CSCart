<div class="sidebar-row">
<form action="{""|fn_url}" method="post" name="report_form_{$report.report_id}">
<h6>{__("search")}</h6>
    {capture name="simple_search"}
        <input type="hidden" name="report_id" value="{$report.report_id}">
        <input type="hidden" name="selected_section" value="">
        {include file="common/period_selector.tpl" period=$period display="form"}
    {/capture}
    {include file="common/advanced_search.tpl" no_adv_link=true simple_search=$smarty.capture.simple_search not_saved=true dispatch="sales_reports.set_report_view"}
</form>
</div>