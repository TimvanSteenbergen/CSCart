<div class="deb-tab-content" id="DebugToolbarTabSQLContent">
    <div class="deb-sub-tab">
        <ul>
            <li class="active"><a data-sub-tab-id="DebugToolbarSubTabSQLList">Queries list</a></li>
            <li><a data-sub-tab-id="DebugToolbarSubTabSQLCount">Queries count</a></li>
            <li><a data-sub-tab-id="DebugToolbarSubTabSQLParse">Queries parse</a></li>
        </ul>
    </div>

    <div class="deb-sub-tab-content" id="DebugToolbarSubTabSQLList">
        <table class="deb-table" id="DebugToolbarSubTabSQLListTable">
            <caption>Queries <small class="deb-font-gray">time: {$data.totals.time|number_format:"5"}</small></caption>
            <tr>
                <th>№</th>
                <th>Query</th>
                <th style="width: 60px;">Time</th>
            </tr>

            {foreach from=$data.list item="query" key="key"}
                {if $query.time > $long_query_time}
                    {assign var="color" value="deb-light-red"}
                {elseif $query.time > $medium_query_time}
                    {assign var="color" value="deb-light2-red"}
                {else}
                    {assign var="color" value=false}
                {/if}
                <tr>
                    <td {if $color}class="{$color}"{/if}><strong>{$key+1}</strong></td>
                    <td class="sql {if $color}{$color}{/if}"><a href="{"debugger.sql_parse?debugger_hash=`$debugger_hash`&sql_id=`$key`"|fn_url}" ca-data-target-id="DebugToolbarSubTabSQLParse" data-ca-target-id="DebugToolbarSubTabSQLParse" class="cm-ajax cm-ajax-cache"><pre><code>{$query.query}</code></pre></a></td>
                    <td {if $color}class="{$color}"{/if}><strong>{$query.time|number_format:"5"}</strong></td>
                </tr>

            {/foreach}
        </table>
    </div>

    <div class="deb-sub-tab-content" id="DebugToolbarSubTabSQLCount">
        <table class="deb-table">
            <caption>Queries <small class="deb-font-gray">max count: {$data.totals.rcount}</small></caption>
            <tr>
                <th>Query</th>
                <th>Count</th>
                <th>Min time</th>
                <th>Max time</th>
                <th>Average time</th>
            </tr>

            {foreach from=$data.count item="query"}
                {assign var="average_time" value=$query.total_time/$query.count_time}
                <tr>
                    <td class="sql"><pre><code>{$query.query}</code></pre></td>
                    <td style="width: 60px;"><strong>{$query.count}</strong></td>
                    <td style="width: 60px;"><strong>{$query.min_time|number_format:"5"}</strong></td>
                    <td style="width: 60px;"><strong>{$query.max_time|number_format:"5"}</strong></td>
                    <td style="width: 120px;"><strong>{$average_time|number_format:"5"}</strong></td>
                </tr>

            {/foreach}
        </table>
    </div>

    <div class="deb-sub-tab-content" id="DebugToolbarSubTabSQLParse">
        <form action="{""|fn_url}" method="post" class="cm-ajax">
            <input type="hidden" name="result_ids" value="DebugToolbarSubTabSQLParse" />
            <input type="hidden" name="dispatch[debugger.sql_parse]" value="save" />
            <input type="hidden" name="exec" value="N" />
            <table class="table-width">
                <tr>
                    <td colspan="2"><textarea cols="100" rows="20" name="query"></textarea></td>
                </tr>
                <tr>
                    <td style="width: 100px; padding-top: 10px;"><input type="submit" value="Send" class="btn" id="DebugToolbarSubTabSQLParseSubmit"></td>
                    <td style="padding-top: 15px;" valign="middle">
                        <label><input type="checkbox" name="exec" value="Y" checked="checked" /> Sandbox</label>
                    </td>
                </tr>
            </table>           
        </form>
    </div>

<!--DebugToolbarTabSQLContent--></div>
