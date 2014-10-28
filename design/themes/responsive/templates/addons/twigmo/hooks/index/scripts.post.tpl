{assign var="state" value=$smarty.session.twg_state}

{if $state.twg_can_be_used and !$state.mobile_link_closed}
    <script>
    //<![CDATA[
    {literal}
    $(function () {
        $('#close_notification_mobile_avail_notice').bind('click', function (e) {
            $(e.target).parents('div.mobile-avail-notice').hide();
            $.ajax({
                url: '{/literal}{fn_url("twigmo.post&close_notice=1") nofilter}{literal}',
                dataType: 'json'
            });
        });
    });
    {/literal}
    //]]>
    </script>
{/if}
