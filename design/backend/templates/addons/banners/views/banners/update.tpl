{if $banner}
    {assign var="id" value=$banner.banner_id}
{else}
    {assign var="id" value=0}
{/if}

{assign var="allow_save" value=$banner|fn_allow_save_object:"banners"}

{** banners section **}

{assign var="b_type" value=$banner.type|default:"G"}

{capture name="mainbox"}

<form action="{""|fn_url}" method="post" class="form-horizontal form-edit  {if !$allow_save} cm-hide-inputs{/if}" name="banners_form" enctype="multipart/form-data">
<input type="hidden" class="cm-no-hide-input" name="fake" value="1" />
<input type="hidden" class="cm-no-hide-input" name="banner_id" value="{$id}" />

{capture name="tabsbox"}
<div id="content_general">
    <div class="control-group">
        <label for="elm_banner_name" class="control-label cm-required">{__("name")}</label>
        <div class="controls">
        <input type="text" name="banner_data[banner]" id="elm_banner_name" value="{$banner.banner}" size="25" class="input-large" /></div>
    </div>

    {if "ULTIMATE"|fn_allowed_for}
        {include file="views/companies/components/company_field.tpl"
            name="banner_data[company_id]"
            id="banner_data_company_id"
            selected=$banner.company_id
        }
    {/if}

    <div class="control-group">
        <label for="elm_banner_position" class="control-label">{__("position_short")}</label>
        <div class="controls">
            <input type="text" name="banner_data[position]" id="elm_banner_position" value="{$banner.position|default:"0"}" size="3"/>
        </div>
    </div>

    <div class="control-group">
        <label for="elm_banner_type" class="control-label cm-required">{__("type")}</label>
        <div class="controls">
        <select name="banner_data[type]" id="elm_banner_type" onchange="Tygh.$('#banner_graphic').toggle();  Tygh.$('#banner_text').toggle(); Tygh.$('#banner_url').toggle();  Tygh.$('#banner_target').toggle();">
            <option {if $banner.type == "G"}selected="selected"{/if} value="G">{__("graphic_banner")}
            <option {if $banner.type == "T"}selected="selected"{/if} value="T">{__("text_banner")}
        </select>
        </div>
    </div>

    <div class="control-group {if $b_type != "G"}hidden{/if}" id="banner_graphic">
        <label class="control-label">{__("image")}</label>
        <div class="controls">
            {include file="common/attach_images.tpl" image_name="banners_main" image_object_type="promo" image_pair=$banner.main_pair image_object_id=$id no_detailed=true hide_titles=true}
        </div>
    </div>

    <div class="control-group {if $b_type == "G"}hidden{/if}" id="banner_text">
        <label class="control-label" for="elm_banner_description">{__("description")}:</label>
        <div class="controls">
            <textarea id="elm_banner_description" name="banner_data[description]" cols="35" rows="8" class="cm-wysiwyg input-large">{$banner.description}</textarea>
        </div>
    </div>

    <div class="control-group {if $b_type == "T"}hidden{/if}" id="banner_target">
        <label class="control-label" for="elm_banner_target">{__("open_in_new_window")}</label>
        <div class="controls">
        <input type="hidden" name="banner_data[target]" value="T" />
        <input type="checkbox" name="banner_data[target]" id="elm_banner_target" value="B" {if $banner.target == "B"}checked="checked"{/if} />
        </div>
    </div>

    <div class="control-group {if $b_type == "T"}hidden{/if}" id="banner_url">
        <label class="control-label" for="elm_banner_url">{__("url")}:</label>
        <div class="controls">
            <input type="text" name="banner_data[url]" id="elm_banner_url" value="{$banner.url}" size="25" class="input-large" />
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="elm_banner_timestamp_{$id}">{__("creation_date")}</label>
        <div class="controls">
        {include file="common/calendar.tpl" date_id="elm_banner_timestamp_`$id`" date_name="banner_data[timestamp]" date_val=$banner.timestamp|default:$smarty.const.TIME start_year=$settings.Company.company_start_year}
        </div>
    </div>

    {include file="views/localizations/components/select.tpl" data_name="banner_data[localization]" data_from=$banner.localization}

    {include file="common/select_status.tpl" input_name="banner_data[status]" id="elm_banner_status" obj_id=$id obj=$banner hidden=true}
</div>
{/capture}
{include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox active_tab=$smarty.request.selected_section track=true}

{capture name="buttons"}
    {if !$id}
        {include file="buttons/save_cancel.tpl" but_role="submit-link" but_target_form="banners_form" but_name="dispatch[banners.update]"}
    {else}
        {if "ULTIMATE"|fn_allowed_for && !$allow_save}
            {assign var="hide_first_button" value=true}
            {assign var="hide_second_button" value=true}
        {/if}
        {include file="buttons/save_cancel.tpl" but_name="dispatch[banners.update]" but_role="submit-link" but_target_form="banners_form" hide_first_button=$hide_first_button hide_second_button=$hide_second_button save=$id}
    {/if}
{/capture}
    
</form>

{/capture}

{notes}
    {hook name="banners:update_notes"}
    {__("banner_details_notes", ["[layouts_href]" => fn_url('block_manager.manage')])}
    {/hook}
{/notes}

{if !$id}
    {assign var="title" value=__("banners.new_banner")}
{else}
    {assign var="title" value="{__("banners.editing_banner")}: `$banner.banner`"}
{/if}
{include file="common/mainbox.tpl" title=$title content=$smarty.capture.mainbox buttons=$smarty.capture.buttons select_languages=true}

{** banner section **}
