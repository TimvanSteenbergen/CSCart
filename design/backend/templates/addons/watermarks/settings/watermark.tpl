{if "ULTIMATE"|fn_allowed_for && !$runtime.company_id}
<div id="wt_settings_block">
    <div class="right update-for-all">
        {include file="buttons/update_for_all.tpl" display=true object_id="wt_settings" name="wt_settings[update_all_vendors]" hide_element="wt_settings_block"}
    </div>
{/if}

<div class="control-group setting-wide">
    <label for="type" class="cm-required control-label">{__("type")}:</label>
    <div class="controls">
        <select name="wt_settings[type]" id="type" class="input-text" onchange="Tygh.$('#wt_graphic_watermark').switchAvailability();  Tygh.$('#wt_text_watermark').switchAvailability();">
            <option {if $wt_settings.type == "G"}selected="selected"{/if} value="G">{__("wt_graphic_watermark")}</option>
            <option {if $wt_settings.type == "T"}selected="selected"{/if} value="T">{__("wt_text_watermark")}</option>
        </select>
    </div>
</div>

<div class="control-group setting-wide" id="wt_graphic_watermark">
    <label class="control-label">{__("wt_watermark_image")}:</label>
    <div class="controls">
        {include file="common/attach_images.tpl" image_name="wt_image" image_object_type="watermark" image_pair=$wt_settings.image_pair image_object_id=$smarty.const.WATERMARK_IMAGE_ID icon_title=__("wt_watermark_icon") detailed_title=__("wt_watermark_detailed") hide_alt=true}
    </div>
</div>

<div id="wt_text_watermark">
<div class="control-group setting-wide">
    <label for="text" class="cm-required control-label">{__("wt_watermark_text")}:</label>
    <div class="controls">
        <input type="text" name="wt_settings[text]" id="text" value="{$wt_settings.text}" size="25" class="input-text-large">
    </div>
</div>

<div class="control-group setting-wide">
    <label for="wt_font" class="control-label">{__("wt_font")}:</label>
    <div class="controls">
        <select name="wt_settings[font]" id="wt_font">
            {foreach from=$wt_fonts item="font" key="f"}
            <option {if $wt_settings.font == $f}selected="selected"{/if} value="{$f}">{$font}</option>
            {/foreach}
        </select>
    </div>
</div>

<div class="control-group setting-wide">
    <label for="wt_font_color" class="control-label">{__("wt_font_color")}:</label>
    <div class="controls">
        <select name="wt_settings[font_color]" id="wt_font_color">
            {foreach from=$wt_font_colors item="color" key="c"}
            <option {if $wt_settings.font_color == $c}selected="selected"{/if} value="{$c}">{$color}</option>
            {/foreach}
        </select>
    </div>
</div>

<div class="control-group setting-wide">
    <label for="wt_font_size" class="control-label">{__("wt_font_size_icon")}:</label>
    <div class="controls">
        <select name="wt_settings[font_size_icon]" id="wt_font_size">
            {foreach from=$wt_font_sizes item="size"}
            <option {if $wt_settings.font_size_icon == $size}selected="selected"{/if} value="{$size}">{$size}px</option>
            {/foreach}
        </select>
    </div>
</div>

<div class="control-group setting-wide">
    <label for="wt_font_size" class="control-label">{__("wt_font_size_detailed")}:</label>
    <div class="controls">
        <select name="wt_settings[font_size_detailed]" id="wt_font_size">
            {foreach from=$wt_font_sizes item="size"}
            <option {if $wt_settings.font_size_detailed == $size}selected="selected"{/if} value="{$size}">{$size}px</option>
            {/foreach}
        </select>
    </div>
</div>
</div>

<script type="text/javascript">
    Tygh.$(document).ready(function(){$ldelim}
        {if $wt_settings.type == "T"}
            Tygh.$('#wt_graphic_watermark').switchAvailability(true);
        {else}
            Tygh.$('#wt_text_watermark').switchAvailability(true);
        {/if}
    {$rdelim});
</script>

<div class="control-group setting-wide">
    <label class="control-label" for="position">{__("wt_watermark_position")}:</label>
    <div class="select-field wt_position_bg controls">
        <table class="table table-middle table-bordered" style="width: 222px;">
        <tr>
            <td class="center">
                <input name="wt_settings[position]" type="radio" value="left_top" {if $wt_settings.position == "left_top"}checked="checked"{/if}>
            </td>
            <td class="center">
                <input name="wt_settings[position]" type="radio" value="center_top" {if $wt_settings.position == "center_top"}checked="checked"{/if}>
            </td>
            <td class=" center">
                <input name="wt_settings[position]" type="radio" value="right_top" {if $wt_settings.position == "right_top"}checked="checked"{/if}>
            </td>
        </tr>
        <tr>
            <td class="center">
                <input name="wt_settings[position]" type="radio" value="left_center" {if $wt_settings.position == "left_center"}checked="checked"{/if}>
            </td>
            <td class="center">
                <input name="wt_settings[position]" type="radio" value="center_center" {if $wt_settings.position == "center_center"}checked="checked"{/if}>
            </td>
            <td class="center">
                <input name="wt_settings[position]" type="radio" value="right_center" {if $wt_settings.position == "right_center"}checked="checked"{/if}>
            </td>
        </tr>
        <tr>
            <td class="center">
                <input name="wt_settings[position]" type="radio" value="left_bottom" {if $wt_settings.position == "left_bottom"}checked="checked"{/if}>
            </td>
            <td class="center">
                <input name="wt_settings[position]" type="radio" value="center_bottom" {if $wt_settings.position == "center_bottom"}checked="checked"{/if}>
            </td>
            <td class="center">
                <input name="wt_settings[position]" type="radio" value="right_bottom" {if !$wt_settings.position || $wt_settings.position == "right_bottom"}checked="checked"{/if}>
            </td>
        </tr>
        </table>
    </div>
</div>
{if "ULTIMATE"|fn_allowed_for && !$runtime.company_id && !$runtime.simple_ultimate}
</div>

<script type="text/javascript">
Tygh.$(document).ready(function(){$ldelim}
var $ = Tygh.$;
{if $settings.Stores.default_state_update_for_all != 'active'}
    $('#wt_graphic_watermark').find('.fileuploader').hide();
    $('#wt_settings_block').find(':input').prop('disabled', true);
{/if}
{literal}
    $('.cm-update-for-all-icon[data-ca-disable-id=wt_settings]').on('click', function() {
        $(this).toggleClass('visible');
        $('#wt_settings_block').find(':input').prop('disabled', !$(this).hasClass('visible'));
        if ($(this).hasClass('visible')) {
            if ($('#wt_graphic_watermark').is(':visible')) {
                $('#wt_text_watermark').switchAvailability(true);
            } else {
                $('#wt_graphic_watermark').switchAvailability(true);
            }
            $('#wt_graphic_watermark').find('.fileuploader').show();
        } else {
            $('#wt_graphic_watermark').find('.fileuploader').hide();
        }
        return false;
    });
{/literal}
{$rdelim});
</script>
{/if}