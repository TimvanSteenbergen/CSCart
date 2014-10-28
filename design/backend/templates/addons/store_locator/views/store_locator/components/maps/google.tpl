{if !$smarty.capture.$map_provider_api}
<script src="http://www.google.com/jsapi"></script>
<script src="http://maps.googleapis.com/maps/api/js?v=3&amp;sensor=false&amp;language={$smarty.const.CART_LANGUAGE|fn_store_locator_google_langs}" type="text/javascript"></script>
{script src="/js/addons/store_locator/google.js"}
{capture name="`$map_provider_api`"}Y{/capture}
{/if}

<script type="text/javascript">
    {literal}
    (function(_, $) {

        options = {
            {/literal}
            'latitude': {$smarty.const.STORE_LOCATOR_DEFAULT_LATITUDE|doubleval},
            'longitude': {$smarty.const.STORE_LOCATOR_DEFAULT_LONGITUDE|doubleval},
            'map_container': '{$map_container}'
            {literal}
        };

        $.ceMap('init', options);
    }(Tygh, Tygh.$));
    {/literal}
</script>