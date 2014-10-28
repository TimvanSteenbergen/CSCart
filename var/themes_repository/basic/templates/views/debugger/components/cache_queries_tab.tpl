<div class="deb-tab-content" id="DebugToolbarTabCacheQueriesContent">
    <div class="deb-sub-tab">
        <ul>
            <li class="active"><a data-sub-tab-id="DebugToolbarSubTabCacheQueriesList">Queries list</a></li>
        </ul>
    </div>

    <div class="deb-sub-tab-content" id="DebugToolbarSubTabCacheQueriesList">
        <table class="deb-table" id="DebugToolbarSubTabCacheQueriesListTable">
            <caption>Queries <small class="deb-font-gray">time: {$data.totals.time|number_format:"5"}</small></caption>
            <tr>
                <th>№</th>
                <th>Query</th>
                <th width="60px">Time</th>
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
                    <td class="sql {if $color}{$color}{/if}"><pre><code>{$query.query}</code></pre></td>
                    <td {if $color}class="{$color}"{/if}><strong>{$query.time|number_format:"5"}</strong></td>
                </tr>

            {/foreach}
        </table>
    </div>

<!--DebugToolbarTabCacheQueriesContent--></div>
