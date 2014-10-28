{assign var="map_provider" value=$addons.store_locator.map_provider}
{assign var="map_provider_api" value="`$map_provider`_map_api"}
{assign var="map_customer_templates" value="C"|fn_get_store_locator_map_templates}
{assign var="map_container" value="map_canvas"}

{if $store_locations}
    {if $map_customer_templates && $map_customer_templates.$map_provider}
        {include file=$map_customer_templates.$map_provider}
    {/if}

    <div class="ty-store-location">
        <div class="ty-store-location__map-wrapper" id="{$map_container}"></div>
        <div class="ty-wysiwyg-content ty-store-location__locations-wrapper" id="stores_list_box">
            {foreach from=$store_locations item=loc key=num}
                <div class="ty-store-location__item" id="loc_{$loc.store_location_id}">
                    <h3 class="ty-store-location__item-title">{$loc.name}</h3>
                    
                    <span class="ty-store-location__item-desc">{$loc.description nofilter}</span>

                    {if $loc.city || $loc.country_title}
                        <span class="ty-store-location__item-country">{if $loc.city}{$loc.city}, {/if}{$loc.country_title}</span>
                    {/if}
                    
                    <div class="ty-store-location__item-view">
                        {include file="buttons/button.tpl" but_role="text" but_meta="cm-map-view-location ty-btn__tertiary" but_text=__("view_on_map") but_extra="data-ca-latitude={$loc.latitude} data-ca-longitude={$loc.longitude}"}
                    </div>
                </div>
                {if $store_locations|count > 1}
                    <hr />
                {/if}
            {/foreach}

            {if $store_locations|count > 1}
                <div class="ty-store-location__item ty-store-location__item-all_stores">
                    <h3 class="ty-store-location__item-title">{__("all_stores")}</h3>
                    <div class="ty-store-location__item-view">{include file="buttons/button.tpl" but_role="text" but_meta="cm-map-view-locations ty-btn__tertiary" but_text=__("view_on_map")}</div>
                </div>
            {/if}
        </div>
    </div>
{else}
    <p class="ty-no-items">{__("no_data")}</p>
{/if}

{capture name="mainbox_title"}{__("store_locator")}{/capture}