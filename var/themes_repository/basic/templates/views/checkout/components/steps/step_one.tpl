<div class="step-container{if $edit}-active{/if} step-one" data-ct-checkout="user_info" id="step_one">
    {if $settings.General.checkout_style != "multi_page"}
        <h2 class="step-title{if $edit}-active{/if}{if $complete && !$edit}-complete{/if} clearfix">
            <span class="float-left">{if !$complete || $edit}1{/if}{if $complete && !$edit}<i class="icon-ok"></i>{/if}</span>

            {if $complete && !$edit}
                {hook name="checkout:edit_link"}
                <span class="float-right">
                    {include file="buttons/button.tpl" but_meta="cm-ajax" but_href="checkout.checkout?edit_step=step_one&from_step=$edit_step" but_target_id="checkout_*" but_text=__("change") but_role="tool"}
                </span>
                {/hook}
            {/if}

            {if ($settings.General.disable_anonymous_checkout == "Y" && !$auth.user_id) || ($settings.General.disable_anonymous_checkout != "Y" && !$auth.user_id && !$contact_info_population) || $smarty.session.failed_registration == true}
                {assign var="title" value=__("please_sign_in")}
            {else}
                {if $auth.user_id != 0}
                    {if $user_data.firstname || $user_data.lastname}
                        {assign var="login_info" value="`$user_data.firstname`&nbsp;`$user_data.lastname`"}
                    {else}
                        {if $settings.General.use_email_as_login == "Y"}
                            {assign var="login_info" value="`$user_data.email`"}
                        {else}
                            {assign var="login_info" value="`$user_data.user_login`"}
                        {/if}
                    {/if}
                {else}
                    {assign var="login_info" value=__("guest")}
                {/if}
                
                {assign var="title" value="{__("signed_in_as")} `$login_info`"}
            {/if}
            
            {hook name="checkout:edit_link_title"}
            <a class="title{if $contact_info_population && !$edit} cm-ajax{/if}" {if $contact_info_population && !$edit}href="{"checkout.checkout?edit_step=step_one&from_step=`$edit_step`"|fn_url}" data-ca-target-id="checkout_*"{/if}>{$title|strip_tags nofilter}</a>
            {/hook}
        </h2>
    {/if}

    <div id="step_one_body" class="step-body{if $edit}-active{/if}{if !$edit} hidden{/if}">
        {if ($settings.General.disable_anonymous_checkout == "Y" && !$auth.user_id) || ($settings.General.disable_anonymous_checkout != "Y" && !$auth.user_id && !$contact_info_population) || $smarty.session.failed_registration == true}
            <div id="step_one_login" {if $smarty.request.login_type == "register"}class="hidden"{/if}>
                <div class="clearfix">
                    {include file="views/checkout/components/checkout_login.tpl" checkout_type="one_page"}
                </div>
            </div>
            <div id="step_one_register" class="clearfix {if $smarty.request.login_type != "register"}hidden{/if}">
                    <form name="step_one_register_form" class="{$ajax_form} cm-ajax-full-render" action="{""|fn_url}" method="post">
                        <input type="hidden" name="result_ids" value="checkout*,account*" />
                        <input type="hidden" name="return_to" value="checkout" />
                        <input type="hidden" name="user_data[register_at_checkout]" value="Y" />
                        <div class="checkout-inside-block">
                            {include file="common/subheader.tpl" title=__("register_new_account")}
                            {include file="views/profiles/components/profiles_account.tpl" nothing_extra="Y" location="checkout"}
                            {include file="views/profiles/components/profile_fields.tpl" section="C" nothing_extra="Y"}
                
                            {hook name="checkout:checkout_steps"}{/hook}
                            
                            {include file="common/image_verification.tpl" option="use_for_register"}
                            
                            <div class="clear"></div>
                        </div>
                        <div class="checkout-buttons clearfix">
                            {include file="buttons/button.tpl" but_name="dispatch[checkout.add_profile]" but_text=__("register")}
                            {include file="buttons/button.tpl" but_onclick="Tygh.$('#step_one_register').hide(); Tygh.$('#step_one_login').show();" but_text=__("cancel") but_role="text"} 
                        </div>
                    </form>
            </div>
        {else}
            <form name="step_one_contact_information_form" class="{$ajax_form}" action="{""|fn_url}" method="{if !$edit}get{else}post{/if}">
            <input type="hidden" name="update_step" value="step_one" />
            <input type="hidden" name="next_step" value="{if $smarty.request.from_step && $smarty.request.from_step != "step_one"}{$smarty.request.from_step}{else}step_two{/if}" />
            <input type="hidden" name="result_ids" value="checkout*" />
                {if $edit}
                <div class="clearfix">
                    <div class="checkout-inside-block">
                        {include file="views/profiles/components/profile_fields.tpl" section="C" nothing_extra="Y" email_extra=$smarty.capture.email_extra}
                        <a href="{"auth.change_login"|fn_url}" class="relogin">{__("sign_in_as_different")}</a>
                    </div>
                </div>
                    {hook name="checkout:checkout_steps"}
                        <div class="checkout-buttons">
                            {include file="buttons/button.tpl" but_name="dispatch[checkout.update_steps]" but_text=$but_text}
                        </div>
                    {/hook}
                {/if}
            </form>
        {/if}
        
        {*if !$edit}
            {hook name="checkout:edit_link"}
            <div class="right">
                {include file="buttons/button.tpl" but_href="checkout.checkout?edit_step=step_one&from_step=`$edit_step`" but_target_id="checkout_*" but_meta="cm-ajax" but_text=__("change") but_role="tool"}
            </div>
            {/hook}
        {/if*}
    </div>
<!--step_one--></div>