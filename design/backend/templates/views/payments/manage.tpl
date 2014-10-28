{script src="js/tygh/tabs.js"}

{capture name="mainbox"}

<script type="text/javascript">
var processor_descriptions = [];
{foreach from=$payment_processors item="p"}
processor_descriptions[{$p.processor_id}] = '{$p.description|escape:javascript nofilter}';
{/foreach}
function fn_switch_processor(payment_id, processor_id)
{
    Tygh.$('#tab_conf_' + payment_id).toggleBy(processor_id == 0);
    if (processor_id != 0) {
        Tygh.$('#tab_conf_' + payment_id + ' a').prop('href', fn_url('payments.processor?payment_id=' + payment_id + '&processor_id=' + processor_id));
        Tygh.$('#content_tab_conf_' + payment_id).remove();
        Tygh.$('#elm_payment_tpl_' + payment_id).prop('disabled', true);
        if (processor_descriptions[processor_id]) {
            Tygh.$('#elm_processor_description_' + payment_id).html(processor_descriptions[processor_id]).show();
        } else {
            Tygh.$('#elm_processor_description_' + payment_id).hide();
        }
    } else {
        Tygh.$('#elm_payment_tpl_' + payment_id).prop('disabled', false);
        Tygh.$('#elm_processor_description_' + payment_id).hide();
    }
}
</script>

<div class="items-container cm-sortable" data-ca-sortable-table="payments" data-ca-sortable-id-name="payment_id" id="payments_list">
{assign var="skip_delete" value=false}
{if $payments}
<table class="table table-middle table-objects table-striped">
    <tbody>
        {foreach from=$payments item=payment name="pf"}
            {if "ULTIMATE"|fn_allowed_for}
                {if $runtime.company_id && $runtime.company_id != $payment.company_id}
                    {assign var="skip_delete" value=true}
                    {assign var="hide_for_vendor" value=true}

                {else}
                    {assign var="skip_delete" value=false}
                    {assign var="hide_for_vendor" value=false}
                {/if}
            {/if}

            {include file="common/object_group.tpl"
                id=$payment.payment_id
                text=$payment.payment
                status=$payment.status
                href="payments.update?payment_id=`$payment.payment_id`"
                object_id_name="payment_id"
                table="payments"
                href_delete="payments.delete?payment_id=`$payment.payment_id`"
                delete_target_id="payments_list"
                skip_delete=$skip_delete
                header_text="{__("editing_payment")}: `$payment.payment`"
                additional_class="cm-sortable-row cm-sortable-id-`$payment.payment_id`"
                no_table=true
                draggable=true
            }
        {/foreach}
    </tbody>
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}
<!--payments_list--></div>
{/capture}

{capture name="buttons"}
    {capture name="tools_list"}
        {hook name="payments:manage_tools_list"}
        {/hook}
    {/capture}
    {dropdown content=$smarty.capture.tools_list}
{/capture}

{capture name="adv_buttons"}
    {capture name="add_new_picker"}
        {include file="views/payments/update.tpl" payment=[] hide_for_vendor=false}
    {/capture}
    {include file="common/popupbox.tpl" id="add_new_payments" text=__("new_payments") content=$smarty.capture.add_new_picker title=__("add_payment") act="general" icon="icon-plus"}
{/capture}

{include file="common/mainbox.tpl" title=__("payment_methods") content=$smarty.capture.mainbox select_languages=true buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons}
