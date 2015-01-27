{* issuer information *}
{if $user_data}
<div class="sidebar-row">
        <h6>{__("issuer_info")}</h6>
        <div class="profile-info">
            <i class="icon-user"></i>

            <p class="strong">
                {$user_full_name = "`$user_data.firstname` `$user_data.lastname`"|trim}
                {if $user_full_name}
                    {if $user_data.user_id}
                        <a href="{"profiles.update?user_id=`$user_data.user_id`"|fn_url}">{$user_full_name}</a>,
                    {else if $user_full_name}
                        {$user_full_name},
                    {/if}
                {/if}
                <a href="mailto:{$user_data.email|escape:url}">{$user_data.email}</a>
            </p>
        </div>
</div>
<hr>
{/if}


