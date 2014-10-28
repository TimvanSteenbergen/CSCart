{foreach from=$logo_types key="type" item="type_data"}

{if $logos && $logos.$type}
    {assign var="id" value=$logos.$type.logo_id}
    {assign var="image" value=$logos.$type.image}
    {assign var="company_name" value=$company_id|fn_get_company_name}
{else}
    {assign var="id" value=0}
    {assign var="image" value=[]}
{/if}
<input type="hidden" name="logotypes_image_data[{$type}][type]" value="M">
<input type="hidden" name="logotypes_image_data[{$type}][object_id]" value="{$id}">
<div class="attach-images">
    <div class="upload-box clearfix">
        <h5>{__($type_data.text)}</h5>
        <div class="image-wrap pull-left">
            <div class="image">
                {if $image}
                <img class="solid-border" src="{$image.image_path}" width="152">
                {else}
                <div class="no-image"><i class="glyph-image" title="{__("no_image")}"></i></div>
                {/if}
            </div>
            <div class="image-alt">
                <div class="input-prepend">
                    <span class="add-on cm-tooltip" title="{__("alt_text")}. {__("tt_views_site_layout_logos_alt_text")}"><i class="icon-comment"></i></span>
                    <input type="text" class="input-text cm-image-field" id="alt_text_{$type}" name="logotypes_image_data[{$type}][image_alt]" value="{$image.alt|default:$company_name}" value="">
                </div>
            </div>
        </div>

        <div class="image-upload">
            {include file="common/fileuploader.tpl" var_name="logotypes_image_icon[`$type`]"}

            {hook name="logos:upload_options"}
            {/hook}
        </div>


    </div>
</div>
{/foreach}