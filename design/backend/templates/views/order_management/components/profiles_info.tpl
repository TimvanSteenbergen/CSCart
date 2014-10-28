{assign var="profile_fields" value=$location|fn_get_profile_fields}

{function name="profile_enter_data_link"}
    <div class="btn-group"><a class="btn cm-dialog-opener" data-ca-target-id="customer_info"  data-ca-scroll="{$scroll_to}">{__("enter_data")}</a></div>
{/function}

{function name="profile_edit_link"}
    {if $is_edit}
    <div class="pull-right">
        <a class="hand cm-tooltip icon-edit cm-dialog-opener{if $click_to} cm-external-click{/if}" data-ca-target-id="customer_info" data-ca-scroll="{$scroll_to}" {if $click_to}data-ca-external-click-id="{$click_to}"{/if} title="{__("edit")}"></a>
    </div>
    {/if}
{/function}

{* billing_address *}
{capture name="billing_address"}
    {hook name="order_management:profile_billing_address"}
    {if !fn_is_empty($user_data)}
        {if $profile_fields.B}
            {if $user_data.b_firstname || $user_data.b_lastname}
                <p class="strong">{$user_data.b_firstname} {$user_data.b_lastname}</p>
            {/if}
            {if $user_data.b_address}
                <p>{$user_data.b_address}</p>
            {/if}
            {if $user_data.b_address_2}
                <p>{$user_data.b_address_2}</p>
            {/if}
            {if $user_data.b_city || $user_data.b_state_descr || $user_data.b_zipcode}
                <p>{$user_data.b_city}{if $user_data.b_city && ($user_data.b_state_descr || $user_data.b_zipcode)},{/if} {$user_data.b_state_descr} {$user_data.b_zipcode}</p>
            {/if}
            {if $user_data.b_country_descr}<p>{$user_data.b_country_descr}</p>{/if}
            {include file="views/profiles/components/profile_fields_info.tpl" fields=$profile_fields.B}
            {if $user_data.b_phone}
                <p>{$user_data.b_phone}</p>
            {/if}
        {else}
            <p class="muted">{__("no_data")}</p>
        {/if}
    {else}
        <p class="muted">{__("section_is_not_completed")}</p>
        <div class="enter-data">
            {profile_enter_data_link scroll_to="profile_fields_b"}
        </div>
    {/if}
    {/hook}
{/capture}

{* shippng address *}
{capture name="shipping_address"}
    {hook name="order_management:profile_shipping_address"}
    {if !fn_is_empty($user_data)}
        {if $profile_fields.S}
            {if $user_data.s_firstname || $user_data.s_lastname}
                <p class="strong">{$user_data.s_firstname} {$user_data.s_lastname}</p>
            {/if}
            {if $user_data.s_address}
                <p>{$user_data.s_address}</p>
            {/if}
            {if $user_data.s_address_2}
                <p>{$user_data.s_address_2}</p>
            {/if}
            {if $user_data.s_city || $user_data.s_state_descr || $user_data.s_zipcode}
                <p>{$user_data.s_city}{if $user_data.s_city && ($user_data.s_state_descr || $user_data.s_zipcode)},{/if}  {$user_data.s_state_descr} {$user_data.s_zipcode}</p>
            {/if}
            {if $user_data.s_country_descr}<p>{$user_data.s_country_descr}</p>{/if}
            {include file="views/profiles/components/profile_fields_info.tpl" fields=$profile_fields.S}
            {if $user_data.s_phone}
                <p>{$user_data.s_phone}</p>
            {/if}
            {if $user_data.s_address_type}
                <p>{__("address_type")}: {$user_data.s_address_type}</p>
            {/if}
        {else}
            <p class="muted">{__("no_data")}</p>
        {/if}
    {else}
        <p class="muted">{__("section_is_not_completed")}</p>
        <div class="enter-data">
            {profile_enter_data_link scroll_to="profile_fields_s"}
        </div>
    {/if}
    {/hook}
{/capture}

{* customer information *}

{capture name="customer_information"}
    {if !fn_is_empty($user_data)}
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

        {if $user_data.ip_address}
            <span>{__("ip_address")}:</span>
            {$user_data.ip_address}
        {/if}
        <div class="clear">
            {if $user_data.phone}
                <span>{__("phone")}:</span>
                <span>{$user_data.phone}</span>
            {/if}
            {if $user_data.fax}
                <span>{__("fax")}:</span>
                <span>{$user_data.fax}</span>
            {/if}
            {if $user_data.company}
                <span>{__("company")}:</span>
                <span>{$user_data.company}</span>
            {/if}
            {if $user_data.url}
                <span>{__("website")}:</span>
                <span>{$user_data.url}</span>
            {/if}
        </div>
        {include file="views/profiles/components/profile_fields_info.tpl" fields=$profile_fields.C customer_info="Y"}
        {if $email_changed}
                <span class="text-warning strong">{__("attention")}</span>
                <span class="text-warning">{__("notice_update_customer_details")}</span>

                <label for="update_customer_details" class="checkbox">
                    <input type="checkbox" name="update_customer_details" id="update_customer_details" value="Y" />
                {__("update_customer_info")}</label>
        {/if}
    {else}
        <p class="muted">{__("section_is_not_completed")}</p>
        <div class="enter-data">
            <div class="clearfix shift-button">
                {include file="pickers/users/picker.tpl" extra_var="order_management.select_customer?page=`$smarty.request.page`" display="radio" but_text=__("select_customer") no_container=true but_meta="btn" shared_force=$users_shared_force}
            </div>
            {profile_enter_data_link scroll_to="profile_fields_c"}
        </div>
        {if $is_empty_user_data}
        <div class="text-error">
            <label class="hidden cm-required" for="user_data_required">{__("user_data_required")}</label>
            <input type="hidden" id="user_data_required" name="user_data_required" value="" />
        </div>
        {/if}
    {/if}
{/capture}

<div class="sidebar-row">
    {profile_edit_link scroll_to="profile_fields_c"}
    <h6>{__("customer_information")}</h6>
    <div class="profile-info">
        <i class="icon-user"></i>
        {$smarty.capture.customer_information nofilter}
    </div>
</div>
<hr class="profile-info-delim" />
<div class="sidebar-row">
    {profile_edit_link scroll_to="profile_fields_s" click_to="on_sta_notice"}
    <h6>{__("shipping_address")}</h6>
    <div class="profile-info">
        <i class="exicon-car"></i>
        {$smarty.capture.shipping_address nofilter}
    </div>
</div>
<hr class="profile-info-delim" />
<div class="sidebar-row">
    {profile_edit_link scroll_to="profile_fields_b"}
    <h6>{__("billing_address")}</h6>
    <div class="profile-info">
        <i class="icon-tag"></i>
        {$smarty.capture.billing_address nofilter}
    </div>
</div>
<hr class="profile-info-delim" />

{hook name="order_management:profiles_info"}
{/hook}
