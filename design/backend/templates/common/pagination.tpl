{assign var="id" value=$div_id|default:"pagination_contents"}
{assign var="c_url" value=$config.current_url|fn_query_remove:"page"}
{assign var="pagination" value=$search|fn_generate_pagination}

{if $smarty.capture.pagination_open == "Y"}
    {assign var="pagination_meta" value=" paginate-top"}
{/if}

{if $smarty.capture.pagination_open != "Y"}
<div class="cm-pagination-container{if $pagination_class} {$pagination_class}{/if}" id="{$id}">
{/if}

{if $pagination}
    {if $save_current_page}
        <input type="hidden" name="page" value="{$search.page|default:$smarty.request.page|default:1}" />
    {/if}

    {if $save_current_url}
        <input type="hidden" name="redirect_url" value="{$config.current_url}" />
    {/if}

    {if !$disable_history}
        {assign var="history_class" value=" cm-history"}
    {else}
        {assign var="history_class" value=" cm-ajax-cache"}
    {/if}
    <div class="pagination-wrap clearfix">
    {if $pagination.total_pages > 1}
    <div class="pagination pull-left">
        <ul>
        {if $pagination.current_page != "full_list" && $pagination.total_pages > 1}
            <li class="{if !$pagination.prev_page}disabled{/if}{$history_class}"><a data-ca-scroll=".cm-pagination-container" class="cm-ajax{$history_class}" {if $pagination.prev_page}href="{"`$c_url`&page=`$pagination.prev_page`"|fn_url}" data-ca-page="{$pagination.prev_page}" data-ca-target-id="{$id}"{/if}>&laquo;&nbsp;{__("previous")}</a></li>

            {foreach from=$pagination.navi_pages item="pg" name="f_pg"}
            <li {if $pg == $pagination.current_page}class="active" {/if}>
                {if $smarty.foreach.f_pg.first && $pg > 1 }
                <a data-ca-scroll=".cm-pagination-container" class="cm-ajax{$history_class}" href="{"`$c_url`&page=1`"|fn_url}" data-ca-page="1" data-ca-target-id="{$id}">1</a>
                {if $pg != 2}<a data-ca-scroll=".cm-pagination-container" class="{if $pagination.prev_range}cm-ajax{/if} prev-range{$history_class}" {if $pagination.prev_range}href="{"`$c_url`&page=`$pagination.prev_range`"|fn_url}" data-ca-page="{$pagination.prev_range}" data-ca-target-id="{$id}"{/if}>&nbsp;...&nbsp;</a>{/if}
                {/if}
                {if $pg != $pagination.current_page}<a data-ca-scroll=".cm-pagination-container" class="cm-ajax{$history_class}" href="{"`$c_url`&page=`$pg`"|fn_url}" data-ca-page="{$pg}" data-ca-target-id="{$id}">{$pg}</a>{else}<a href="#">{$pg}</a>{/if}
                {if $smarty.foreach.f_pg.last && $pg < $pagination.total_pages}
                {if $pg != $pagination.total_pages-1}<a data-ca-scroll=".cm-pagination-container" class="{if $pagination.next_range}cm-ajax{/if} next-range{$history_class}" {if $pagination.next_range}href="{"`$c_url`&page=`$pagination.next_range`"|fn_url}" data-ca-page="{$pagination.next_range}" data-ca-target-id="{$id}"{/if}>&nbsp;...&nbsp;</a>{/if}<a data-ca-scroll=".cm-pagination-container" class="cm-ajax{$history_class}" href="{"`$c_url`&page=`$pagination.total_pages`"|fn_url}" data-ca-page="{$pagination.total_pages}" data-ca-target-id="{$id}">{$pagination.total_pages}</a>
                {/if}
            </li>
            {/foreach}
            <li class="{if !$pagination.next_page}disabled{/if}{$history_class}"><a data-ca-scroll=".cm-pagination-container" class="{if $pagination.next_page}cm-ajax{/if}{$history_class}" {if $pagination.next_page}href="{"`$c_url`&page=`$pagination.next_page`"|fn_url}" data-ca-page="{$pagination.next_page}" data-ca-target-id="{$id}"{/if}>{__("next")}&nbsp;&raquo;</a></li>
        {/if}
        </ul>
    </div>
        {if $pagination.total_items}
            <div class="pagination-desc pull-left">
            <div class="btn-toolbar">
            <span class="pagination-total-items">&nbsp;{__("total_items")}:&nbsp;{$pagination.total_items}&nbsp;/&nbsp;</span>
            {capture name="pagination_list"}
                    {assign var="range_url" value=$c_url|fn_query_remove:"items_per_page"}
                    {foreach from=$pagination.per_page_range item="step"}
                        <li><a data-ca-scroll=".cm-pagination-container" class="cm-ajax{$history_class}" href="{"`$c_url`&items_per_page=`$step`"|fn_url}" data-ca-target-id="{$id}">{$step}</a></li>
                    {/foreach}
            {/capture}
            {math equation="rand()" assign="rnd"}
            {include file="common/tools.tpl" prefix="pagination_`$rnd`" hide_actions=true tools_list=$smarty.capture.pagination_list link_text=$pagination.items_per_page override_meta="pagination-selector" skip_check_permissions="true" override_meta="btn-text" tool_meta="{$pagination_meta} " caret=true}
            </div></div>
        {/if}
    {/if}
    </div>
{/if}


{if $smarty.capture.pagination_open == "Y"}
    <!--{$id}--></div>
    {capture name="pagination_open"}N{/capture}
{elseif $smarty.capture.pagination_open != "Y"}
    {capture name="pagination_open"}Y{/capture}
{/if}
