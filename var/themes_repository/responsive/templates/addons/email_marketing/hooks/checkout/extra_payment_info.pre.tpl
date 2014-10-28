{if $show_subscription_checkbox}
<div class="ty-control-group ty-checkout__terms">
    <label><input type="checkbox" name="subscribe_customer" value="1" class="checkbox" {if $addons.email_marketing.em_checkout_enabled != "Y"}checked="checked"{/if} />{__("email_marketing.text_subscribe")}</label>
</div>

{/if}