{if $option|fn_needs_image_verification == true}    
    {assign var="is" value=$settings.Image_verification}
    
    {assign var="id" value="iv_"|uniqid}
    <div class="captcha control-group">
    {if $sidebox}
        <p>
            <img id="verification_image_{$id}" class="image-captcha valign" src="{"image.captcha?verification_id=$id&$id"|fn_url}" alt="" onclick="this.src += '|1' ;" width="{$is.width}" height="{$is.height}" />            
            <i class="icon-refresh" onclick="document.getElementById('verification_image_{$id}').src += '|1';"></i>
        </p>
    {/if}
        <label for="verification_answer_{$id}" class="cm-required">{__("image_verification_label")}</label>
    {if !$sidebox}
        <div class="cm-field-container">
    {/if}
        <input class="captcha-input-text valign cm-autocomplete-off" type="text" id="verification_answer_{$id}" name="verification_answer" value= "" />
        <input type="hidden" name="verification_id" value= "{$id}" />
    {if !$sidebox}
        <div class="captcha-code">
            <img id="verification_image_{$id}" class="image-captcha valign" src="{"image.captcha?verification_id=$id&no_session=Y&$id"|fn_url}" alt="" onclick="this.src += '|1' ;"  width="{$is.width}" height="{$is.height}" />
            <i class="icon-refresh" onclick="document.getElementById('verification_image_{$id}').src += '|1';"></i>
        </div>
        </div><!-- close .cm-field-container  -->
    {/if}
    <p{if $align} class="{$align}"{/if}>{__("image_verification_body")}</p>
    </div>
{/if}