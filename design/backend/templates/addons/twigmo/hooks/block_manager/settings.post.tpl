
{if $is_twigmo_location}
    <div class="control-group cm-no-hide-input">
        <label class="control-label" for="block_{$html_id}_hide_header">{__('twgadmin_hide_header')}:</label>
        <div class="controls">
            <input type="hidden" name="block_data[properties][hide_header]" value="N">
            <input type="checkbox" class="checkbox" name="block_data[properties][hide_header]" value="Y" id="block_{$html_id}_hide_header" {if $block.properties.hide_header && $block.properties.hide_header == "Y"}checked="checked"{/if} >
        </div>
    </div>
{/if}