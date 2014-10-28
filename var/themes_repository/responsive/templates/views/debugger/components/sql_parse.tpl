<div id="DebugToolbarSubTabSQLParse">
    <form action="{""|fn_url}" method="post" class="cm-ajax">
        <input type="hidden" name="result_ids" value="DebugToolbarSubTabSQLParse">
        <input type="hidden" name="dispatch" value="debugger.sql_parse">
        <table width="100%" style="height:50px;">
           <tr>
                <td style="width: 100px; vertical-top: top;"><input type="submit" value="Send" class="ty-btn" id="DebugToolbarSubTabSQLParseSubmit"></td>
                <td style="padding-top: 3px;">
                    <label><input type="checkbox" name="exec" value="Y" checked="checked" /> Sandbox</label>
                </td>
            </tr>
        </table>
        
        <input type="hidden" name="query" id="DebugToolbarSQLQuery">
        <table class="deb-table ty-width-full">
            <tr>
                <td><div><pre id="DebugToolbarSQLQueryValue" contenteditable="true"><code>{$query}</code></pre></div></td>
            </tr>
        </table>

    </form>

    {if $stop_exec}
        <h4>Query is invalid</h4>
    {/if}

    {if $query_time}
        <h4>Query time </small>{$query_time}</small></h4>
    {/if}

    {if $explain}
        <table class="deb-table ty-width-full">
            <caption>Explain</caption>
            <tr>
                <th>id</th>
                <th>select_type</th>
                <th>table</th>
                <th>type</th>
                <th>possible_keys</th>
                <th>key</th>
                <th>key_len</th>
                <th>ref</th>
                <th>rows</th>
                <th>Extra</th>
            </tr>
            {foreach from=$explain item="exp"}
                <tr>
                    <td>{$exp.id}</td>
                    <td>{$exp.select_type}</td>
                    <td>{$exp.table}</td>
                    <td>{$exp.type}</td>
                    <td>{$exp.possible_keys}</td>
                    <td>{$exp.key}</td>
                    <td>{$exp.key_len}</td>
                    <td>{$exp.ref}</td>
                    <td>{$exp.rows}</td>
                    <td>{$exp.Extra}</td>
                </tr>
            {/foreach}
        </table>
    {/if}

    {if $result}
        <table class="deb-table ty-width-full">
            <caption>Result</caption>
            {if $result_columns}
                <tr>
                    {foreach from=$result_columns item="column"}
                        <th>{$column}</th>
                    {/foreach}
                </tr>
                {foreach from=$result item="row"}
                    <tr>
                        {foreach from=$row item="value"}
                            <td>{$value}</td>
                        {/foreach}
                    </tr>
                {/foreach}
            {else}
            <tr>
                <td> <div><pre><code>{$result|var_dump}</code></pre></div></td>
            </tr>
            {/if}
        </table>
    {/if}

    {if $backtrace}
        <table class="deb-table ty-width-full">
            <caption>Backtrace</caption>
            <tr>
                <th>#</th>
                <th>Location</th>
                <th>Function</th>
                <th>Line</th>
            </tr>
            {foreach from=$backtrace item="item" key="key"}
                <tr>
                    <td>{$key}</td>
                    {foreach from="#"|explode:$item item="col"}
                        <td>{$col}</td>
                    {/foreach}
                </tr>
            {/foreach}
        </table>
    {/if}
<!--DebugToolbarSubTabSQLParse--></div>
