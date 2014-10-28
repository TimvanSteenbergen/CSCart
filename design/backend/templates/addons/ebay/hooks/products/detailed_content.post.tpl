{if $ebay_templates}
{include file="common/subheader.tpl" title=__("ebay") target="#acc_ebay"}
<div id="acc_ebay" class="collapse in">
    <div class="control-group">
        <label class="control-label" for="elm_ebay_template_id">{__("ebay_template")}:</label>
        <div class="controls">
        <select class="span3" name="product_data[ebay_template_id]" id="elm_ebay_template_id">
            <option value="0">{__('select')}</option>
        {foreach from=$ebay_templates item="template"}
            <option value="{$template.template_id}" {if $product_data.ebay_template_id == $template.template_id || (empty($product_data.ebay_template_id) && $template.use_as_default == 'Y')}selected="selected"{/if}>{$template.name}</option>
        {/foreach}
        </select>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="elm_package_type">{__("package_type")}{include file="common/tooltip.tpl" tooltip={__("package_type_tooltip")}}:</label>
        <div class="controls">
        <select class="span3" name="product_data[package_type]" id="elm_package_type">
            <option {if  $product_data.package_type == 'Letter'}selected="selected"{/if} value="Letter">{__('Letter')}</option>
            <option {if  $product_data.package_type == 'LargeEnvelope'}selected="selected"{/if} value="LargeEnvelope">{__('large_envelope')}</option>
            <option {if  $product_data.package_type == 'PackageThickEnvelope'}selected="selected"{/if} value="PackageThickEnvelope">{__('ebay_package')}</option>
            <option {if  $product_data.package_type == 'ExtraLargePack'}selected="selected"{/if} value="ExtraLargePack">{__('large_package')}</option>
        </select>
        </div>
    </div>
    <div class="control-group" id="override" >
        <label for="elm_override" class="control-label">{__("override")}{include file="common/tooltip.tpl" tooltip={__("override_tooltip")}}:</label>
        <div class="controls">
            <input type="hidden" value="N" name="product_data[override]"/>
            <input type="checkbox" onclick="override();" id="elm_override" name="product_data[override]" class="cm-toggle-checkbox cm-no-hide-input" {if $product_data.override == 'Y'} checked="checked"{/if} value="Y" />
        </div>
    </div>
    <div class="control-group">
        <label for="elm_ebay_title" class="control-label {if $product_data.override == 'Y'}cm-required{/if}" id="ebay_title_req">{__("ebay_title")}{include file="common/tooltip.tpl" tooltip={__("ebay_title_tooltip")}}:</label>
        <div class="controls">
            <input type="text" name="product_data[ebay_title]" id="elm_ebay_title" size="55" {if !empty($product_data.ebay_title)} value="{$product_data.ebay_title}" {else} value="{$product_data.product}" {/if} class="input-large cm-no-hide-input" {if $product_data.override == 'N' || empty($product_data.override)} disabled="disabled" {/if}/>   
        </div>
    </div>

    <div class="control-group cm-no-hide-input">
        <label class="control-label" for="elm_ebay_full_descr">{__("ebay_description")}{include file="common/tooltip.tpl" tooltip={__("ebay_description_tooltip")}}:</label>
        <div class="controls">
            <textarea {if $product_data.override == 'N' || empty($product_data.override)}disabled="disabled"{/if} id="elm_ebay_full_descr" name="product_data[ebay_description]" cols="55" rows="8" class="cm-wysiwyg input-large cm-no-hide-input">
                {if !empty($product_data.ebay_description)}
                    {$product_data.ebay_description}
                {else}
                    {$product_data.full_description}
                {/if}
            </textarea>
        </div>
    </div>
</div>
{else}
    {__("ebay_templates_not_found")}
{/if}

<script type="text/javascript">
function override() {$ldelim}
    var $ = Tygh.$;
    if ($("#elm_override").is(":checked")) {
        $("#elm_ebay_title").removeAttr("disabled");
        $("#elm_ebay_full_descr").removeAttr("disabled");
        $("#elm_ebay_full_descr_overlay").remove();
        $(".redactor_box").unwrap();
        $("#ebay_title_req").addClass("cm-required");
    } else {
        if($("#elm_ebay_full_descr_overlay").length) {
            $("#elm_ebay_full_descr_overlay").addClass("disable-overlay");
        } else {
            $(".redactor_box").wrap("<div class='disable-overlay-wrap wysiwyg-overlay'></div>");
            $(".redactor_box").before("<div id='elm_ebay_full_descr_overlay' class='disable-overlay'></div>");
        }
        $("#ebay_title_req").removeClass("cm-required");
        $("#elm_ebay_title").attr("disabled","disabled");
    }
{$rdelim};
</script>
