<script type="text/javascript">
function fn_switch_checkout_type()
{
    var $ = Tygh.$;

    $.ceNotification('closeAll');
    $('#step_one_register').show();
    $('#step_one_login').hide();
}

function fn_show_checkout_buttons(type)
{
    var $ = Tygh.$;
    if (type == 'register') {
        $('#register_checkout').show();
        $('#anonymous_checkout').hide();
    } else {
        $('#register_checkout').hide();
        $('#anonymous_checkout').show();
    }
}

</script>
    {hook name="checkout:login_form"}
        <div class="ty-checkout__login">
            {include file="views/auth/login_form.tpl" id="checkout_login" style="checkout" result_ids="checkout*,account*"}
        </div>
    {/hook}
    
    {hook name="checkout:register_customer"}
        <div class="ty-checkout__register checkout-register">
            {capture name="register"}
                {if $settings.General.approve_user_profiles != "Y"}
                    <div id="register_checkout" class="ty-checkout-buttons">{include file="buttons/button.tpl" but_onclick="fn_switch_checkout_type();" but_meta="ty-btn__primary" but_text=__("register")}</div>
                {/if}
            {/capture}
            
            {capture name="anonymous"}
                {if $settings.General.disable_anonymous_checkout != "Y"}
                    <div id="anonymous_checkout" class="cm-noscript ty-anonymous_checkout">
                        <form name="step_one_anonymous_checkout_form" class="{$ajax_form}" action="{""|fn_url}" method="post">
                            <input type="hidden" name="result_ids" value="checkout*,account*" />

                            {if !$contact_fields_filled}
                                <div class="ty-control-group">
                                    <label for="guest_email" class="cm-required">{__("email")}</label>
                                    <input type="text" id="guest_email" name="user_data[email]" size="32" value="" class="ty-input-text" />
                                </div>
                            {/if}

                            <div class="ty-checkout-buttons">
                                {include file="buttons/button.tpl" but_meta="ty-btn__primary" but_name="dispatch[checkout.customer_info.guest_checkout]" but_text=__("checkout_as_guest")}
                            </div>
                        </form>
                    </div>
                {/if}
            {/capture}

            <div class="ty-checkout__register-content">
                {if $settings.General.approve_user_profiles != "Y" || $settings.General.disable_anonymous_checkout != "Y"}
                    {include file="common/subheader.tpl" title=__("new_customer")}
                {/if}

                <ul class="ty-checkout__register-methods">
                    <li class="ty-checkout__register-methods-item">
                        <input class="ty-checkout__register-methods-radio" type="radio" id="checkout_type_register" name="checkout_type" value="" checked="checked" onclick="fn_show_checkout_buttons('register')" />
                        <label for="checkout_type_register">
                            <span class="ty-checkout__register-methods-title">{__("register")}</span>
                            <span class="ty-checkout__register-methods-hint">{__("create_new_account")}</span>
                        </label>
                    </li>

                    {if $settings.General.disable_anonymous_checkout != "Y"}
                        <li class="ty-checkout__register-methods-item">
                            <input class="ty-checkout__register-methods-radio" type="radio" id="checkout_type_guest" name="checkout_type" value="" onclick="fn_show_checkout_buttons('guest')" />
                            <label for="checkout_type_guest">
                                <span class="ty-checkout__register-methods-title">{__("checkout_as_guest")}</span>
                                <span class="ty-checkout__register-methods-hint">{__("create_guest_account")}</span>
                            </label>
                        </li>
                    {/if}
                </ul>
            </div>

            {$smarty.capture.register nofilter}
            {$smarty.capture.anonymous nofilter}
        </div>
    {/hook}
