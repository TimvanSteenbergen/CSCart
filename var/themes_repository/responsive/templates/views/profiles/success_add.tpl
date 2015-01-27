{capture name="mainbox_title"}{__("successfully_registered")}{/capture}

<span class="ty-success-registration__text">{__("success_registration_text")}</span>
<ul class="success-registration__list">
    {hook name="profiles:success_registration"}
        <li class="ty-success-registration__item">
            <a href="{"profiles.update"|fn_url}" class="success-registration__a">{__("edit_profile")}</a>
            <span class="ty-success-registration__info">{__("edit_profile_note")}</span>
        </li>
        <li class="ty-success-registration__item">
            <a href="{"orders.search"|fn_url}" class="success-registration__a">{__("orders")}</a>
            <span class="ty-success-registration__info">{__("track_orders")}</span>
        </li>
        <li class="ty-success-registration__item">
            <a href="{"product_features.compare"|fn_url}" class="success-registration__a">{__("compare_list")}</a>
            <span class="ty-success-registration__info">{__("compare_list_note")}</span>
        </li>
    {/hook}
</ul>
