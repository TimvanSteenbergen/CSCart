<div class="control-group">
    <label>{__("twgadmin_terms")}:</label>
    <div class="controls">
        <input type="checkbox" id="id_accept_terms" name="accept_terms" value="Y" class="cm-agreement checkbox" />
        <label for="id_accept_terms" class="cm-check-agreement">{__("twgadmin_accept_terms_n_conditions")}</label>
    </div>
</div>


<script>
//<![CDATA[
Tygh.lang.checkout_terms_n_conditions_alert = '{__("checkout_terms_n_conditions_alert")|escape:javascript}';
{literal}
Tygh.$.ceFormValidator('registerValidator', {
    class_name: 'cm-check-agreement',
    message: Tygh.lang.checkout_terms_n_conditions_alert,
    func: function(id) {
        return $('#' + id).prop('checked');
    }
});
{/literal}
//]]>
</script>
