<div id="addon_upgrade">
{if !$is_on_saas}
    {include file="addons/twigmo/settings/components/contact_twigmo_support.tpl"}

    {include file="common/subheader.tpl" title=__('upgrade')}

    {if $next_version_info.next_version and $next_version_info.next_version != $smarty.const.TWIGMO_VERSION}
        <p>{$next_version_info.description nofilter}</p>

        <input type="submit" name="dispatch[upgrade_center.upgrade_twigmo]" value="{__('upgrade')}" class="cm-skip-validation btn btn-primary">

        <script type="text/javascript">
        //<![CDATA[
        {literal}
        $(document).ready(function () {
            var upgradeIndicator = ' *';
            var $link = $('#twigmo_addon a');
            var oldHtml = $link.html().replace(upgradeIndicator, '');
            $link.html(oldHtml + upgradeIndicator);
        });
        {/literal}
        //]]>
        </script>
    {else}
        <p>{__('text_no_upgrades_available')}</p>
        <div class="buttons-container">
            {include file="buttons/button.tpl" but_name="dispatch[twigmo_updates.check]" but_text=__('twgadmin_check_for_updates') but_role="submit" but_meta="cm-ajax cm-skip-avail-switch"}
            {if $twg_is_connected}
                <input type="hidden" name="result_ids" value="addon_upgrade" />
            {/if}
        </div>
    {/if}
{else}
    <script type="text/javascript">
    //<![CDATA[
    $(document).ready(function () {
        $('#twigmo_addon').hide();
    });
    //]]>
    </script>
{/if}
<!--addon_upgrade--></div>
