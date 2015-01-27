<ul>
{foreach from=$pages_tree item=page}
    {$shift = 14 * $page.level|default:0}
    {$expanded = $page.page_id|in_array:$runtime.active_page_ids}
    {$comb_id = "page_`$page.page_id`"}
    <li {if $page.active}class="active"{/if} {if !$search.paginate}style="padding-left: {$shift}px;"{/if}>
    {strip}
        <div class="link">
            {if $page.subpages}
                <span alt="{__("expand_sublist_of_items")}" title="{__("expand_sublist_of_items")}" id="on_{$comb_id}" class="cm-combination{if $expanded} hidden{/if}" ><span class="exicon-expand"> </span></span>
                <span alt="{__("collapse_sublist_of_items")}" title="{__("collapse_sublist_of_items")}" id="off_{$comb_id}" class="cm-combination{if !$expanded} hidden{/if}" ><span class="exicon-collapse"> </span></span>
            {/if}
            <a href="{"pages.update?page_id=`$page.page_id`&come_from=`$come_from`"|fn_url}" {if $page.status == "N"}class="manage-root-item-disabled"{/if} id="page_title_{$page.page_id}" title="{$page.page}" {if !$page.subpages} style="padding-left: 14px;"{/if}>{$page.page}</a>
        </div>
    {/strip}
    </li>
{if $page.subpages}
    <li class="{if !$expanded} hidden{/if}" id="{$comb_id}">
        {include file="views/pages/components/pages_links_tree.tpl" pages_tree=$page.subpages parent_id=$page.page_id}
    </li>
{/if}

{/foreach}
</ul>