<script type="text/javascript">
//<![CDATA[
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

//]]>
</script>
    {hook name="checkout:login_form"}
    <div class="login-form float-left">
        {include file="views/auth/login_form.tpl" id="checkout_login" style="checkout" result_ids="checkout*,account*"}
    </div>
    {/hook}
    
    {hook name="checkout:register_customer"}
    <div class="checkout-register">
        <div class="checkout-separator"></div>
        {capture name="register"}
            {if $settings.General.approve_user_profiles != "Y"}
                <div id="register_checkout" class="checkout-buttons">{include file="buttons/button.tpl" but_onclick="fn_switch_checkout_type();" but_text=__("register")}</div>
            {/if}
        {/capture}
        
        {capture name="anonymous"}
            {if $settings.General.disable_anonymous_checkout != "Y"}
                <div id="anonymous_checkout" class="cm-noscript">
                    <form name="step_one_anonymous_checkout_form" class="{$ajax_form}" action="{""|fn_url}" method="post">
                        <input type="hidden" name="result_ids" value="checkout*,account*" />

                        {if !$contact_fields_filled}
                            <div class="control-group">
                                <label for="guest_email" class="cm-required">{__("email")}</label>
                                <input type="text" id="guest_email" name="user_data[email]" size="32" value="" class="input-text " />
                            </div>
                        {/if}

                        <div class="checkout-buttons">
                            {include file="buttons/button.tpl" but_name="dispatch[checkout.customer_info.guest_checkout]" but_text=__("checkout_as_guest")}
                        </div>
                    </form>
                </div>
            {/if}
        {/capture}

        <div class="register-content">
            {if $settings.General.approve_user_profiles != "Y" || $settings.General.disable_anonymous_checkout != "Y"}
                {include file="common/subheader.tpl" title=__("new_customer")}
            {/if}

            <ul class="register-methods">
                <li class="one"><input class="radio valign" type="radio" id="checkout_type_register" name="checkout_type" value="" checked="checked" onclick="fn_show_checkout_buttons('register')" /><div class="radio1"><label for="checkout_type_register"><span class="method-title">{__("register")}</span><span class="method-hint">{__("create_new_account")}</span></label></div></li>

                {if $settings.General.disable_anonymous_checkout != "Y"}
                    <li><input class="radio valign" type="radio" id="checkout_type_guest" name="checkout_type" value="" onclick="fn_show_checkout_buttons('guest')" /><div class="radio1"><label for="checkout_type_guest"><span class="method-title">{__("checkout_as_guest")}</span><span class="method-hint">{__("create_guest_account")}</span></label></div></li>
                {/if}
            </ul>
        </div>

        {$smarty.capture.register nofilter}
        {$smarty.capture.anonymous nofilter}
    </div>
    {/hook}
