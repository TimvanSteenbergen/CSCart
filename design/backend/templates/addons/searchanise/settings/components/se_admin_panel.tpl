<script type="text/javascript">
    SearchaniseAdmin = {ldelim}{rdelim};
    SearchaniseAdmin.host = '{$se_service_url}';
    SearchaniseAdmin.PrivateKey = '{$se_parent_private_key}';
    SearchaniseAdmin.OptionsLink = '{"searchanise.options"|fn_url:'A':'current'}';
    SearchaniseAdmin.ReSyncLink = '{"searchanise.export"|fn_url:'A':'current'}';
    SearchaniseAdmin.LastRequest = '{"last_request"|fn_se_get_simple_setting|fn_parse_date|date_format:"`$date_format`"}';
    SearchaniseAdmin.LastResync = '{"last_resync"|fn_se_get_simple_setting|fn_parse_date|date_format:"`$date_format`"}';
    SearchaniseAdmin.ConnectLink = '{"searchanise.signup"|fn_url:'A':'current'}';
    SearchaniseAdmin.AddonStatus = 'enabled';

    SearchaniseAdmin.Engines = [];
    {foreach from=$se_company_id|fn_se_get_engines_data item='e'}
    SearchaniseAdmin.Engines.push({ldelim}
            PrivateKey: '{$e.private_key}',
            LangCode: '{$e.lang_code|upper}',
            Name : '{$e.language_name}',
            ExportStatus: '{$e.import_status}'{if $currencies[$secondary_currency]},
            PriceFormat: {ldelim}
                rate : {$currencies[$secondary_currency].coefficient},
                decimals: {$currencies[$secondary_currency].decimals},
                decimals_separator: '{$currencies[$secondary_currency].decimals_separator}',
                thousands_separator: '{$currencies[$secondary_currency].thousands_separator}',
                symbol: '{$currencies[$secondary_currency].symbol nofilter}',
                after: {if $currencies[$secondary_currency].after == 'N'}false{else}true{/if}
            {rdelim}{/if}
        {rdelim});
    {/foreach}
</script>

<script type="text/javascript" src="{$se_service_url}/js/init.js"></script>