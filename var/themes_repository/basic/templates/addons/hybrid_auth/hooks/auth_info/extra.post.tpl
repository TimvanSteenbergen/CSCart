{if $runtime.controller == "auth"}
    {if $runtime.mode == "connect_social"}
        <h4 class="ty-login-info__title">{__("text_hybrid_auth.connect_social_title")}</h4>
        <div class="ty-login-info__txt">{__("text_hybrid_auth.connect_social")}</div>
    {/if}
    {if $runtime.mode == "specify_email"}
        <h4 class="ty-login-info__title">{__("text_hybrid_auth.specify_email_title")}</h4>
        <div class="ty-login-info__txt">{__("text_hybrid_auth.specify_email")}</div>
    {/if}
{/if}
