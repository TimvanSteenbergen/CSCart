<div class="ty-login-info">
	{if $runtime.controller == "auth" && $runtime.mode == "login_form"}
        {hook name="auth_info:login_form"}
	    <div class="ty-login-info__txt">
		    {__("text_login_form")}
		    <a href="{"profiles.add"|fn_url}">{__("register_new_account")}</a>
		</div>
        {/hook}
	{elseif $runtime.controller == "auth" && $runtime.mode == "recover_password"}
        {hook name="auth_info:recover_password"}
	    <h4 class="ty-login-info__title">{__("text_recover_password_title")}</h4>
	    <div class="ty-login-info__txt">{__("text_recover_password")}</div>
        {/hook}
	{/if}
    {hook name="auth_info:extra"}
    {/hook}
</div>