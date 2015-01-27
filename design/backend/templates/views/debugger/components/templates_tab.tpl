<div class="deb-tab-content" id="DebugToolbarTabTemplatesContent">
    <h1>Template tree</h1>
    <div class="tree">
        {include file="views/debugger/components/templates_list.tpl" list=$data.tpls}
    </div>

    <div class="deb-variables">
        <h4>Variables</h4>
        {foreach from=$data.vars item="var" key="name"}
            <a href="#{$name}">{$name}</a>
        {/foreach}
    </div>
    <table class="deb-table">
        <caption>Template variables</caption>
        {foreach from=$data.vars item="var" key="name"}
            <tr>
                <td valign="top"><strong><a name="{$name}">{$name}</a></strong></td>
                <td><pre><code>{$var|var_dump}</code></pre></td>
            </tr>
        {/foreach}
    </table>
</div>