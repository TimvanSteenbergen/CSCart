<fieldset>

{include file="common/subheader.tpl" title=__("international_settings")}

<div class="control-group">
    <label class="control-label" for="ship_sp_ur_additional_insurance">{__("ship_sp_ur_additional_insurance")}</label>
    <div class="controls">
        <input type="hidden" name="shipping_data[service_params][ur_additional_insurance]" value="N" />
        <input id="ship_sp_ur_additional_insurance" type="checkbox" name="shipping_data[service_params][ur_additional_insurance]" value="Y" {if $shipping.service_params.ur_additional_insurance == "Y"}checked="checked"{/if} />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_sp_l_registered_mail">{__("ship_sp_l_registered_mail")}</label>
    <div class="controls">
        <input type="hidden" name="shipping_data[service_params][l_registered_mail]" value="N" />
        <input id="ship_sp_l_registered_mail" type="checkbox" name="shipping_data[service_params][l_registered_mail]" value="Y" {if $shipping.service_params.l_registered_mail == "Y"}checked="checked"{/if} />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_sp_l_acknowledgement_of_delivery">{__("ship_sp_l_acknowledgement_of_delivery")}</label>
    <div class="controls">
        <input type="hidden" name="shipping_data[service_params][l_acknowledgement_of_delivery]" value="N" />
        <input id="ship_sp_l_acknowledgement_of_delivery" type="checkbox" name="shipping_data[service_params][l_acknowledgement_of_delivery]" value="Y" {if $shipping.service_params.l_acknowledgement_of_delivery == "Y"}checked="checked"{/if} />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_sp_l_personal_delivery">{__("ship_sp_l_personal_delivery")}</label>
    <div class="controls">
        <input type="hidden" name="shipping_data[service_params][l_personal_delivery]" value="N" />
        <input id="ship_sp_l_personal_delivery" type="checkbox" name="shipping_data[service_params][l_personal_delivery]" value="Y" {if $shipping.service_params.l_personal_delivery == "Y"}checked="checked"{/if} />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_sp_l_cash_on_delivery">{__("ship_sp_l_cash_on_delivery")}</label>
    <div class="controls">
        <input type="hidden" name="shipping_data[service_params][l_cash_on_delivery]" value="N" />
        <input id="ship_sp_l_cash_on_delivery" type="checkbox" name="shipping_data[service_params][l_cash_on_delivery]" value="Y" {if $shipping.service_params.l_cash_on_delivery == "Y"}checked="checked"{/if} />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_sp_pp_additional_insurance">{__("ship_sp_pp_additional_insurance")}</label>
    <div class="controls">
        <input type="hidden" name="shipping_data[service_params][pp_additional_insurance]" value="N" />
        <input id="ship_sp_pp_additional_insurance" type="checkbox" name="shipping_data[service_params][pp_additional_insurance]" value="Y" {if $shipping.service_params.pp_additional_insurance == "Y"}checked="checked"{/if} />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_sp_pp_bulky_goods">{__("ship_sp_pp_bulky_goods")}</label>
    <div class="controls">
        <input type="hidden" name="shipping_data[service_params][pp_bulky_goods]" value="N" />
        <input id="ship_sp_pp_bulky_goods" type="checkbox" name="shipping_data[service_params][pp_bulky_goods]" value="Y" {if $shipping.service_params.pp_bulky_goods == "Y"}checked="checked"{/if} />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_sp_pp_cash_on_delivery">{__("ship_sp_pp_cash_on_delivery")}</label>
    <div class="controls">
        <input type="hidden" name="shipping_data[service_params][pp_cash_on_delivery]" value="N" />
        <input id="ship_sp_pp_cash_on_delivery" type="checkbox" name="shipping_data[service_params][pp_cash_on_delivery]" value="Y" {if $shipping.service_params.pp_cash_on_delivery == "Y"}checked="checked"{/if} />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_sp_pp_manual_processing">{__("ship_sp_pp_manual_processing")}</label>
    <div class="controls">
        <input type="hidden" name="shipping_data[service_params][pp_manual_processing]" value="N" />
        <input id="ship_sp_pp_manual_processing" type="checkbox" name="shipping_data[service_params][pp_manual_processing]" value="Y" {if $shipping.service_params.pp_manual_processing == "Y"}checked="checked"{/if} />
    </div>
</div>

{include file="common/subheader.tpl" title=__("private_customer_settings")}

<div class="control-group">
    <label class="control-label" for="ship_sp_pc_manual_handling">{__("ship_sp_pc_manual_handling")}</label>
    <div class="controls">
        <input type="hidden" name="shipping_data[service_params][pc_manual_handling]" value="N" />
        <input id="ship_sp_pc_manual_handling" type="checkbox" name="shipping_data[service_params][pc_manual_handling]" value="Y" {if $shipping.service_params.pc_manual_handling == "Y"}checked="checked"{/if} />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_sp_pc_fragile">{__("ship_sp_pc_fragile")}</label>
    <div class="controls">
        <input type="hidden" name="shipping_data[service_params][pc_fragile]" value="N" />
        <input id="ship_sp_pc_fragile" type="checkbox" name="shipping_data[service_params][pc_fragile]" value="Y" {if $shipping.service_params.pc_fragile == "Y"}checked="checked"{/if} />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_sp_pc_signature">{__("ship_sp_pc_signature")}</label>
    <div class="controls">
        <input type="hidden" name="shipping_data[service_params][pc_signature]" value="N" />
        <input id="ship_sp_pc_signature" type="checkbox" name="shipping_data[service_params][pc_signature]" value="Y" {if $shipping.service_params.pc_signature == "Y"}checked="checked"{/if} />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_sp_pc_assurance">{__("ship_sp_pc_assurance")}</label>
    <div class="controls">
        <input type="hidden" name="shipping_data[service_params][pc_assurance]" value="N" />
        <input id="ship_sp_pc_assurance" type="checkbox" name="shipping_data[service_params][pc_assurance]" value="Y" {if $shipping.service_params.pc_assurance == "Y"}checked="checked"{/if}/>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_sp_pc_personal">{__("ship_sp_pc_personal")}</label>
    <div class="controls">
        <input type="hidden" name="shipping_data[service_params][pc_personal]" value="N" />
        <input id="ship_sp_pc_personal" type="checkbox" name="shipping_data[service_params][pc_personal]" value="Y" {if $shipping.service_params.pc_personal == "Y"}checked="checked"{/if} />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_sp_pc_cash_on_delivery">{__("ship_sp_pc_cash_on_delivery")}</label>
    <div class="controls">
        <input type="hidden" name="shipping_data[service_params][pc_cash_on_delivery]" value="N" />
        <input id="ship_sp_pc_cash_on_delivery" type="checkbox" name="shipping_data[service_params][pc_cash_on_delivery]" value="Y" {if $shipping.service_params.pc_cash_on_delivery == "Y"}checked="checked"{/if} />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="max_weight">{__("max_box_weight")}</label>
    <div class="controls">
        <input id="max_weight" type="text" name="shipping_data[service_params][max_weight_of_box]" size="30" value="{$shipping.service_params.max_weight_of_box|default:0}" />
    </div>
</div>

</fieldset>