<div class="exception">
    <span class="exception-code"> {$exception_status} <em>{__("exception_error")}</em> </span>
<h1>{__("exception_title")}</h1>
<p>
    {if $smarty.const.HTTPS === true}
        {assign var="return_url" value=$config.https_location|fn_url}
    {else}
        {assign var="return_url" value=$config.http_location|fn_url}
    {/if}
    
    {if $exception_status == "403"}
        {__("access_denied_text")}
    {elseif $exception_status == "404"}
        {__("page_not_found_text")}
    {/if}

</p>
<p>{__("exception_error_code")}
    {if $exception_status == "403"}
        {__("access_denied")}
    {elseif $exception_status == "404"}
        {__("page_not_found")}
    {/if}
</p>
    <ul>
        <li><a href="{$return_url}">{__("go_to_the_homepage")}</a></li>
        <li id="go_back"><a class="cm-back-link">{__("go_back")}</a></li>
    </ul>
</div>
<script type="text/javascript">
    //<![CDATA[
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
    //]]>
</script>