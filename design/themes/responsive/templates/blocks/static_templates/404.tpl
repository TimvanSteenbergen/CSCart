<div class="ty-exception">
    <div class="ty-exception__code">
        {$exception_status}
        <span class="ty-exception__code-txt">{__("exception_error")}</span>
    </div>
    <div class="ty-exception__title-info">
        <h1 class="ty-exception__title">{__("exception_title")}</h1>
        <p class="ty-exception__info">
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

        <p class="ty-exception__info">{__("exception_error_code")}
            {if $exception_status == "403"}
                {__("access_denied")}
            {elseif $exception_status == "404"}
                {__("page_not_found")}
            {/if}
        </p>
        <ul class="ty-exception__links">
            <li class="ty-exception__links-item">
                <a class="ty-exception__links-a" href="{$return_url}">{__("go_to_the_homepage")}</a>
            </li>
            <li class="ty-exception__links-item" id="go_back">
                <a class="ty-exception__links-a cm-back-link">{__("go_back")}</a>
            </li>
        </ul>
    </div>
</div>
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