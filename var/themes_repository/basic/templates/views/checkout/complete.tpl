<div class="order-placed-successfully">
    <p>{__("text_order_placed_successfully")}
        {if $order_info}
            {if $order_info.child_ids}
                <a href="{"orders.search?period=A&order_id=`$order_info.child_ids`"|fn_url}">{__("order_details")}</a>.
            {else}
                <a href="{"orders.details?order_id=`$order_info.order_id`"|fn_url}">{__("order_details")}</a>.
            {/if}
        {/if}
    </p>
</div>

{if $order_info && $settings.General.allow_create_account_after_order == "Y" && !$auth.user_id}
<div class="order-placed-create-account">
    <h2 class="subheader">{__("create_account")}</h2>
    <div class="form-wrap login">
        <form name="order_register_form" action="{""|fn_url}" method="post">
            <input type="hidden" name="order_id" value="{$order_info.order_id}" />

            {if $settings.General.use_email_as_login != "Y"}
            <div class="control-group">
                <label for="user_login_profile" class="cm-required">{__("username")}</label>
                <input id="user_login_profile" type="text" name="user_data[user_login]" size="32" maxlength="32" value="" class="input-text" />
            </div>
            {/if}

            <div class="control-group">
                <label for="password1" class="cm-required cm-password">{__("password")}</label>
                <input type="password" id="password1" name="user_data[password1]" size="32" maxlength="32" value="" class="cm-autocomplete-off input-text" />
            </div>

            <div class="control-group">
                <label for="password2" class="cm-required cm-password">{__("confirm_password")}</label>
                <input type="password" id="password2" name="user_data[password2]" size="32" maxlength="32" value="" class="cm-autocomplete-off input-text" />
            </div>

            <div class="buttons-container body-bc clearfix wrap">
                <p>{include file="buttons/button.tpl" but_name="dispatch[checkout.create_profile]" but_text=__("create")}</p>
            </div>
        </form>
        </div>

    </div>
    <div class="order-placed-login-info">
        {hook name="checkout:payment_instruction"}
            {if $order_info.payment_method.instructions}
                <div class="login-info">
                    <h4 class="subheader">{__("payment_instructions")}</h4>
                    <div class="wysiwyg-content">
                        {$order_info.payment_method.instructions nofilter}
                    </div>
                </div>
            {/if}
        {/hook}
    </div>
{else}
    <div class="order-placed-login-info login-info-full">
        {hook name="checkout:payment_instruction"}
            {if $order_info.payment_method.instructions}
                    <h4 class="subheader">{__("payment_instructions")}</h4>
                    <div class="wysiwyg-content">
                        <br>
                        {$order_info.payment_method.instructions nofilter}
                    </div>
            {/if}
        {/hook}
    </div>
{/if}

    {* place any code you wish to display on this page right after the order has been placed *}
    {hook name="checkout:order_confirmation"}
    {/hook}

    <div class="clearfix buttons-container wrap {if !$order_info || !$settings.General.allow_create_account_after_order == "Y" || $auth.user_id} margin-top{/if}">
        {hook name="checkout:complete_button"}
            <div class="float-left">
                {if $order_info}
                    {if $order_info.child_ids}
                        {include file="buttons/button.tpl" but_text=__("order_details") but_href="orders.search?period=A&order_id=`$order_info.child_ids`"}
                    {else}
                        {include file="buttons/button.tpl" but_text=__("order_details") but_href="orders.details?order_id=`$order_info.order_id`"}
                    {/if}
                {/if}
                &nbsp;{include file="buttons/button.tpl" but_text=__("view_orders") but_href="orders.search"}
            </div>
            <div class="float-right">
                {include file="buttons/continue_shopping.tpl" but_role="text" but_meta="text-button-vmid" but_href=$continue_url|fn_url}
            </div>
        {/hook}
    </div>

{capture name="mainbox_title"}{__("order")}{/capture}