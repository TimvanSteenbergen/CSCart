{if $table_conditions.$table_id}
    {include file="common/subheader.tpl" title=__("table_conditions") meta="collapsed" target="#box_table_conditions_`$table_id`"}
    <div id="box_table_conditions_{$table_id}" class="collapse">
        <dl class="dl-horizontal">
        {foreach from=$table_conditions.$table_id item="i"}
            <dt>{$i.name}:</dt>
            <dd>
                {foreach from=$i.objects item="o" name="feco"}
                    {if $o.href}<a href="{$o.href|fn_url}">{/if}{$o.name}{if $o.href}</a>{/if}{if !$smarty.foreach.feco.last}, {/if}
                {/foreach}
            </dd>
        {/foreach}
        </dl>
    </div>
{/if}

{if $table.interval_id != 1}
    <table width="100%">
        <tr valign="top">
            {cycle values="" assign=""}
            <td width="30%">
                <table width="100%" class="table">
                    <thead>
                        <tr>
                            <th width="100%">{$table.parameter}</th>
                        </tr>
                    </thead>
                    {foreach from=$table.elements item=element}
                        <tr>
                            <td>{$element.description nofilter}&nbsp;</td>
                        </tr>
                    {/foreach}
                    <tr class="td-no-bg">
                        <td class="right">{__("total")}:</td>
                    </tr>
                </table>
            </td>
            <td width="70%">
                {cycle values="" assign=""}
                <div id="div_scroll_{$table_id}" class="scroll-x scroll-sales-report">
                    <table class="table no-left-border" >
                        <thead>
                            <tr class="nowrap">
                                {foreach from=$table.intervals item=row}
                                    <th class="center">&nbsp;{$row.description}&nbsp;</th>
                                {/foreach}
                            </tr>
                        </thead>
                        {foreach from=$table.elements item=element}
                            <tr>
                                {assign var="element_hash" value=$element.element_hash}
                                {foreach from=$table.intervals item=row}
                                    {assign var="interval_id" value=$row.interval_id}
                                    <td class="center">
                                        {if $table.values.$element_hash.$interval_id}
                                            {if $table.display != "product_number" && $table.display != "order_number"}{include file="common/price.tpl" value=$table.values.$element_hash.$interval_id}{else}{$table.values.$element_hash.$interval_id}{/if}
                                            {else}-{/if}</td>
                                {/foreach}
                            </tr>
                        {/foreach}
                        <tr class="td-no-bg">
                            {foreach from=$table.totals item=row}
                                <td class="center">
                                    {if $row}
                                        <span>{if $table.display != "product_number" && $table.display != "order_number"}{include file="common/price.tpl" value=$row}{else}{$row}{/if}</span>
                                        {else}-{/if}
                                </td>
                            {/foreach}
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>
{else}
<table class="table table-middle">
    <thead>
        <tr>
            <th>{$table.parameter}</th>
            {foreach from=$table.intervals item=row}
                {assign var="interval_id" value=$row.interval_id}
                {assign var="interval_name" value="reports_interval_$interval_id"}
                <th class="right">{__($interval_name)}</th>
            {/foreach}
        </tr>
    </thead>
    <tbody>
        {assign var="elements_count" value=$table.elements|sizeof}
        {foreach from=$table.elements item=element}
            {assign var="element_hash" value=$element.element_hash}
            <tr>
                {foreach from=$table.intervals item=row}
                    {assign var="interval_id" value=$row.interval_id}
                    {math equation="round(value_/max_value*100)" value_=$table.values.$element_hash.$interval_id|default:"0" max_value=$table.max_value assign="percent_value"}
                    <td>
                        {$element.description nofilter}&nbsp;
                        {include file="views/sales_reports/components/graph_bar.tpl" bar_width="100px" value_width=$percent_value}
                    </td>
                    <td  class="right">
                        {if $table.values.$element_hash.$interval_id}
                            {if $table.display != "product_number" && $table.display != "order_number"}
                                {include file="common/price.tpl" value=$table.values.$element_hash.$interval_id}{else}{$table.values.$element_hash.$interval_id}
                            {/if}
                        {else}
                            -
                        {/if}
                    </td>
                {/foreach}
            </tr>
        {/foreach}
        <tr class="td-no-bg">
            <td class="right" width="70%">{__("total")}:</td>
            <td class="right" width="30%">
                {foreach from=$table.totals item="row"}
                    {if $row}
                        {if $table.display != "product_number" && $table.display != "order_number"}
                            {include file="common/price.tpl" value=$row}
                        {else}
                            {$row}
                        {/if}
                    {else}
                        -
                    {/if}
                {/foreach}
            </td>
        </tr>
    </tbody>
</table>

{/if}
