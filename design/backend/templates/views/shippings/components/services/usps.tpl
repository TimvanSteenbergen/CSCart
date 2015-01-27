<fieldset>

{include file="common/subheader.tpl" title=__("general_info")}

<div class="control-group">
    <label class="control-label" for="ship_usps_username">{__("ship_usps_username")}</label>
    <div class="controls">
    <input id="ship_usps_username" type="text" name="shipping_data[service_params][username]" size="30" value="{$shipping.service_params.username}" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="test_mode">{__("test_mode")}</label>
    <div class="controls">
    <input type="hidden" name="shipping_data[service_params][test_mode]" value="N" />
    <input id="test_mode" type="checkbox" name="shipping_data[service_params][test_mode]" value="Y" {if $shipping.service_params.test_mode == "Y"}checked="checked"{/if}/>
    </div>
</div>

{include file="common/subheader.tpl" title=__("international_usps")}

<div class="control-group">
    <label class="control-label" for="ship_usps_mailtype">{__("ship_usps_mailtype")}</label>
    <div class="controls">
    <select id="ship_usps_mailtype" name="shipping_data[service_params][mailtype]">
        <option value="All" {if $shipping.service_params.mailtype == "All"}selected="selected"{/if}>{__("all")}</option>
        <option value="Package" {if $shipping.service_params.mailtype == "Package"}selected="selected"{/if}>{__("package")}</option>
        <option value="Postcards or aerogrammes" {if $shipping.service_params.mailtype == "Postcards or aerogrammes"}selected="selected"{/if}>{__("ship_usps_mailtype_postcards_or_aerogrammes")}</option>
        <option value="Matter for the blind" {if $shipping.service_params.mailtype == "Matter for the blind"}selected="selected"{/if}>{__("ship_usps_mailtype_matter_for_the_blind")}</option>
        <option value="Envelope" {if $shipping.service_params.mailtype == "Envelope"}selected="selected"{/if}>{__("envelope")}</option>
    </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_usps_container">{__("ship_usps_container")}</label>
    <div class="controls">
    <select id="ship_usps_container" name="shipping_data[service_params][container]">
        <option value="" {if $shipping.service_params.container == ""}selected="selected"{/if}>{__("none")}</option>
        <option value="RECTANGULAR" {if $shipping.service_params.container == "RECTANGULAR"}selected="selected"{/if}>{__("ship_usps_container_priority_rectangular")}</option>
        <option value="NONRECTANGULAR" {if $shipping.service_params.container == "NONRECTANGULAR"}selected="selected"{/if}>{__("ship_usps_container_priority_nonrectangular")}</option>
    </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_usps_intl_package_width">{__("ship_usps_intl_package_width")}</label>
    <div class="controls">
    <input id="ship_usps_intl_package_width" type="text" name="shipping_data[service_params][intl_package_width]" size="30" value="{$shipping.service_params.intl_package_width|default:0}" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_usps_intl_package_length">{__("ship_usps_intl_package_length")}</label>
    <div class="controls">
    <input id="ship_usps_intl_package_length" type="text" name="shipping_data[service_params][intl_package_length]" size="30" value="{$shipping.service_params.intl_package_length|default:0}"/>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_usps_intl_package_height">{__("ship_usps_intl_package_height")}</label>
    <div class="controls">
    <input id="ship_usps_intl_package_height" type="text" name="shipping_data[service_params][intl_package_height]" size="30" value="{$shipping.service_params.intl_package_height|default:0}" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_usps_intl_package_girth">{__("ship_usps_intl_package_girth")}</label>
    <div class="controls">
    <input id="ship_usps_intl_package_girth" type="text" name="shipping_data[service_params][intl_package_girth]" size="30" value="{$shipping.service_params.intl_package_girth|default:0}" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_usps_intl_package_size">{__("ship_usps_intl_package_size")}</label>
    <div class="controls">
    <select id="ship_usps_intl_package_size" name="shipping_data[service_params][intl_package_size]">
        <option value="REGULAR" {if $shipping.service_params.intl_package_size == "REGULAR"}selected="selected"{/if}>{__("usps_package_size_regular")}</option>
        <option value="LARGE" {if $shipping.service_params.intl_package_size == "LARGE"}selected="selected"{/if}>{__("usps_package_size_large")}</option>
    </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label">{__("extra_services")}</label>
    <div class="table-filters controls">
        <div class="scroll-y">
            <div class="select-field">
                <input type="hidden" name="shipping_data[service_params][intl_service_registered_mail]" value="N" />
                <label class="checkbox" for="intl_service_registered_mail">
                <input type="checkbox" {if $shipping.service_params.intl_service_registered_mail == "Y"}checked="checked"{/if} value="Y" name="shipping_data[service_params][intl_service_registered_mail]" id="intl_service_registered_mail">{__("usps_service_registered_mail")}</label>
            </div>
            <div class="select-field">
                <input type="hidden" name="shipping_data[service_params][intl_service_insurance]" value="N" />
                <label class="checkbox" for="intl_service_insurance">
                <input type="checkbox" {if $shipping.service_params.intl_service_insurance == "Y"}checked="checked"{/if} value="Y" name="shipping_data[service_params][intl_service_insurance]" id="intl_service_insurance" >{__("usps_service_insurance")}</label>
            </div>
            <div class="select-field">
                <input type="hidden" name="shipping_data[service_params][intl_service_return_receipt]" value="N" />
                <label class="checkbox" for="intl_service_return_receipt">
                <input type="checkbox" {if $shipping.service_params.intl_service_return_receipt == "Y"}checked="checked"{/if} value="Y" name="shipping_data[service_params][intl_service_return_receipt]" id="intl_service_return_receipt">{__("usps_service_return_receipt")}</label>
            </div>
            <div class="select-field">
                <input type="hidden" name="shipping_data[service_params][intl_service_pick_up_on_demand]" value="N" />
                <label class="checkbox" for="intl_service_pick_up_on_demand">
                <input type="checkbox" {if $shipping.service_params.intl_service_pick_up_on_demand == "Y"}checked="checked"{/if} value="Y" name="shipping_data[service_params][intl_service_pick_up_on_demand]" id="intl_service_pick_up_on_demand" >{__("usps_service_pick_up_on_demand")}</label>
            </div>
            <div class="select-field">
                <input type="hidden" name="shipping_data[service_params][intl_service_certificate_of_mailing]" value="N" />
                <label for="intl_service_certificate_of_mailing" class="checkbox">
                <input type="checkbox" {if $shipping.service_params.intl_service_certificate_of_mailing == "Y"}checked="checked"{/if} value="Y" name="shipping_data[service_params][intl_service_certificate_of_mailing]" id="intl_service_certificate_of_mailing">{__("usps_service_certificate_of_mailing")}</label>
            </div>
            <div class="select-field">
                <input type="hidden" name="shipping_data[service_params][intl_service_edelivery_confirmation]" value="N" />
                <label for="intl_service_edelivery_confirmation" class="checkbox">
                <input type="checkbox" {if $shipping.service_params.intl_service_edelivery_confirmation == "Y"}checked="checked"{/if} value="Y" name="shipping_data[service_params][intl_service_edelivery_confirmation]" id="intl_service_edelivery_confirmation">{__("usps_service_edelivery_confirmation")}</label>
            </div>
        </div>
    </div>
