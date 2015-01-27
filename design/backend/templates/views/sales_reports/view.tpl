{script src="js/lib/amcharts/amcharts.js"}
{script src="js/lib/amcharts/pie.js"}
{script src="js/lib/amcharts/serial.js"}

{capture name="mainbox"}
    <div id="content_{$report.report_id}">
        {if $report}
            {capture name="tabsbox"}
                {if $report.tables}
                    {assign var="table_id" value=$table.table_id}
                    {assign var="table_prefix" value="table_$table_id"}
                    <div id="content_table_{$table_id}">
                        {if !$table.elements || $table.empty_values == "Y"}
                            <p>{__("no_data")}</p>
                        {elseif $table.type == "T"}
                            {include file="views/sales_reports/components/table.tpl"}
                        {elseif $table.type == "P"}
                            <div id="{$table_prefix}pie">{include file="views/sales_reports/components/amchart_pie.tpl" chart_data=$new_array.pie_data chart_id=$table_prefix}<!--{$table_prefix}pie--></div>
                        {elseif $table.type == "B"}
                            <div id="div_scroll_{$table_id}" class="reports-graph-scroll">
                                <div id="{$table_prefix}bar">{include file="views/sales_reports/components/amchart_bar.tpl" chart_data=$new_array.column_data chart_id=$table_prefix}<!--{$table_prefix}bar--></div>
                            </div>
                        {/if}
                    <!--content_table_{$table_id}--></div>

                {else}
                    <p class="no-items">{__("no_data")}</p>
                {/if}
            {/capture}
            {include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox active_tab="table_`$table_id`" track=true}
        {else}
            <p class="no-items">{__("no_data")}</p>
        {/if}
    <!--content_{$report.report_id}--></div>
{/capture}

{capture name="buttons"}
    {capture name="tools_list"}
        <li>{btn type="list" text=__("manage_reports") href="sales_reports.manage"}</li>
        <li>{btn type="list" text=__("edit_report") href="sales_reports.update_table?report_id=$report_id&table_id=`$table.table_id`"}</li>
    {/capture}
    {dropdown content=$smarty.capture.tools_list}
{/capture}

{capture name="sidebar"}
    {include file="views/sales_reports/components/sales_reports_search_form.tpl" period=$report.period search=$report}
{/capture}

{include file="common/mainbox.tpl" title=__("reports") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons sidebar=$smarty.capture.sidebar}
