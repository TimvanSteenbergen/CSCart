{capture name="mainbox"}

    {if "ULTIMATE:FULL:TRIAL"|fn_allowed_for}
        {__("upgrade_center.upgrades_are_not_available_in_trial")}
    {elseif $uc_filehash_failed}
        {__("upgrade_center.filehash_check_failed")}
    {else}
        {if $installed_upgrades.has_conflicts == true}
            <div class="notes">
                <h5>{__("note")}:</h5>
                {__("text_uc_has_conflicts")}: <a class="tool-link" href="{"upgrade_center.installed_upgrades"|fn_url}">{__("view")}</a>
            </div>
        {/if}

        {if $require_license_number == true}
            <form action="{""|fn_url}" method="post" name="uc_license_form" class="form-horizontal form-edit">
                <input type="hidden" name="redirect_url" value="{$config.current_url}">

                <div class="control-group">
                    <label for="elm_license_number" class="control-label">{__("license_number")}:</label>

                    <div class="controls">
                        <input type="text" name="settings_data[license_number]" id="elm_license_number" size="20"
                               value="{$uc_settings.license_number}" class="input-text-large">
                        {include file="buttons/button.tpl" but_name="dispatch[upgrade_center.update_settings]" but_text=__("apply") but_role="button_main"}
                        <p class="muted">{__("text_uc_license_number_required", ["[product]" => $smarty.const.PRODUCT_NAME])}</p>
                    </div>
                </div>
            </form>
        {else}

            {foreach from=$packages item="package" name="fep"}

                {capture name="mainbox_title"}
                    {__('upgrade_center')} /
                    <span class="f-middle">{$package.name}</span>
                {/capture}

                {capture name="sidebar"}
                    <div class="sidebar-row">
                        <h6>{__("upgrade")}</h6>
                        <ul class="unstyled">
                            <li>{__("version")}: {$package.to_version}</li>
                            <li>{__("release_date")}: {$package.timestamp|date_format:"`$settings.Appearance.date_format` `$settings.Appearance.time_format`"}</li>
                            <li>{__("filesize")}: {$package.size|formatfilesize nofilter}</li>
                        </ul>
                    </div>
                    <hr>
                    <div class="sidebar-row">
                        <h6>{__("description")}</h6>
                        <p>{$package.description nofilter}</p>
                    </div>
                {/capture}
                <h4>{__("package_contents")}</h4>
                {if $package.from_version == $smarty.const.PRODUCT_VERSION}
                    {if $package.is_avail == 'Y'}
                        <form action="{""|fn_url}" method="get" name="uc_form_{$package.package_id}">
                            <input type="hidden" name="package_id" value="{$package.package_id}"/>
                            <input type="hidden" name="md5" value="{$package.md5}"/>
                            {capture name="install_btn"}
                                {include file="buttons/button.tpl" but_role="submit-link" but_name="dispatch[upgrade_center.get_upgrade]" but_target_form="uc_form_{$package.package_id}" but_text=__("install")}
                            {/capture}
                        </form>
                    {elseif $package.purchase_time_limit == 'Y'}
                        <span>{__("upgrade_is_not_avail", ["[product]" => $smarty.const.PRODUCT_NAME, "[href]" => $config.resources.helpdesk_url])}</span>
                    {else}
                        <span>{__("update_period_expired")}</span>
                    {/if}
                {else}
                    <p>{__("text_uc_upgrade_needed", ["[to_version]" => $package.from_version, "[your_version]" => $smarty.const.PRODUCT_VERSION])}</p>
                {/if}
                <table class="table table-middle table-condensed">
                    <thead>
                    <th>{__("file")}</th>
                    </thead>
                    {foreach from=$package.contents item="c"}
                        <tr>
                            <td title="{$c}">
                                {$c|truncate:120:" ... ":true:true}
                            </td>
                        </tr>
                    {/foreach}
                </table>
                {foreachelse}
                <p>{__("text_no_upgrades_available")}</p>
            {/foreach}
        {/if}
    {/if}
{/capture}

{capture name="buttons"}
    {capture name="tools_list"}
        <li>{btn type="list" text=__("refresh_packages_list") href="upgrade_center.refresh"}</li>
        <li>{btn type="list" text=__("settings") href="settings.manage&section_id=Upgrade_center"}</li>
        <li class="divider"></li>
        <li>{btn type="list" text=__("installed_upgrades") href="upgrade_center.installed_upgrades"} </li>
    {/capture}
    {dropdown content=$smarty.capture.tools_list}
    {$smarty.capture.install_btn nofilter}
    {if $installed_upgrades.has_upgrades}
        {include file="buttons/button.tpl" but_href="upgrade_center.installed_upgrades" but_text=__("installed_upgrades") but_role="link"}
    {/if}
{/capture}

{include file="common/mainbox.tpl" title=$smarty.capture.mainbox_title content=$smarty.capture.mainbox buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons sidebar=$smarty.capture.sidebar}
