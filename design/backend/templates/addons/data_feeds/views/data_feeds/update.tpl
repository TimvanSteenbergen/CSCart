{if $datafeed_data.datafeed_id}
    {assign var="id" value=$datafeed_data.datafeed_id}
{else}
    {assign var="id" value=0}
{/if}

{capture name="mainbox"}

{capture name="tabsbox"}
{** /Item menu section **}
{assign var="date" value=$smarty.const.TIME|date_format:"%m%d%Y"}

<form action="{""|fn_url}" method="post" name="feed_update_form" class=" form-horizontal" enctype="multipart/form-data"> {* feed update form *}
<input type="hidden" name="fake" value="1" />
<input type="hidden" name="selected_section" id="selected_section" value="{$smarty.request.selected_section}" />
<input type="hidden" name="datafeed_id" value="{$id}" />

{** Datafeed description section **}

<div id="content_detailed"> {* content detailed *}
<fieldset>

{include file="common/subheader.tpl" title=__("general_settings") target="#data_feed_general_settings"}

<div id="data_feed_general_settings" class="in collapse">
    <div class="control-group">
        <label for="elm_datafeed_name" class="control-label cm-required">{__("datafeed_name")}:</label>
        <div class="controls">
            <input type="text" name="datafeed_data[datafeed_name]" id="elm_datafeed_name" size="55" value="{$datafeed_data.datafeed_name}" class="input-text-large main-input" />
        </div>
    </div>
    
    <div class="control-group">
        <label for="elm_datafeed_file_name" class="control-label cm-required">{__("filename")}:</label>
        <div class="controls">
            <input type="text" name="datafeed_data[file_name]" id="elm_datafeed_file_name" size="55" value="{$datafeed_data.file_name|default:"datafeed_`$date`.csv"}" class="input-text-large" />
        </div>
    </div>
    
    <div class="control-group">
        <label for="elm_datafeed_enclosure" class="control-label">{__("enclosure")}:</label>
        <div class="controls">
            <input type="text" name="datafeed_data[enclosure]" id="elm_datafeed_enclosure" size="55" value="{$datafeed_data.enclosure}" class="input-text-large" />
        </div>
    </div>
    
    {if $pattern.options}
    {foreach from=$pattern.options key=k item=o}
    {if !$o.import_only}
    <div class="control-group">
        <label for="elm_datafeed_element_{$p_id}_{$k}" class="control-label">
            {__($o.title)}{if $o.description}{include file="common/tooltip.tpl" tooltip=__($o.description)}{/if}:
        </label>
        <div class="controls">{if $o.type == "checkbox"}
                <input type="hidden" name="datafeed_data[export_options][{$k}]" value="N" />
                <input id="elm_datafeed_element_{$p_id}_{$k}" type="checkbox" name="datafeed_data[export_options][{$k}]" value="Y" {if $datafeed_data.export_options.$k == "Y"}checked="checked"{/if} />
            {elseif $o.type == "input"}
                <input id="elm_datafeed_element_{$p_id}_{$k}" class="input-text-large" type="text" name="datafeed_data[export_options][{$k}]" value="{$datafeed_data.export_options.$k|default:$o.default_value}" />
            {elseif $o.type == "languages"}
                <div class="checkbox-list">
                    {html_checkboxes name="datafeed_data[export_options][lang_code]" options=$datafeed_langs selected=$datafeed_data.export_options.lang_code|default:$o.default_value columns=8}
                </div>
            {elseif $o.type == "select"}
                <select id="elm_datafeed_element_{$p_id}_{$k}" name="datafeed_data[export_options][{$k}]">
                {if $o.variants_function}
                    {foreach from=$o.variants_function|call_user_func key=vk item=vi}
                    <option value="{$vk}" {if $vk == $datafeed_data.export_options.$k|default:$o.default_value}checked="checked"{/if}>{$vi}</option>
                    {/foreach}
                {else}
                    {foreach from=$o.variants key=vk item=vi}
                    <option value="{$vk}" {if $vk == $datafeed_data.export_options.$k|default:$o.default_value}checked="checked"{/if}>{__($vi)}</option>
                    {/foreach}
                {/if}
                </select>
            {/if}

            {if $o.notes}
                <p class="muted">{$o.notes nofilter}</p>
            {/if}
        </div>
    </div>
    {/if}
    {/foreach}
    {/if}
    
    <div class="control-group">
        <label for="elm_datafeed_csv_delimiter" class="control-label">{__("csv_delimiter")}:</label>
        <div class="controls">{include file="views/exim/components/csv_delimiters.tpl" name="datafeed_data[csv_delimiter]" value=$datafeed_data.csv_delimiter id="elm_datafeed_csv_delimiter"}</div>
    </div>
    
    <div class="control-group">
        <label for="elm_datafeed_exclude_disabled_products" class="control-label">{__("exclude_disabled_products")}:</label>
        <div class="controls"><input type="hidden" name="datafeed_data[exclude_disabled_products]" value="N" />
            <input type="checkbox" name="datafeed_data[exclude_disabled_products]" id="elm_datafeed_exclude_disabled_products" value="Y" {if $datafeed_data.exclude_disabled_products == "Y"}checked="checked"{/if} /></div>
    </div>
    
    {include file="common/select_status.tpl" input_name="datafeed_data[status]" id="elm_datafeed_status" obj=$datafeed_data hidden=false}
