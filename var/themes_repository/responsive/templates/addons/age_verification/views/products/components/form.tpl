<h1 class="ty-mainbox-title">{$product.product nofilter}</h1>

<form action="{""|fn_url}" method="post" name="age_verification">
    <div class="ty-age-verification">
        <div class="ty-age-verification__txt ty-mb-m">{if $product.age_warning_message}{$product.age_warning_message}{else}{$age_warning_message}{/if}</div>
        <input type="hidden" name="redirect_url" value="{$config.current_url}" />

        <div class="ty-control-group">
            <label class="ty-control-group__title" for="age">{__("your_age")}</label>
            <input type="text" name="age" id="age" size="10" class="ty-input-text-short">
        </div>
    </div>

    <div class="buttons-container">
        {include file="buttons/button.tpl" but_role="submit" but_text=__("verify") but_name="dispatch[age_verification.verify]" but_meta="ty-btn__secondary"}
    </div>
</form>
