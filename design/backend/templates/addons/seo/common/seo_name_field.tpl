{if !$hide_title}
{include file="common/subheader.tpl" title=__("seo") target="#acc_addon_seo"}
{/if}
<div id="acc_addon_seo" class="collapsed in">
<div class="control-group {if $share_dont_hide}cm-no-hide-input{/if}">
    <label class="control-label" for="elm_seo_name">{__("seo_name")}:</label>
    <div class="controls">

        {$parent_uri = $object_id|fn_get_seo_parent_uri:$object_type:$smarty.const.DESCR_SL}

        <span class="cm-field-prefix">{$parent_uri.prefix}</span><input type="text" name="{$object_name}[seo_name]" id="elm_seo_name" size="10" value="{$object_data.seo_name}" class="input-long cm-seo-check-changed" /><span class="cm-field-suffix">{$parent_uri.suffix}</span>
        <div class="hidden cm-seo-check-changed-block">
            <input type="hidden" name="{$object_name}[seo_create_redirect]" disabled="disabled" value="0" />
            <label class="checkbox inline"><input type="checkbox" name="{$object_name}[seo_create_redirect]" value="1" checked="checked" disabled="disabled" />{__("seo.create_redirect")}</label>
        </div>
    </div>
</div>
</div>
