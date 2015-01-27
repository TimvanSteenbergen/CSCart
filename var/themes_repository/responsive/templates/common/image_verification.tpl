{if $option|fn_needs_image_verification == true}
    {assign var="is" value=$settings.Image_verification}
    
    {assign var="id" value="iv_"|uniqid}
    <div class="captcha ty-control-group">
        {if $sidebox}
            <p>
                <img id="verification_image_{$id}" class="ty-captcha__img" src="{"image.captcha?verification_id=$id&$id"|fn_url}" alt="" onclick="this.src += '|1' ;" width="{$is.width}" height="{$is.height}" />
                <i class="ty-icon-refresh ty-captcha__refresh" onclick="document.getElementById('verification_image_{$id}').src += '|1';"></i>
            </p>
        {/if}
            <label for="verification_answer_{$id}" class="cm-required ty-captcha__label">{__("image_verification_label")}</label>
        {if !$sidebox}
            <div class="cm-field-container">
        {/if}
            <input class="ty-captcha__input cm-autocomplete-off" type="text" id="verification_answer_{$id}" name="verification_answer" value= "" />
            <input type="hidden" name="verification_id" value= "{$id}" />
        {if !$sidebox}
            <div class="ty-captcha__code">
                <img id="verification_image_{$id}" class="ty-captcha__img" src="{"image.captcha?verification_id=$id&no_session=Y&$id"|fn_url}" alt="" onclick="this.src += '|1' ;"  width="{$is.width}" height="{$is.height}" />
                <i class="ty-icon-refresh ty-captcha__refresh" onclick="document.getElementById('verification_image_{$id}').src += '|1';"></i>
            </div>
            </div><!-- close .cm-field-container  -->
        {/if}
        <div {if $align} class="ty-captcha__txt {$align}"{/if}>{__("image_verification_body")}</div>
    </div>
{/if}