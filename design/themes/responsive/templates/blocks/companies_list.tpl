{** block-description:vendors **}

{if $items.companies}
    <ul>
    {foreach from=$items.companies item=v key=k}
        <li><a href="{"companies.view?company_id=`$k`"|fn_url}">{$v}</a></li>
    {/foreach}
    </ul>
    
    <div class="ty-homepage-vendors__devider">
        <a class="ty-btn ty-btn__tertiary" href="{"companies.catalog"|fn_url}">{__("all_vendors")}</a>
    </div>
{/if}