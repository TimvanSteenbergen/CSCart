<div class="search-field">
    <label for="gift_cert_code">{__("gift_cert_code")}:</label>
    <input type="text" name="gift_cert_code" id="gift_cert_code" value="{$search.gift_cert_code}" size="30" class="input-text" />
    <select name="gift_cert_in">
        <option value="B|U">--</option>
        <option value="B" {if $search.gift_cert_in == "B"}selected="selected"{/if}>{__("purchased")}</option>
        <option value="U" {if $search.gift_cert_in == "U"}selected="selected"{/if}>{__("used")}</option>
    </select>
</div>