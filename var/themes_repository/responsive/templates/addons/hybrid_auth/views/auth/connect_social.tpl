{assign var="id" value=$id|default:"main_login"}

<div class="ty-connect-social">
    <form name="connect-social" action="{""|fn_url}" method="post">
        <input type="hidden" name="return_url" value="{$smarty.request.return_url|default:$config.current_url}" />
        <input type="hidden" name="redirect_url" value="{$config.current_url}" />
        <input type="hidden" name="user_login" value="{$user_login}" />

        <div class="ty-control-group">
            <label for="login_{$id}" class="ty-login__filed-label ty-control-group__label cm-required cm-trim{if $settings.General.use_email_as_login == "Y"} cm-email{/if}">{if $settings.General.use_email_as_login == "Y"}{__("email")}{else}{__("username")}{/if}</label>
            <input type="text" id="login_{$id}" name="user_login" size="30" value="{$user_login}" class="ty-login__input"/>
        </div>

        <div class="ty-control-group password-forgot">
            <label for="psw_{$id}" class="ty-login__filed-label ty-control-group__label ty-password-forgot__label cm-required ">{__("password")}</label>
            <input type="password" id="psw_{$id}" name="password" size="30" value="{$config.demo_password}" class="ty-login__input" maxlength="32" />
        </div>

        {include file="common/image_verification.tpl" option="use_for_register" align="left" assign="image_verification"}
        {if $image_verification}
            <div class="ty-control-group">
                {$image_verification nofilter}
            </div>
        {/if}

        <div class="buttons-container clearfix">
            <div class="ty-float-right">
                {include file="buttons/login.tpl" but_name="dispatch[auth.login]" but_role="submit"}
            </div>
        </div>
    </form>
</div>
{capture name="mainbox_title"}{__("hybrid_auth.connect_social")}{/capture}