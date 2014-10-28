{assign var="map_provider" value=$addons.store_locator.map_provider}
{assign var="map_provider_api" value="`$map_provider`_map_api"}
{assign var="map_customer_templates" value="C"|fn_get_store_locator_map_templates}
{assign var="map_container" value="map_canvas"}

{if $store_locations}
    {if $map_customer_templates && $map_customer_templates.$map_provider}
        {include file=$map_customer_templates.$map_provider}
    {/if}

    <div class="store-location">
        <div class="float-left store-location-wrapper" id="{$map_container}"></div>
        <div class="wysiwyg-content" id="stores_list_box">
            {foreach from=$store_locations item=loc key=num}
                <div class="store-location-item" id="loc_{$loc.store_location_id}">
                    <h2>{$loc.name}</h2>
                    {$loc.description nofilter}
                    {if $loc.city || $loc.country_title}{if $loc.city}{$loc.city}, {/if}{$loc.country_title}{/if}
                    <div>{include file="buttons/button.tpl" but_role="text" but_meta="cm-map-view-location" but_text=__("view_on_map") but_extra="data-ca-latitude={$loc.latitude} data-ca-longitude={$loc.longitude}"}</div>
                </div>
                {if $store_locations|count > 1}
                    <hr />
                {/if}
            {/foreach}

            {if $store_locations|count > 1}
                <div class="store-location-item">
                    <h2>{__("all_stores")}</h2>
                    <div>{include file="buttons/button.tpl" but_role="text" but_meta="cm-map-view-locations" but_text=__("view_on_map")}</div>
                </div>
            {/if}
        </div>
    </div>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}

{capture name="mainbox_title"}{__("store_locator")}{/capture}