</div>

{include file="common/subheader.tpl" title=__("domestic_usps")}

<div class="control-group">
    <label class="control-label" for="ship_usps_package_size">{__("ship_usps_package_size")}</label>
    <div class="controls">
    <select id="ship_usps_package_size" name="shipping_data[service_params][package_size]">
        <option value="Regular" {if $shipping.service_params.package_size == "Regular"}selected="selected"{/if}>{__("ship_usps_package_size_regular")}</option>
        <option value="Large" {if $shipping.service_params.package_size == "Large"}selected="selected"{/if}>{__("ship_usps_package_size_large")}</option>
        <option value="Oversize" {if $shipping.service_params.package_size == "Oversize"}selected="selected"{/if}>{__("ship_usps_package_size_oversize")}</option>
    </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_usps_first_class_mail_type">{__("ship_usps_first_class_mail_type")}</label>
    <div class="controls">
    <select id="ship_usps_first_class_mail_type" name="shipping_data[service_params][first_class_mail_type]">
        <option value="LETTER" {if $shipping.service_params.first_class_mail_type == "LETTER"}selected="selected"{/if}>{__("letter")}</option>
        <option value="FLAT" {if $shipping.service_params.first_class_mail_type == "FLAT"}selected="selected"{/if}>{__("ship_usps_first_class_mail_type_flat")}</option>
        <option value="PARCEL" {if $shipping.service_params.first_class_mail_type == "PARCEL"}selected="selected"{/if}>{__("ship_usps_first_class_mail_type_parcel")}</option>
    </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_usps_machinable">{__("ship_usps_machinable")}</label>
    <div class="controls">
    <select id="ship_usps_machinable" name="shipping_data[service_params][machinable]">
        <option value="True" {if $shipping.service_params.machinable == "True"}selected="selected"{/if}>{__("ship_usps_machinable_true")}</option>
        <option value="False" {if $shipping.service_params.machinable == "False"}selected="selected"{/if}>{__("ship_usps_machinable_false")}</option>
    </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_usps_container_priority">{__("ship_usps_container_priority")}</label>
    <div class="controls">
    <select id="ship_usps_container_priority" name="shipping_data[service_params][container_priority]">
        <option value="" {if $shipping.service_params.container_priority == ""}selected="selected"{/if}>{__("none")}</option>
        <option value="Flat Rate Envelope" {if $shipping.service_params.container_priority == "Flat Rate Envelope"}selected="selected"{/if}>{__("ship_usps_container_priority_flat_rate_envelope")}</option>
        <option value="Padded Flat Rate Envelope" {if $shipping.service_params.container_priority == "Padded Flat Rate Envelope"}selected="selected"{/if}>{__("ship_usps_container_priority_padded_flat_rate_envelope")}</option>
        <option value="Legal Flat Rate Envelope" {if $shipping.service_params.container_priority == "Legal Flat Rate Envelope"}selected="selected"{/if}>{__("ship_usps_container_priority_legal_flat_rate_envelope")}</option>
        <option value="Sm Flat Rate Envelope" {if $shipping.service_params.container_priority == "Sm Flat Rate Envelope"}selected="selected"{/if}>{__("ship_usps_container_priority_sm_flat_rate_envelope")}</option>
        <option value="Window Flat Rate Envelope" {if $shipping.service_params.container_priority == "Window Flat Rate Envelope"}selected="selected"{/if}>{__("ship_usps_container_priority_window_flat_rate_envelope")}</option>
        <option value="Gift Card Flat Rate Envelope" {if $shipping.service_params.container_priority == "Gift Card Flat Rate Envelope"}selected="selected"{/if}>{__("ship_usps_container_priority_gift_card_flat_rate_envelope")}</option>
        <option value="Flat Rate Box" {if $shipping.service_params.container_priority == "Flat Rate Box"}selected="selected"{/if}>{__("ship_usps_container_priority_flat_rate_box")}</option>
        <option value="Rectangular" {if $shipping.service_params.container_priority == "Rectangular"}selected="selected"{/if}>{__("ship_usps_container_priority_rectangular")}</option>
        <option value="NonRectangular" {if $shipping.service_params.container_priority == "NonRectangular"}selected="selected"{/if}>{__("ship_usps_container_priority_nonrectangular")}</option>
        <option value="SM FLAT RATE BOX" {if $shipping.service_params.container_priority == "SM FLAT RATE BOX"}selected="selected"{/if}>{__("ship_usps_container_priority_sm_flat_rate_box")}</option>
        <option value="MD FLAT RATE BOX" {if $shipping.service_params.container_priority == "MD FLAT RATE BOX"}selected="selected"{/if}>{__("ship_usps_container_priority_md_flat_rate_box")}</option>
        <option value="LG FLAT RATE BOX" {if $shipping.service_params.container_priority == "LG FLAT RATE BOX"}selected="selected"{/if}>{__("ship_usps_container_priority_lg_flat_rate_box")}</option>
        <option value="REGIONALRATEBOXA" {if $shipping.service_params.container_priority == "REGIONALRATEBOXA"}selected="selected"{/if}>{__("ship_usps_container_priority_regional_a_rate_box")}</option>
        <option value="REGIONALRATEBOXB" {if $shipping.service_params.container_priority == "REGIONALRATEBOXB"}selected="selected"{/if}>{__("ship_usps_container_priority_regional_b_rate_box")}</option>
        <option value="REGIONALRATEBOXC" {if $shipping.service_params.container_priority == "REGIONALRATEBOXC"}selected="selected"{/if}>{__("ship_usps_container_priority_regional_C_rate_box")}</option>
    </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_usps_container_express">{__("ship_usps_container_express")}</label>
    <div class="controls">
    <select id="ship_usps_container_express" name="shipping_data[service_params][container_express]">
        <option value="" {if $shipping.service_params.container_express == ""}selected="selected"{/if}>{__("none")}</option>
        <option value="Flat Rate Envelope" {if $shipping.service_params.container_express == "Flat Rate Envelope"}selected="selected"{/if}>{__("ship_usps_container_express_flat_rate_envelope")}</option>
    </select>
    </div>
