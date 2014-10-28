{assign var="c_url" value=$config.current_url|fn_url}

<script type="text/javascript">
//<![CDATA[
(function(_, $) {
    $(document).ready(function() {

        $(_.doc).on('click', '#off_minimize_block', function() {
            $('#tygh_container').removeClass('top-panel-padding');
        });

        $(_.doc).on('click', '#on_minimize_block', function() {
            $('#tygh_container').addClass('top-panel-padding');
        });

        var open = $.cookie.get('minimize_block');
        if (open) {
            $('#tygh_container').removeClass('top-panel-padding');
        } else {
            $('#tygh_container').addClass('top-panel-padding');
        }

        // Countdown timer
        var mins = 30;
        var date = new Date({$smarty.now} * 1000);
        var minutes_left = date.getMinutes() > mins ? 60 - date.getMinutes() : mins - date.getMinutes();
        var seconds = Math.abs(minutes_left * 60 - date.getSeconds());

        var countdownTimer = setInterval(function secondPassed() {
            var elm = $('#timer');
            var minutes = Math.round((seconds - 30)/60);
            var remainingSeconds = seconds % 60;
            if (remainingSeconds < 10) {
                remainingSeconds = "0" + remainingSeconds;
            }
            elm.html(minutes + ":" + remainingSeconds);
            if (seconds == 0) {
                clearInterval(countdownTimer);
            } else {
                seconds--;
            }
        }, 1000);

    });
}(Tygh, Tygh.$));
//]]>
</script>

{strip}
    <div class="top-panel">
        <div id="minimize_block" class="top-panel__wrapper{if $smarty.cookies.minimize_block} hidden{/if}">
            <div class="top-panel__logo">
                <a href="http://www.cs-cart.com/compare.html" class="top-panel__logo-link" target="_blank"><i class="top-panel__icon-basket icon-basket"></i></a>
            </div>
            <h4 class="top-panel__title">
                {__("demo_panel.demo_store_panel")}
            </h4>
            <div class="top-panel-action">
                <span class="top-panel-action_item">
                    <span class="top-panel__timer"> {__("demo_panel.demo_will_be_reset_in")} <strong id="timer"></strong> {__("minutes")}</span>
                    <a href="{if "ULTIMATE"|fn_allowed_for}{$config.origin_http_location}/{/if}{$config.admin_index}" class="top-panel-btn cm-no-ajax">
                        {__("demo_panel.go_admin_panel")}
                    </a>
                    {if "MULTIVENDOR"|fn_allowed_for}
                        <a href="{$config.vendor_index}" class="top-panel-btn cm-no-ajax">
                            {__("demo_panel.go_vendor_panel")}
                        </a>
                    {/if}
                </span>

                <a href="{$c_url|fn_link_attach:"demo_customize_theme=Y"}" id="setting_customize" class="top-panel-action__setting top-panel-action_item{if $runtime.customization_mode.theme_editor} active{/if}" title="{__("theme_editor.enable")}">
                    <i class="top-panel-action__icon-setting icon-wrench"></i>
                </a>

                <a id="off_minimize_block" class="top-panel__close top-panel-action_item cm-combination-panel cm-save-state cm-ss-reverse"><i class="icon-cancel"></i></a>
            </div>
        </div>
        <a id="on_minimize_block" class="minimize-label cm-combination-panel cm-save-state cm-ss-reverse{if !$smarty.cookies.minimize_block} hidden{/if}">
            <i class="minimize-label__icon icon-down-open"></i>
        </a>
    </div>
{/strip}