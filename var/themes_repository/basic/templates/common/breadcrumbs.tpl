<div id="breadcrumbs_{$block.block_id}">

{if $breadcrumbs && $breadcrumbs|@sizeof > 1}
    <div class="breadcrumbs clearfix">
        {strip}
            {foreach from=$breadcrumbs item="bc" name="bcn" key="key"}
                {if $key != "0"}
                    <i class="icon-right-open-thin"></i>
                {/if}
                {if $bc.link}
                    <a href="{$bc.link|fn_url}"{if $additional_class} class="{$additional_class}"{/if}{if $bc.nofollow} rel="nofollow"{/if}>{$bc.title|strip_tags|escape:"html" nofilter}</a>
                {else}
                    <span>{$bc.title|strip_tags|escape:"html" nofilter}</span>
                {/if}
            {/foreach}
            {include file="common/view_tools.tpl"}
        {/strip}
    </div>
{/if}

<!--breadcrumbs_{$block.block_id}--></div>