</div>

{include file="common/subheader.tpl" title=__("export_to_server") target="#data_feed_export_settings"}

<div id="data_feed_export_settings" class="in collapse">
    <div class="control-group">
        <label for="elm_datafeed_save_directory" id="label_save_directory" class="control-label">{__("save_directory")}:</label>
        <div class="controls">
            <input type="text" name="datafeed_data[save_dir]" id="elm_datafeed_save_directory" size="55" value="{$datafeed_data.save_dir|default:fn_get_files_dir_path()}" class="input-text-large" />
        </div>
    </div>
</div>

{include file="common/subheader.tpl" title=__("export_to_ftp") target="#data_feed_ftp_settings"}

<div id="data_feed_ftp_settings" class="in collapse">
    <div class="control-group">
        <label for="elm_datafeed_ftp_url" id="label_ftp_url" class="control-label">{__("ftp_url")}:</label>
        <div class="controls">
            <input type="text" name="datafeed_data[ftp_url]" id="elm_datafeed_ftp_url" size="55" value="{$datafeed_data.ftp_url}" class="input-text-large" />
            <p><small>{__("ftp_url_hint")}</small></p>
        </div>
    </div>
    
    <div class="control-group">
        <label for="elm_datafeed_ftp_user" id="label_ftp_user" class="control-label">{__("ftp_user")}:</label>
        <div class="controls">
            <input type="text" name="datafeed_data[ftp_user]" id="elm_datafeed_ftp_user" size="20" value="{$datafeed_data.ftp_user}" class="input-text-medium" />
        </div>
    </div>
    
    <div class="control-group">
        <label for="elm_datafeed_ftp_pass" class="control-label">{__("ftp_pass")}:</label>
        <div class="controls">
            <input type="password" name="datafeed_data[ftp_pass]" id="elm_datafeed_ftp_pass" size="20" value="{$datafeed_data.ftp_pass}" class="input-text-medium" />
        </div>
    </div>
</div>

{include file="common/subheader.tpl" title=__("cron_export") target="#data_feed_cron_settings"}

<div id="data_feed_cron_settings" class="in collapse">
    <div class="control-group">
        <label for="elm_datafeed_export_file_location" class="control-label">{__("export_by_cron_to")}:</label>
        <div class="controls">
            <select name="datafeed_data[export_location]" id="elm_datafeed_export_file_location">
                <option value=""> -- </option>
                <option value="S" {if $datafeed_data.export_location == "S"}selected="selected"{/if}>{__("server")}</option>
                <option value="F" {if $datafeed_data.export_location == "F"}selected="selected"{/if}>{__("ftp")}</option>
            </select>
            
            <p><small>{__("export_cron_hint")}:<br>php /path/to/cart/{""|fn_url:"A":"rel"} --dispatch=exim.cron_export --cron_password={$addons.data_feeds.cron_password}</small>
            </p>
        </div>
    </div>
</div>

</fieldset>

</div> {* /content detailed *}

<div id="content_exported_items" class="hidden"> {* content products *}
    {include file="common/subheader.tpl" title=__("categories_products")}
    {include file="pickers/categories/picker.tpl" input_name="datafeed_data[categories]" item_ids=$datafeed_data.categories multiple=true single_line=true use_keys="N" placement="right"}

    {include file="common/subheader.tpl" title=__("products")}
    {include file="pickers/products/picker.tpl" input_name="datafeed_data[products]" data_id="added_products" item_ids=$datafeed_data.products type="links" placement="right"}

</div> {* /content products *}

<div id="content_fields" class="hidden"> {* content fields *}
    {include file="addons/data_feeds/views/data_feeds/components/datafeed_fields.tpl"}
</div> {* /content fields *}

{capture name="buttons"}
    {if $id}
        {capture name="tools_list"}
            <li>{btn type="list" class="cm-ajax cm-comet" text=__("local_export") href="exim.export_datafeed?datafeed_ids[]={$id}&location=L"}</li>
            <li>{btn type="list" class="cm-ajax cm-comet" text=__("export_to_server") href="exim.export_datafeed?datafeed_ids[]={$id}&location=S"}</li>
            <li>{btn type="list" class="cm-ajax cm-comet" text=__("upload_to_ftp") href="exim.export_datafeed?datafeed_ids[]={$id}&location=F"}</li>
        {/capture}
        {dropdown content=$smarty.capture.tools_list}
    {/if}
    {include file="buttons/save_cancel.tpl" but_name="dispatch[data_feeds.update]" but_role="submit-link" but_target_form="feed_update_form" save=$id}
{/capture}

</form>

{/capture}
{include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox group_name=$runtime.controller active_tab=$smarty.request.selected_section track=true}

{/capture}

{if !$id}
    {include file="common/mainbox.tpl" title=__("add_new_datafeed") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons}
{else}
    {include file="common/mainbox.tpl" title="{__("update_datafeed")}:&nbsp;`$datafeed_data.datafeed_name`" content=$smarty.capture.mainbox select_languages=true buttons=$smarty.capture.buttons}
{/if}
