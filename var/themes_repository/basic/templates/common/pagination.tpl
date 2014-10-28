{assign var="id" value=$id|default:"pagination_contents"}
{assign var="pagination" value=$search|fn_generate_pagination}

{if $smarty.capture.pagination_open != "Y"}
    <div class="pagination-container cm-pagination-container" id="{$id}">

    {if $save_current_page}
    <input type="hidden" name="page" value="{$search.page|default:$smarty.request.page}" />
    {/if}

    {if $save_current_url}
    <input type="hidden" name="redirect_url" value="{$config.current_url}" />
    {/if}
{/if}

{if $pagination.total_pages > 1}
    {if $settings.Appearance.top_pagination == "Y" && $smarty.capture.pagination_open != "Y" || $smarty.capture.pagination_open == "Y"}
    {assign var="c_url" value=$config.current_url|fn_query_remove:"page"}

    {if !$config.tweaks.disable_dhtml || $force_ajax}
        {assign var="ajax_class" value="cm-ajax"}
    {/if}

    {if $smarty.capture.pagination_open == "Y"}
    <div class="pagination-bottom">
    {/if}
    <div class="pagination">
        {if $pagination.prev_range}
            <a data-ca-scroll=".cm-pagination-container" href="{"`$c_url`&page=`$pagination.prev_range``$extra_url`"|fn_url}" data-ca-page="{$pagination.prev_range}" class="cm-history prev {$ajax_class}" data-ca-target-id="{$id}">{$pagination.prev_range_from} - {$pagination.prev_range_to}</a>
        {/if}
        <a data-ca-scroll=".cm-pagination-container" class="prev {if $pagination.prev_page}cm-history {$ajax_class}{/if}" {if $pagination.prev_page}href="{"`$c_url`&page=`$pagination.prev_page`"|fn_url}" data-ca-page="{$pagination.prev_page}" data-ca-target-id="{$id}"{/if}><i class="text-arrow">&larr;</i>&nbsp;{__("prev_page")}</a>

        {foreach from=$pagination.navi_pages item="pg"}
            {if $pg != $pagination.current_page}
                <a data-ca-scroll=".cm-pagination-container" href="{"`$c_url`&page=`$pg``$extra_url`"|fn_url}" data-ca-page="{$pg}" class="cm-history {$ajax_class}" data-ca-target-id="{$id}">{$pg}</a>
            {else}
                <span class="pagination-selected-page">{$pg}</span>
            {/if}
        {/foreach}
        <span><a data-ca-scroll=".cm-pagination-container" class="next {if $pagination.next_page}cm-history {$ajax_class}{/if}" {if $pagination.next_page}href="{"`$c_url`&page=`$pagination.next_page``$extra_url`"|fn_url}" data-ca-page="{$pagination.next_page}" data-ca-target-id="{$id}"{/if}>{__("next")}&nbsp;<i class="text-arrow">&rarr;</i></a></span>

        {if $pagination.next_range}
            <a data-ca-scroll=".cm-pagination-container" href="{"`$c_url`&page=`$pagination.next_range``$extra_url`"|fn_url}" data-ca-page="{$pagination.next_range}" class="cm-history next {$ajax_class}" data-ca-target-id="{$id}">{$pagination.next_range_from} - {$pagination.next_range_to}</a>
        {/if}
    </div>
    {if $smarty.capture.pagination_open == "Y"}
    </div>
    {/if}
    {else}
    <div><a data-ca-scroll=".cm-pagination-container" href="" data-ca-page="{$pg}" data-ca-target-id="{$id}" class="hidden"></a></div>
    {/if}
{/if}

{if $smarty.capture.pagination_open == "Y"}
    <!--{$id}--></div>
    {capture name="pagination_open"}N{/capture}
{elseif $smarty.capture.pagination_open != "Y"}
    {capture name="pagination_open"}Y{/capture}
{/if}