</div>

<blockquote>
    <small>{__("usps_size")}</small>
</blockquote>

<div class="control-group">
    <label class="control-label" for="ship_usps_priority_width">{__("ship_usps_priority_width")}</label>
    <div class="controls">
    <input id="ship_usps_priority_width" type="text" name="shipping_data[service_params][priority_width]" size="30" value="{$shipping.service_params.priority_width}" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_usps_priority_length">{__("ship_usps_priority_length")}</label>
    <div class="controls">
    <input id="ship_usps_priority_length" type="text" name="shipping_data[service_params][priority_length]" size="30" value="{$shipping.service_params.priority_length}" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_usps_priority_height">{__("ship_usps_priority_height")}</label>
    <div class="controls">
    <input id="ship_usps_priority_height" type="text" name="shipping_data[service_params][priority_height]" size="30" value="{$shipping.service_params.priority_height}" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_usps_priority_girth">{__("ship_usps_priority_girth")}</label>
    <div class="controls">
    <input id="ship_usps_priority_girth" type="text" name="shipping_data[service_params][priority_girth]" size="30" value="{$shipping.service_params.priority_girth}" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ground_only">{__("ground_only")}</label>
    <div class="controls">
    <input type="hidden" name="shipping_data[service_params][ground_only]" value="N" />
    <input id="ground_only" type="checkbox" name="shipping_data[service_params][ground_only]" value="Y" {if $shipping.service_params.ground_only == "Y"}checked="checked"{/if}/>
    </div>
