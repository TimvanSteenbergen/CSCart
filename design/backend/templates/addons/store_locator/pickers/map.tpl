{assign var="map_provider" value=$addons.store_locator.map_provider}
{assign var="map_provider_api" value="`$map_provider`_map_api"}
{assign var="map_admin_templates" value="A"|fn_get_store_locator_map_templates}
{assign var="map_container" value="map_canvas"}

{if $map_admin_templates && $map_admin_templates.$map_provider}
    {include file=$map_admin_templates.$map_provider}
{/if}

<div class="hidden" id="map_picker" title="{__("select_coordinates")}">
    <div class="map-canvas" id="{$map_container}" style="z-index: 2000; height: 100%;"></div>

    <form name="map_picker" action="" method="">
    <div class="buttons-container">
        <a class="cm-dialog-closer cm-cancel tool-link btn">{__("cancel")}</a>
        {if $allow_save}
        {include file="buttons/button.tpl" but_text=__("set") but_role="action" but_meta="btn-primary cm-dialog-closer cm-map-save-location"}
        {/if}
    </div>
    </form>
</div>