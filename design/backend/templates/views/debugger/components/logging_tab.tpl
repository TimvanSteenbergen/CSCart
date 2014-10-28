<div class="deb-tab-content" id="DebugToolbarTabLoggingContent">
    {foreach from=$data item="checkpoint" key="name"}
        <table class="deb-table">
            <caption>{$name}</caption>
                {if $first}
                    {assign var="cur_memory" value=$checkpoint.memory-$previous.memory}
                    {assign var="cur_files" value=$checkpoint.included_files-$previous.included_files}
                    {assign var="cur_queries" value=$checkpoint.queries-$previous.queries}
                    {assign var="cur_time" value=$checkpoint.time-$previous.time}
                    {assign var="total_time" value=$checkpoint.time-$first.time}
                {/if}
                <tr>
                    <th width="10%">Memory</th>
                    <th width="10%">Files</th>
                    <th width="10%"v>Queries</th>
                    <th width="10%">Time</th>
                </tr>
                <tr>
                    {if $first}
                        <td>{$cur_memory|number_format} ({$checkpoint.memory|number_format})</td>
                        <td>{$cur_files} ({$checkpoint.included_files})</td>
                        <td>{$cur_queries} ({$checkpoint.queries})</td>
                        <td>{$cur_time|number_format:"4"} ({$total_time|number_format:"4"})</td>
                    {else}
                        <td>{$checkpoint.memory|number_format}</td>
                        <td>{$checkpoint.included_files}</td>
                        <td>{$checkpoint.queries}</td>
                        <td>0</td>
                        {assign var="first" value=$checkpoint}
                    {/if}
                    {assign var="previous" value=$checkpoint}
                </tr>
        </table>
    {/foreach}
</div>