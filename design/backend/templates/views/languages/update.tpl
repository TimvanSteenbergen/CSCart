{if $lang_data}
    {$id = $lang_data.lang_id}
{else}
    {$id = "0"}
{/if}

<div id="content_group{$id}">

{if $id}
    <form action="{""|fn_url}" method="post" name="add_language_form" class="form-horizontal{if !""|fn_allow_save_object:"languages"} cm-hide-inputs{/if}">
    <input type="hidden" name="selected_section" value="languages" />
    <input type="hidden" name="lang_id" value="{$id}" />

    <div class="tabs cm-j-tabs">
        <ul class="nav nav-tabs">
            <li id="tab_general_{$id}" class="cm-js active"><a>{__("general")}</a></li>
        </ul>
    </div>

    <div class="cm-tabs-content">
        <div id="content_tab_general_{$id}">
            <fieldset>
                <div class="control-group">
                    <label for="elm_to_lang_code_{$id}" class="control-label cm-required">{__("language_code")}:</label>
                    <div class="controls">
                        <input id="elm_to_lang_code_{$id}" type="text" name="language_data[lang_code]" value="{$lang_data.lang_code}" size="6" maxlength="2">
                    </div>
                </div>

                <div class="control-group">
                    <label for="elm_lang_name_{$id}" class="control-label cm-required">{__("name")}:</label>
                    <div class="controls">
                        <input id="elm_lang_name_{$id}" type="text" name="language_data[name]" value="{$lang_data.name}" maxlength="64">
                    </div>
                </div>

                <div class="control-group">
                    <label for="elm_lang_country_code_{$id}" class="control-label cm-required">{__("country")}:</label>
                    <div class="controls">
                        <select id="elm_lang_country_code_{$id}" name="language_data[country_code]">
                            {foreach from=$countries item="country" key="code"}
                                <option {if $code == $lang_data.country_code}selected="selected"{/if} value="{$code}">{$country}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>

                {if "ULTIMATE:FREE"|fn_allowed_for}
                    {$hidden = false}
                {else}
                    {$hidden = true}
                {/if}
                {include file="common/select_status.tpl" obj=$lang_data display="radio" input_name="language_data[status]" hidden=$hidden}

                {if !$id}
                <div class="control-group">
                    <label for="elm_from_lang_code_{$id}" class="control-label cm-required">{__("clone_from")}:</label>
                    <div class="controls">
                        <select name="language_data[from_lang_code]" id="elm_from_lang_code_{$id}">
                            {foreach from=""|fn_get_translation_languages item="language"}
                                <option value="{$language.lang_code}">{$language.name}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                {/if}

            </fieldset>
        </div>
    </div>

    {if ""|fn_allow_save_object:"languages"}
        <div class="buttons-container">
            {include file="buttons/save_cancel.tpl" but_name="dispatch[languages.update]" cancel_action="close" save=$id}
        </div>
    {/if}

    </form>
{else}
    <form action="{""|fn_url}" method="post" name="add_language_form" class="form-horizontal{if !""|fn_allow_save_object:"languages"} cm-hide-inputs{/if}" enctype="multipart/form-data">

        <div class="control-group">
            <label for="po_file_{$id}" class="control-label cm-required">{__("po_file")}</label>
            <div class="controls">
                {include file="common/fileuploader.tpl" var_name="language_data[po_file]" allowed_ext="po,zip"}
            </div>
        </div>

        {if ""|fn_allow_save_object:"languages"}
            <div class="buttons-container">
                {include file="buttons/save_cancel.tpl" but_name="dispatch[languages.install_from_po]" but_text=__("install") cancel_action="close" save=$id}
            </div>
        {/if}
    </form>
{/if}

<!--content_group{$id}--></div>