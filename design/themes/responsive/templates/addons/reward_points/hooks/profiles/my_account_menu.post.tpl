{if $auth.user_id}
<li class="ty-account-info__item ty-dropdown-box__item"><a class="ty-account-info__a" href="{"reward_points.userlog"|fn_url}" rel="nofollow">{__("my_points")}&nbsp;<span class="ty-reward-points-count">({$user_info.points|default:"0"})</span></a></li>
{/if}