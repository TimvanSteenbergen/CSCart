{if $cart.points_info.total_price && $user_points}
<div class="control-group">
    <label for="points_to_use" class="control-label">{__("points_to_use")}:</label>
    <div class="controls">
        <input type="text" name="points_to_use" id="points_to_use" size="20" value="" />
        <p class="help-block">({__("available")}:&nbsp;{$user_info.points}&nbsp;{$user_points|default:"0"}&nbsp;/&nbsp;{__("maximum")}:&nbsp;{$cart.points_info.total_price})</p>
    </div>
</div>
{/if}