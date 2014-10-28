{if !$runtime.simple_ultimate && $object.company_id}
    {if !$object.company_name}
        {$_company_name = $object.company_id|fn_get_company_name}
    {/if}

    {if $simple}
        <small class="muted">{$object.company_name|default:$_company_name}</small>
    {else}
        <p class="muted"><small>{$object.company_name|default:$_company_name}</small></p>
    {/if}
{/if}