{if "MULTIVENDOR"|fn_allowed_for && ($company_name || $company_id) && $settings.Vendors.display_vendor == "Y"}
    <div class="ty-control-group{if !$capture_options_vs_qty} product-list-field{/if}">
        <label class="ty-control-group__label">{__("vendor")}:</label>
        <span class="ty-control-group__item"><a href="{"companies.view?company_id=`$company_id`"|fn_url}">{if $company_name}{$company_name}{else}{$company_id|fn_get_company_name}{/if}</a></span>
    </div>
{/if}
