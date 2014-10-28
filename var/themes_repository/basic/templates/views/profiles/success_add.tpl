{capture name="mainbox_title"}{__("successfully_registered")}{/capture}

<span class="success-registration-text">{__("success_registration_text")}</span>
<ul class="success-registration-list">
    {hook name="profiles:success_registration"}
        <li>
            <a href="{"profiles.update"|fn_url}">{__("edit_profile")}</a>
            <span>{__("edit_profile_note")}</span>
        </li>
        <li>
            <a href="{"orders.search"|fn_url}">{__("orders")}</a>
            <span>{__("track_orders")}</span>
        </li>
        <li>
            <a href="{"product_features.compare"|fn_url}">{__("compare_list")}</a>
            <span>{__("compare_list_note")}</span>
        </li>
    {/hook}
</ul>
