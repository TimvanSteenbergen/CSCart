{capture name="mainbox"}
{if !$auth.user_id}
    <span class="right"><span>&nbsp;</span></span>

    <h1 class="clear exception-header">
        <span>{__("administration_panel")}</span>
    </h1>
{/if}

<div class="exception-body login-content">

<h2>{$exception_status}</h2>

<h3>
    {if $exception_status == "403"}
        {__("access_denied")}
    {elseif $exception_status == "404"}
        {__("page_not_found")}
    {/if}
</h3>

<div class="exception-content">
    {if $exception_status == "403"}
        <h4>{__("access_denied_text")}</h4>
    {elseif $exception_status == "404"}
        <h4>{__("page_not_found_text")}</h4>
    {/if}
    
    <ul class="exception-menu">
        <li id="go_back"><a class="cm-back-link">{__("go_back")}</a></li>
        <li><a href="{$auth|fn_get_index_script|fn_url}">{__("go_to_the_admin_homepage")}</a></li>
    </ul>

    <script type="text/javascript">
    {literal}
    Tygh.$(document).ready(function() {
        var $ = Tygh.$;
        $.each($.browser, function(i, val) {
            if ((i == 'opera') && (val == true)) {
                if (history.length == 0) {
                    $('#go_back').hide();
                }
            } else {
                if (history.length == 1) {
                    $('#go_back').hide();
                }
            }
        });
    });
    {/literal}
    </script>
</div>

</div>
{/capture}
{include file="common/mainbox.tpl" content=$smarty.capture.mainbox}
