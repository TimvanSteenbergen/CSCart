<form name="connect-social" action="{""|fn_url}" method="post">

    <input type="hidden" name="return_url" value="{$smarty.request.return_url|default:$config.current_url}" />
    <input type="hidden" name="redirect_url" value="{$config.current_url}" />
    <input type="hidden" name="user_login" value="{$user_login}" />

    <div class="control-group">
        <label for="login" class="cm-required cm-trim{if $settings.General.use_email_as_login == "Y"} cm-email{/if}">{if $settings.General.use_email_as_login == "Y"}{__("email")}{else}{__("username")}{/if}</label>
        <input type="text" id="login" name="user_login" size="30" value="{$user_login}" class="input-text"/>
    </div>

    <div class="control-group">
        <label for="password" class="cm-required">{__("password")}</label>
        <input type="password" id="password" name="password" size="32" maxlength="32" value="" class="input-text" />
    </div>

    {include file="common/image_verification.tpl" option="use_for_register" align="left" assign="image_verification"}
    {if $image_verification}
        <div class="control-group">
            {$image_verification nofilter}
        </div>
    {/if}

    <div class="buttons-container clearfix">
        <div class="float-right">
            {include file="buttons/login.tpl" but_name="dispatch[auth.login]" but_role="submit"}
        </div>
    </div>
</form>

{capture name="mainbox_title"}{__("hybrid_auth.connect_social")}{/capture}