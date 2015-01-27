{*
image_key - required
image_name - required
image_object_type - required

image_object_id - optional
image_type - optional
*}

{if !$original_image}
    {assign var="thumbnail_width" value="85"}
{/if}
{assign var="_plug" value="."|explode:""}
{assign var="key" value=$image_key|default:"0"}
{assign var="object_id" value=$image_object_id|default:"0"}
{assign var="name" value=$image_name|default:""}
{assign var="object_type" value=$image_object_type|default:""}
{assign var="type" value=$image_type|default:"M"}
{assign var="pair" value=$image_pair|default:$_plug}
{assign var="suffix" value=$image_suffix|default:""}

<input type="hidden" name="{$name}_image_data{$suffix}[{$key}][pair_id]" value="{$pair.pair_id}" class="cm-image-field" />
<input type="hidden" name="{$name}_image_data{$suffix}[{$key}][type]" value="{$type|default:"M"}" class="cm-image-field" />
<input type="hidden" name="{$name}_image_data{$suffix}[{$key}][object_id]" value="{$object_id}" class="cm-image-field" />

<div id="box_attach_images_{$name}_{$key}">
    {if $no_thumbnail && !$pair.icon}
        {__("text_thumbnail_manual_loading", ["[id]" => "sw_load_thumbnail_`$name``$suffix``$key`", "[class]" => "cm-combination dashed"])}
    {/if}
    {hook name="attach_images:thumbnail"}
    <div class="clear-both {if $no_thumbnail && !$pair.icon}hidden{/if}" id="load_thumbnail_{$name}{$suffix}{$key}">
        {if !$hide_titles}
            <p>
                <span class="field-name">{$icon_title|default:__("thumbnail")}</span>
                {if $icon_text}<span class="small-note">{$icon_text}</span>{/if}
                <span class="field-name">:</span>
            </p>
        {/if}
        
        {if !$hide_images}
            <div class="float-left image">
                {include file="common/image.tpl" images=$pair image_width=$thumbnail_width object_type=$object_type}
            </div>
        {/if}
        <div class="float-left">
        <div class="attach-images-alt">
            {include file="common/fileuploader.tpl" var_name="`$name`_image_icon`$suffix`[`$key`]" is_image=true}
        </div>

        <div>
            {if !$hide_alt}
            <label class="option_variant_alt_text">{__("alt_text")}:</label><br />
            <input type="text" class="input-text cm-image-field" id="alt_icon_{$name}_{$key}" name="{$name}_image_data{$suffix}[{$key}][image_alt]" value="{$pair.icon.alt}" />
            {/if}
        </div>
        </div>
    </div>
    {/hook}

    {if !$no_detailed}
    {hook name="attach_images:detailed"}
    <div class="margin-top">
        {if !$hide_titles}
            <p>
                <span class="field-name">{$detailed_title|default:__("popup_larger_image")}</span>
                {if $detailed_text}
                    <span class="small-note">{$detailed_text}</span>
                {/if}
                <span class="field-name">:</span>
            </p>
        {/if}
        
        {if !$hide_images}
            <div class="float-left image">
                {include file="common/image.tpl" images=$pair image_width=$thumbnail_width object_type="detailed"}
            </div>
        {/if}
        
        <div class="float-left attach-images-alt">
            {include file="common/fileuploader.tpl" var_name="`$name`_image_detailed`$suffix`[`$key`]"}
            {if !$hide_alt}
            <label for="alt_det_{$name}_{$key}">{__("alt_text")}:</label>
            <input type="text" class="input-text cm-image-field" id="alt_det_{$name}_{$key}" name="{$name}_image_data{$suffix}[{$key}][detailed_alt]" value="{$pair.detailed.alt}" />
            {/if}
            {hook name="attach_images:options_for_detailed"}
            {/hook}
        </div>

    </div>
    {/hook}
    {/if}
</div>