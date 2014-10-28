{if $cart_agreements || $settings.General.agree_terms_conditions == "Y"}
    <script type="text/javascript">
    //<![CDATA[
    Tygh.$.ceFormValidator('registerValidator', {
        class_name: 'cm-check-agreement',
        message: '{__("checkout_terms_n_conditions_alert")|escape:javascript}',
        func: function(id) {
            return $('#' + id).prop('checked');
        }
    });     
    
    {if $iframe_mode}
        function fn_check_agreements(suffix)
        {
            var $ = Tygh.$;
            var checked = $('form[name=payments_form_' + suffix + '] input[type=checkbox].cm-agreement').prop('checked');
            $('#payment_method_iframe' + suffix).toggleClass('hidden', checked);
        }
    {/if}
    //]]>
    </script>
        {if $settings.General.agree_terms_conditions == "Y"}
        <div class="control-group terms">
            {hook name="checkout:terms_and_conditions"}
            
            <label for="id_accept_terms{$suffix}" class="cm-check-agreement"><input type="checkbox" id="id_accept_terms{$suffix}" name="accept_terms" value="Y" class="cm-agreement checkbox" {if $iframe_mode}onclick="fn_check_agreements('{$suffix}');"{/if} />{__("checkout_terms_n_conditions")}</label>
            {/hook}
        </div>
        {/if}
        {if $cart_agreements}
        <div class="control-group license-agreement">
            {hook name="checkout:terms_and_conditions_downloadable"}
            
            <label for="product_agreements_{$suffix}" class="cm-check-agreement)"><input type="checkbox" id="product_agreements_{$suffix}" name="agreements[]" value="Y" class="cm-agreement checkbox"  {if $iframe_mode}onclick="fn_check_agreements('{$suffix}');"{/if}/><span>{__("checkout_edp_terms_n_conditions")}</span>&nbsp;<a id="sw_elm_agreements_{$suffix}" class="cm-combination link-dashed">{__("license_agreement")}</a></label>
            {/hook}
            <div class="hidden" id="elm_agreements_{$suffix}">
            {foreach from=$cart_agreements item="product_agreements"}
                {foreach from=$product_agreements item="agreement"}
                <p>{$agreement.license nofilter}</p>
                {/foreach}
            {/foreach}
            </div>
        </div>
        {/if}
{/if}
