<div class="control-group">
    <label for="gift_cert_code" class="control-label">{__("gift_cert_code")}:</label>
    <div class="controls">
        <select name="gift_cert_code" id="gift_cert_code">
            <option value=""> -- </option>
            {foreach from=$gift_certificates item="code"}
                <option value="{$code}">{$code}</option>
            {/foreach}
        </select>
    </div>
</div>