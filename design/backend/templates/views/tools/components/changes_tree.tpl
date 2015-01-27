{if $parent_id}
<div class="hidden" id="changes_{$parent_id}">
{/if}
{foreach from=$changes_tree item=item key=item_id}
<table width="100%" class="table table-tree table-middle">
<tr {if $item.level % 2}class="multiple-table-row"{/if}>
    {math equation="x*14" x=$item.level|default:"0" assign="shift"}
    <td{if $item.action} class="snapshot-{$item.action}"{/if}>
    {strip}
        <span style="padding-left: {$shift}px;">
            {if $item.content}
                {if $show_all}
                    <span title="{__("expand_sublist_of_items")}" id="on_changes_{$item_id}" class="hand cm-combination {if $expand_all}hidden{/if}"><span class="exicon-expand"></span></span>
                {else}
                    <span title="{__("expand_sublist_of_items")}" id="on_changes_{$item_id}" class="hand cm-combination"><span class="exicon-expand"></span></span>
                {/if}
                <span alt="{__("collapse_sublist_of_items")}" title="{__("collapse_sublist_of_items")}" id="off_changes_{$item_id}" class="hand cm-combination{if !$expand_all || !$show_all} hidden{/if}"><span class="exicon-collapse"></span></span>
            {else}
                &nbsp;
            {/if}
            <span {if !$item.content} style="padding-left: 14px;"{/if}>{$item.name}</span>
        </span>
    {/strip}
    </td>
</tr>
</table>
{if $item.content}
    <div{if !$expand_all} class="hidden"{/if} id="changes_{$item_id}">
    {if $item.content}
        {include file="views/tools/components/changes_tree.tpl" changes_tree=$item.content parent_id=false}
    {/if}
    <!--changes_{$item_id}--></div>
{/if}
{/foreach}
{if $parent_id}<!--changes_{$parent_id}--></div>{/if}