</div>

<div class="control-group">
    <label class="control-label">{__("extra_services")}</label>
    <div class="table-filters controls">
        <div class="scroll-y">
            <div class="select-field">
                <input type="hidden" name="shipping_data[service_params][domestic_service_certified]" value="N" />
                <label class="checkbox" for="domestic_service_certified">
                <input type="checkbox" {if $shipping.service_params.domestic_service_certified == "Y"}checked="checked"{/if} value="Y" name="shipping_data[service_params][domestic_service_certified]" id="domestic_service_certified">{__("usps_service_certified")}</label>
            </div>
            <div class="select-field">
                <input type="hidden" name="shipping_data[service_params][domestic_service_insurance]" value="N" />
                <label class="checkbox" for="domestic_service_insurance">
                <input type="checkbox" {if $shipping.service_params.domestic_service_insurance == "Y"}checked="checked"{/if} value="Y" name="shipping_data[service_params][domestic_service_insurance]" id="domestic_service_insurance" >{__("usps_service_insurance")}</label>
            </div>
            <div class="select-field">
                <input type="hidden" name="shipping_data[service_params][domestic_service_registered_without_insurance]" value="N" />
                <label class="checkbox" for="domestic_service_registered_without_insurance">
                <input type="checkbox" {if $shipping.service_params.domestic_service_registered_without_insurance == "Y"}checked="checked"{/if} value="Y" name="shipping_data[service_params][domestic_service_registered_without_insurance]" id="domestic_service_registered_without_insurance">{__("usps_service_registered_without_insurance")}</label>
            </div>
            <div class="select-field">
                <input type="hidden" name="shipping_data[service_params][domestic_service_registered_with_insurance]" value="N" />
                <label class="checkbox" for="domestic_service_registered_with_insurance">
                <input type="checkbox" {if $shipping.service_params.domestic_service_registered_with_insurance == "Y"}checked="checked"{/if} value="Y" name="shipping_data[service_params][domestic_service_registered_with_insurance]" id="domestic_service_registered_with_insurance">{__("usps_service_registered_with_insurance")}</label>
            </div>
            <div class="select-field">
                <input type="hidden" name="shipping_data[service_params][domestic_service_collect_on_delivery]" value="N" />
                <label class="checkbox" for="domestic_service_collect_on_delivery">
                <input type="checkbox" {if $shipping.service_params.domestic_service_collect_on_delivery == "Y"}checked="checked"{/if} value="Y" name="shipping_data[service_params][domestic_service_collect_on_delivery]" id="domestic_service_collect_on_delivery" >{__("usps_service_collect_on_delivery")}</label>
            </div>
            <div class="select-field">
                <input type="hidden" name="shipping_data[service_params][domestic_service_return_receipt_for_merchandise]" value="N" />
                <label class="checkbox" for="domestic_service_return_receipt_for_merchandise">
                <input type="checkbox" {if $shipping.service_params.domestic_service_return_receipt_for_merchandise == "Y"}checked="checked"{/if} value="Y" name="shipping_data[service_params][domestic_service_return_receipt_for_merchandise]" id="domestic_service_return_receipt_for_merchandise">{__("usps_service_return_receipt_for_merchandise")}</label>
            </div>
            <div class="select-field">
                <input type="hidden" name="shipping_data[service_params][domestic_service_return_receipt]" value="N" />
                <label class="checkbox" for="domestic_service_return_receipt">
                <input type="checkbox" {if $shipping.service_params.domestic_service_return_receipt == "Y"}checked="checked"{/if} value="Y" name="shipping_data[service_params][domestic_service_return_receipt]" id="domestic_service_return_receipt">{__("usps_service_return_receipt")}</label>
            </div>
            <div class="select-field">
                <input type="hidden" name="shipping_data[service_params][domestic_service_certificate_of_mailing_per_individual_article]" value="N" />
                <label class="checkbox" for="domestic_service_certificate_of_mailing_per_individual_article">
                <input type="checkbox" {if $shipping.service_params.domestic_service_certificate_of_mailing_per_individual_article == "Y"}checked="checked"{/if} value="Y" name="shipping_data[service_params][domestic_service_certificate_of_mailing_per_individual_article]" id="domestic_service_certificate_of_mailing_per_individual_article" >{__("usps_service_certificate_of_mailing_per_individual_article")}</label>
            </div>
            <div class="select-field">
                <input type="hidden" name="shipping_data[service_params][domestic_service_certificate_of_mailing_for_firm_mailing_books]" value="N" />
                <label class="checkbox" for="domestic_service_certificate_of_mailing_for_firm_mailing_books">
                <input type="checkbox" {if $shipping.service_params.domestic_service_certificate_of_mailing_for_firm_mailing_books == "Y"}checked="checked"{/if} value="Y" name="shipping_data[service_params][domestic_service_certificate_of_mailing_for_firm_mailing_books]" id="domestic_service_certificate_of_mailing_for_firm_mailing_books">{__("usps_service_certificate_of_mailing_for_firm_mailing_books")}</label>
            </div>
            <div class="select-field">
                <input type="hidden" name="shipping_data[service_params][domestic_service_express_mail_insurance]" value="N" />
                <label class="checkbox" for="domestic_service_express_mail_insurance">
                <input type="checkbox" {if $shipping.service_params.domestic_service_express_mail_insurance == "Y"}checked="checked"{/if} value="Y" name="shipping_data[service_params][domestic_service_express_mail_insurance]" id="domestic_service_express_mail_insurance" >{__("usps_service_express_mail_insurance")}</label>
            </div>
            <div class="select-field">
                <input type="hidden" name="shipping_data[service_params][domestic_service_delivery_confirmation]" value="N" />
                <label class="checkbox" for="domestic_service_delivery_confirmation">
                <input type="checkbox" {if $shipping.service_params.domestic_service_delivery_confirmation == "Y"}checked="checked"{/if} value="Y" name="shipping_data[service_params][domestic_service_delivery_confirmation]" id="domestic_service_delivery_confirmation">{__("usps_service_delivery_confirmation")}</label>
            </div>
            <div class="select-field">
                <input type="hidden" name="shipping_data[service_params][domestic_service_signature_confirmation]" value="N" />
                <label  class="checkbox" for="domestic_service_signature_confirmation">
                <input type="checkbox" {if $shipping.service_params.domestic_service_signature_confirmation == "Y"}checked="checked"{/if} value="Y" name="shipping_data[service_params][domestic_service_signature_confirmation]" id="domestic_service_signature_confirmation" >{__("usps_service_signature_confirmation")}</label>
            </div>
            <div class="select-field">
                <input type="hidden" name="shipping_data[service_params][domestic_service_return_receipt_electronic]" value="N" />
                <label class="checkbox" for="domestic_service_return_receipt_electronic">
                <input type="checkbox" {if $shipping.service_params.domestic_service_return_receipt_electronic == "Y"}checked="checked"{/if} value="Y" name="shipping_data[service_params][domestic_service_return_receipt_electronic]" id="domestic_service_return_receipt_electronic" >{__("usps_service_return_receipt_electronic")}</label>
            </div>
        </div>
    </div>
</div>

</fieldset>
