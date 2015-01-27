<ul>
{foreach from=$smarty_tpls item="tpl"}
    {if $previous && $previous.depth > $tpl.depth}
        {section name="end_ul" loop=$previous.depth-$tpl.depth}
            </li></ul>
        {/section}
    {else}
        </li>
    {/if}

    <li>

    {if $previous && $previous.depth < $tpl.depth}
        <ul><li>
    {/if}

    {$tpl.filename}

    {assign var="previous" value=$tpl}
{/foreach}

{if $previous && $previous.depth > 0}
    {section name="close_ul" loop=$previous.depth}
        </li></ul>
    {/section}
{else}
    </ul>
{/if}

<table class="pr-table">
    <caption>Template variables</caption>
    {foreach from=$smarty_vars item="var" key="name"}
        <tr>
            <td>{$name}</td>
            <td><pre><code>{$var|var_dump}</code></pre></td>
        </tr>
    {/foreach}
</table>
