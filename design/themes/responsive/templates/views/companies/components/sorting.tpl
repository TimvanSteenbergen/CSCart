<div class="ty-sort-container">
{if !$config.tweaks.disable_dhtml}
    {assign var="ajax_class" value="cm-ajax"}
{/if}

{assign var="curl" value=$config.current_url|fn_query_remove:"sort_by":"sort_order":"result_ids"}
{assign var="sorting" value=""|fn_get_companies_sorting}
{assign var="sorting_orders" value=""|fn_get_companies_sorting_orders}
{assign var="pagination_id" value=$id|default:"pagination_contents"}

{if $search.sort_order_rev == "asc"}
    {capture name="sorting_text"}
        <a>{$sorting[$search.sort_by].description}<i class="ty-icon-up-dir"></i></a>
    {/capture}
{else}
    {capture name="sorting_text"}
        <a>{$sorting[$search.sort_by].description}<i class="ty-icon-down-dir"></i></a>
    {/capture}
{/if}

<div>
{include file="common/sorting.tpl" class_pref="company-"}
</div>
</div>