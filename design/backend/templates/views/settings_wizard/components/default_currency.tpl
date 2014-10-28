<div class="control-group setting-wide">
    <label for="" class="control-label">{__("primary_currency")}:</label>
    <div class="controls">
        <select name="default_currency">
            {foreach from=$currencies item="currency"}
                <option value="{$currency.currency_code}" {if $smarty.const.CART_PRIMARY_CURRENCY == $currency.currency_code}selected="selected"{/if}>{$currency.description}</option>
            {/foreach}
        </select>
    </div>
</div>