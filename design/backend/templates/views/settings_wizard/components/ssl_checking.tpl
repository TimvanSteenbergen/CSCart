<div id="ssl_checking">
    {if $checking_result == "fail"}
    <br>
    <div class="alert alert-block alert-error fade in">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <p>{__("warning_https_disabled")}</p>
    </div>
    {/if}
    <br>
    <div class="control-group setting-wide">
        <label for="" class="control-label">{__("ssl_certificate")}</label>
        <div class="controls">
            <a class="btn cm-ajax" href="{"settings_wizard.check_ssl"|fn_url}" data-ca-target-id="ssl_checking">{__("check_ssl")}</a>
            {if $checking_result == "fail"}
                <span class="label label-important">{__("fail")}</span>
            {elseif $checking_result == "ok"}
                <span class="label label-success">{__("ok")}</span>
            {/if}
        </div>
    </div>
<!--ssl_checking--></div>