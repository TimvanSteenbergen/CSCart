{capture name="mainbox"}

{if $params.compact == "Y"}
    {if $found_objects}
        {capture name="tabsbox"}
            {foreach from=$found_objects key="object" item="data"}
                {capture name="buttons"}
                    {$smarty.capture.buttons nofilter}
                    <div class="cm-tab-tools btn-bar btn-toolbar" id="tools_manage_{$object}_buttons">
                    <!--tools_{$object}_buttons--></div>
                {/capture}
            {/foreach}
        {/capture}
        {include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox active_tab="manage_`$search.default`" track=true}
    {else}
        <p class="no-items">{__("text_no_matching_results_found")}</p>
    {/if}
    
{else}
    <hr width="100%" />

    {if $search_results}

    {include file="common/pagination.tpl"}
    <p>&nbsp;</p>
    {foreach from=$search_results item=result}
    {if !$result.first}
    <hr />
    {/if}

    {hook name="search:search_results"}
    {if $result.object == "products"}
        {include file="views/products/components/one_product.tpl" product=$result key=$result.id}

    {elseif $result.object == "pages"}
        {include file="views/pages/components/one_page.tpl" page=$result}
    {/if}
    {/hook}

    {/foreach}

    <p>&nbsp;</p>
    {include file="common/pagination.tpl"}

    {else}
        <p class="no-items">{__("text_no_matching_results_found")}</p>
    {/if}
{/if}

{/capture}
{assign var="title" value=__("search_results_for", ["[search]" => $smarty.request.q])}
{include file="common/mainbox.tpl" title=$title content=$smarty.capture.mainbox buttons=$smarty.capture.buttons main_buttons_meta=""}