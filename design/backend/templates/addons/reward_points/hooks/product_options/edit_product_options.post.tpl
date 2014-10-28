<div class="control-group">
    <label class="control-label" for="point_modifier_{$id}">{__("earned_point_modifier")}&nbsp;/ {__("type")}:</label>
    <div class="controls">
    	<input type="text" id="point_modifier_{$id}" name="option_data[variants][{$num}][point_modifier]" value="{if !empty($vr.point_modifier)}{$vr.point_modifier}{else}0.000{/if}" size="5" class="input-mini" />&nbsp;/&nbsp;<select name="option_data[variants][{$num}][point_modifier_type]">
    	    <option value="A" {if !empty($vr.point_modifier_type) && $vr.point_modifier_type == "A"}selected="selected"{/if}>({__("points_lower")})</option>
    	    <option value="P" {if !empty($vr.point_modifier_type) && $vr.point_modifier_type == "P"}selected="selected"{/if}>(%)</option>
    	</select>
    </div>
</div>