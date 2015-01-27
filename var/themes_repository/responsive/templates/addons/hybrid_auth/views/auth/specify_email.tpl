{assign var="id" value=$id|default:"main_login"}

<div class="ty-connect-social">
    <form name="connect-social" action="{""|fn_url}" method="post">
        <input type="hidden" name="return_url" value="{$smarty.request.return_url|default:$config.current_url}" />
        <input type="hidden" name="redirect_url" value="{$redirect_url|default:$config.current_url}" />
        <input type="hidden" name="provider" value="{$provider}" />

        <div class="ty-control-group">
            <label for="login_{$id}" class="ty-login__filed-label ty-control-group__label cm-required cm-trim cm-email">{__("email")}</label>
            <input type="text" id="login_{$id}" name="user_email" size="30" class="ty-login__input"/>
        </div>

        <div class="buttons-container clearfix">
            <div class="ty-float-right">
                {include file="buttons/button.tpl" but_text=__("continue") but_role="submit" but_name="dispatch[auth.specify_email]" but_meta="ty-btn__login ty-btn__secondary"}
            </div>
        </div>
    </form>
</div>
{capture name="mainbox_title"}{__("hybrid_auth.specify_email")}{/capture}