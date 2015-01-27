{if $user_data.user_type == "C"}
    <li><a class="tool-link" href="{"reward_points.userlog?user_id=`$id`"|fn_url}">{__("view_user_points")}</a></li>
{/if}