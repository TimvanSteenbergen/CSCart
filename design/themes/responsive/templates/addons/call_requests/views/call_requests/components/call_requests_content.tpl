
<form name="call_requests_form{if !$product}_main{/if}" id="form_{$id}" action="{""|fn_url}" method="post" class="cm-ajax">

{if $product}
    <input type="hidden" name="call_data[product_id]" value="{$product.product_id}" />
    <div class="ty-cr-product-info-container">
        <div class="ty-cr-product-info-image">
            {include file="common/image.tpl" images=$product.main_pair image_width=$settings.Thumbnails.product_cart_thumbnail_width image_height=$settings.Thumbnails.product_cart_thumbnail_height}
        </div>
        <div class="ty-cr-product-info-header">
            <h1 class="ty-product-block-title">{$product.product}</h1>
        </div>
    </div>
{/if}

<div class="ty-control-group">
    <label class="ty-control-group__title" for="call_data_{$id}_name">{__("your_name")}</label>
    <input id="call_data_{$id}_name" size="50" class="ty-input-text-full" type="text" name="call_data[name]" value="" />
</div>

<div class="ty-control-group">
    <label for="call_data_{$id}_phone" class="ty-control-group__title{if !$product} cm-required{/if}">{__("phone")}</label>
    <input id="call_data_{$id}_phone" class="ty-input-text-full cm-cr-mask-phone" size="50" type="text" name="call_data[phone]" value="" />
</div>

{if $product}

    <div class="ty-control-group">
        <label for="call_data_{$id}_email" class="ty-control-group__title cm-email">{__("email")}</label>
        <input id="call_data_{$id}_email" class="ty-input-text-full" size="50" type="text" name="call_data[email]" value="" />
    </div>

    <div class="cr-popup-error-box">
        <div class="hidden cm-cr-error-box help-inline">
            <p>{__("call_requests.enter_phone_or_email_text")}</p>
        </div>
    </div>

{else}

    <div class="ty-control-group">
        <label for="call_data_{$id}_convenient_time" class="ty-control-group__title">{__("call_requests.convenient_time")}</label>
        <input id="call_data_{$id}_convenient_time" class="ty-input-text cm-cr-mask-time" size="6" type="text" name="call_data[time_from]" value="" placeholder="09:00" /> -
        <input id="call_data_{$id}_convenient_time" class="ty-input-text cm-cr-mask-time" size="6" type="text" name="call_data[time_to]" value="" placeholder="20:00" />
    </div>

{/if}

<div class="buttons-container">
    {include file="buttons/button.tpl" but_name="dispatch[call_requests.request]" but_text=__("submit") but_role="submit" but_meta="ty-btn__primary ty-btn__big cm-form-dialog-closer ty-btn"}
</div>

</form>
