<div id="storefront_settings">

{include file="addons/twigmo/settings/components/contact_twigmo_support.tpl"}

{include file="common/subheader.tpl" title=__("twgadmin_manage_storefront_settings")}

{if $runtime.forced_company_id}
    {assign var="is_one_store_mode" value=true}
{/if}
{if $tw_settings.customer_connections && ($tw_settings.customer_connections[$runtime.company_id].access_id || $tw_settings.customer_connections[$runtime.forced_company_id].access_id)}
    {assign var="current_store_is_connected" value=true}
{/if}
{if fn_allowed_for("ULTIMATE") && ($runtime.company_id || $is_one_store_mode)}
    {assign var="store_is_selected" value=true}
{/if}

{if $current_store_is_connected}

<input type="hidden" name="result_ids" value="connect_settings,storefront_settings,addon_upgrade" />

<fieldset>
    {* Use mobile frontend for ... *}
    <div class="control-group form-field">
        <label class="control-label">{__("twgadmin_use_mobile_frontend_for")}:</label>
        <div  class="controls">
            <label class="checkbox inline">
                <input type="hidden" name="tw_settings[use_for_phones]" value="N">
                <input type="checkbox" name="tw_settings[use_for_phones]" {if $tw_settings.use_for_phones == 'Y'}checked="checked"{/if} value="Y">
                {__("twgadmin_phones")}
            </label>
            <label class="checkbox inline">
                <input type="hidden" name="tw_settings[use_for_tablets]" value="N">
                <input type="checkbox" name="tw_settings[use_for_tablets]" {if $tw_settings.use_for_tablets == 'Y'}checked="checked"{/if} value="Y">
                {__("twgadmin_tablets")}
            </label>
        </div>
    </div>

    {* Home page content *}
    <div class="form-field control-group">
        <label class="control-label" for="elm_tw_home_page_content">{__("twgadmin_home_page_content")}:</label>
        <div class="controls">
                <select id="elm_tw_home_page_content" name="tw_settings[home_page_content]">
                        <option value="home_page_blocks" {if $tw_settings.home_page_content == "home_page_blocks"}selected="selected"{/if}>- {__("twgadmin_home_page_blocks")} -</option>
                        <option value="tw_home_page_blocks" {if $tw_settings.home_page_content == "tw_home_page_blocks"}selected="selected"{/if}>- {__("twgadmin_tw_home_page_blocks")} -</option>
                        {foreach from=0|fn_get_plain_categories_tree:false item="cat"}
                                {if $cat.status == "A"}
                                        <option value="{$cat.category_id}" {if $tw_settings.home_page_content == $cat.category_id}selected="selected"{/if}>{$cat.category|escape|indent:$cat.level:"&#166;&nbsp;&nbsp;&nbsp;&nbsp;":"&#166;--&nbsp;" nofilter}</option>
                                {/if}
                        {/foreach}
                </select>
                {include file="buttons/button.tpl" but_text=__("twgadmin_edit_these_blocks") but_href="block_manager.manage&selected_location=`$locations_info.index`&s_layout=`$default_layout_id`" but_id="elm_edit_home_page_blocks" but_role="link" but_meta="hidden"  but_target="_blank"}
                {include file="buttons/button.tpl" but_text=__("twgadmin_edit_these_blocks") but_href="block_manager.manage&selected_location=`$locations_info.twigmo`&s_layout=`$default_layout_id`" but_id="elm_edit_tw_home_page_blocks" but_role="link" but_meta="hidden"  but_target="_blank"}
        </div>
    </div>

    {* Go to theme editor *}
    <div class="form-field control-group">
        <label class="control-label">{__("design")}:</label>
        <div  class="controls">
            {include file="buttons/button.tpl" but_role="submit" but_meta="btn-primary cm-new-window" but_name="dispatch[addons.tw_svc_auth_te]" but_text=__("twgadmin_open_te")}
        </div>
    </div>

    {* Logo *}
    <div class="form-field control-group">
        {$id = $logo_object_id}
        <input type="text" class="hidden" name="logo_image_data[{$id}][type]" value="M">
        <input type="text" class="hidden" name="logo_image_data[{$id}][object_id]" value="{$id}">

        <label class="control-label">{__("twgadmin_mobile_logo")}:</label>
        <div class="controls">
            <div class="float-left">
                {include file="common/fileuploader.tpl" var_name="logo_image_icon[`$id`]" image=true}
            </div>
            <div class="float-left attach-images-alt logo-image">
                <img class="solid-border" src="{$tw_settings.logo_url|default:$default_logo}" />
            </div>
        </div>
    </div>

    {* Favicon *}
    <div class="form-field control-group">
        {$id = $favicon_object_id}
        <input type="text" class="hidden" name="favicon_image_data[{$id}][type]" value="M">
        <input type="text" class="hidden" name="favicon_image_data[{$id}][object_id]" value="{$id}">

        <label class="control-label">{__("twgadmin_mobile_favicon")}:</label>
        <div class="controls">
            <div class="float-left">
                {include file="common/fileuploader.tpl" var_name="favicon_image_icon[`$id`]" image=true}
            </div>
            <div class="float-left attach-images-alt logo-image">
                <img class="solid-border" src="{$favicon}" />
            </div>
        </div>
    </div>

    {* Geolocation *}
    <div class="form-field control-group">
        <label class="control-label" for="elm_tw_geolocation">{__("twgadmin_enable_geolocation")}:</label>
        <div  class="controls">
                <input type="hidden" name="tw_settings[geolocation]" value="N" />
                <span class="checkbox-wrapper">
                    <input type="checkbox" class="checkbox" id="elm_tw_geolocation" name="tw_settings[geolocation]" value="Y" {if $tw_settings.geolocation != "N"}checked="checked"{/if} />
                </span>

        </div>
    </div>

    {* Show only required profile fields *}
    <div class="form-field control-group">
        <label class="control-label" for="elm_tw_only_req_profile_fields">{__("twgadmin_only_req_profile_fields")}:</label>
        <div  class="controls">
                <input type="hidden" name="tw_settings[only_req_profile_fields]" value="N" />
                <span class="checkbox-wrapper">
                    <input type="checkbox" class="checkbox" id="elm_tw_only_req_profile_fields" name="tw_settings[only_req_profile_fields]" value="Y" {if $tw_settings.only_req_profile_fields == "Y"}checked="checked"{/if} />
                </span>
        </div>
    </div>

    {* Url for facebook *}
    <div class="form-field control-group">
        <label class="control-label" for="elm_tw_url_for_facebook">{__("twgadmin_url_for_facebook")}:</label>
        <div  class="controls">
            <input id="elm_tw_url_for_facebook" type="text" name="tw_settings[url_for_facebook]" size="30" value="{$tw_settings.url_for_facebook nofilter}" class="input-text" />
        </div>
    </div>

    {* Url for twitter *}
    <div class="form-field control-group">
        <label class="control-label" for="elm_tw_url_for_twitter">{__("twgadmin_url_for_twitter")}:</label>
        <div  class="controls">
            <input id="elm_tw_url_for_twitter" type="text" name="tw_settings[url_for_twitter]" size="30" value="{$tw_settings.url_for_twitter nofilter}" class="input-text" />
        </div>
    </div>

    {* Url on appstore *}
    <div class="form-field control-group">
        <label class="control-label" for="elm_tw_url_for_appstore">{__("twgadmin_url_on_appstore")}:</label>
        <div  class="controls">
            <input id="elm_tw_url_on_appstore" type="text" name="tw_settings[url_on_appstore]" size="30" value="{$tw_settings.url_on_appstore nofilter}" class="input-text" />
        </div>
    </div>

    {* Url on googleplay *}
    <div class="form-field control-group">
        <label class="control-label" for="elm_tw_url_on_googleplay">{__("twgadmin_url_on_googleplay")}:</label>
        <div  class="controls">
            <input id="elm_tw_url_on_googleplay" type="text" name="tw_settings[url_on_googleplay]" size="30" value="{$tw_settings.url_on_googleplay nofilter}" class="input-text" />
        </div>
    </div>

    <script>
    //<![CDATA[
    {literal}
    Tygh.$((function (_) {
        _('.form-field a.text-button-link').css({'margin': '0 0 0 10px'});
        _("#elm_tw_home_page_content").bind('change', function(){fn_tw_show_block_link();}).change();
        function fn_tw_show_block_link(){
            var value = _('#elm_tw_home_page_content option:selected').val();
            if ((value == 'home_page_blocks') || (value == 'tw_home_page_blocks')) {
                if (value == 'home_page_blocks') {
                    _('#elm_edit_home_page_blocks').show();
                    _('#elm_edit_tw_home_page_blocks').hide();
                } else {
                    _('#elm_edit_tw_home_page_blocks').show();
                    _('#elm_edit_home_page_blocks').hide();
                }
            } else {
                _('#elm_edit_home_page_blocks').hide();
                _('#elm_edit_tw_home_page_blocks').hide();
            }

            return true;
        }
    })(Tygh.$));
    {/literal}
    //]]>
    </script>

</fieldset>

{elseif !$twg_is_connected}
    {__("twgadmin_connect_to_first_ult")}
{elseif !$store_is_selected && fn_allowed_for("ULTIMATE")}
    {__("twgadmin_select_store")} - {include file="common/ajax_select_object.tpl" data_url="companies.get_companies_list?show_all=Y&action=href"  text=__("select") id=$select_id|default:"twg_top_company_id"}
{elseif !$current_store_is_connected}
    {__("twgadmin_connect_to_first_ult")}
{/if}

<!--storefront_settings--></div>
