{if "ULTIMATE"|fn_allowed_for && $runtime.company_id}
    {$suffix = "&company_id=`$runtime.company_id`"}
{else}
    {$suffix = ""}
{/if}

{if $settings.General.store_mode == "Y"} 
    {* Storefront closed *}
    {$suffix = $suffix|cat:"&store_access_key=`$settings.General.store_access_key`"}
{/if}

<object data="{"searchanise.async?no_session=Y`$suffix`"|fn_url:"C":"current" nofilter}" style="position:absolute;" width="0" height="0" type="text/html"></object>