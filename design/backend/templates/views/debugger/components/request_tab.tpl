<div class="deb-tab-content" id="DebugToolbarTabRequestContent">
    <table class="deb-table">
        <caption>REQUEST</caption>
        {foreach from=$data.request item="value" key="name"}
            <tr>
                <td width="20%">{$name}</td>
                <td>{$value}</td>
            </tr>
        {/foreach}
    </table>

    <table class="deb-table">
        <caption>SERVER</caption>
        {foreach from=$data.server item="value" key="name"}
            <tr>
                <td width="20%">{$name}</td>
                <td>{$value}</td>
            </tr>
        {/foreach}
    </table>

    <table class="deb-table">
        <caption>COOKIE</caption>
        {foreach from=$data.cookie item="value" key="name"}
            <tr>
                <td width="20%">{$name}</td>
                <td>{$value}</td>
            </tr>
        {/foreach}
    </table>
</div>