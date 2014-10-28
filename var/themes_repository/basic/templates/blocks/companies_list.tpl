{** block-description:vendors **}

{if $items.companies}
    <ul>
    {foreach from=$items.companies item=v key=k}
        <li><a href="{"companies.view?company_id=`$k`"|fn_url}">{$v}</a></li>
    {/foreach}
    </ul>

    {if $items.companies|count < $items.count}
        <p class="right">
            <a class="extra-link" href="{"companies.catalog"|fn_url}">{__("view_all")}</a>
        </p>
    {/if}
{/if}