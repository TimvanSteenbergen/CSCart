<p class="ty-footer-menu__header cm-combination" id="sw_account_info_links_{$block.snapping_id}">
    <span>{__("my_account")}</span>
    <i class="ty-footer-menu__icon-open ty-icon-down-open"></i>
    <i class="ty-footer-menu__icon-hide ty-icon-up-open"></i>
</p>
<ul id="account_info_links_{$block.snapping_id}" class="ty-footer-menu__items">
{if $auth.user_id}
    <li class="ty-footer-menu__item"><a href="{"orders.search"|fn_url}">{__("orders")}</a></li>
    <li class="ty-footer-menu__item"><a href="{"profiles.update"|fn_url}">{__("profile_details")}</a></li>
{else}
    <li class="ty-footer-menu__item"><a href="{"auth.login_form"|fn_url}">{__("sign_in")}</a></li>
    <li class="ty-footer-menu__item"><a href="{"profiles.add"|fn_url}">{__("create_account")}</a></li>
{/if}
<!--account_info_links_{$block.snapping_id}--></ul>