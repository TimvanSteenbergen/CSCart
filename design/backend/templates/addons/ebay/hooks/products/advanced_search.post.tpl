<div class="control-group">
    <label class="control-label" for="ebay_change_products">{__("exported_to_ebay")}</label>
    <div class="controls">
    <select name="ebay_update" id="ebay_change_products">
        <option value="">--</option>
        <option value="P" {if $search.ebay_update == "P"}selected="selected"{/if}>{__("all")}</option>
        <option value="W" {if $search.ebay_update == "W"}selected="selected"{/if}>{__("revised_after_the_latest_export")}</option>
    </select>
    </div>
</div>