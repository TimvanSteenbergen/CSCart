{if $settings.General.checkout_style != "multi_page"}
    {assign var="ajax_form" value="cm-ajax"}
{else}
    {assign var="ajax_form" value=""}
{/if}

{include file="views/profiles/components/profiles_scripts.tpl"}

<div class="checkout-steps cm-save-fields clearfix" id="checkout_steps">
{if $settings.General.checkout_style != "multi_page"}
    {if $edit_step == "step_one"}{$edit = true}{else}{$edit = false}{/if}
    {include file="views/checkout/components/steps/step_one.tpl" step="one" complete=$completed_steps.step_one edit=$edit but_text=__("continue")}

    {if $profile_fields.B || $profile_fields.S}
        {if $edit_step == "step_two"}{$edit = true}{else}{$edit = false}{/if}
        {include file="views/checkout/components/steps/step_two.tpl" step="two" complete=$completed_steps.step_two edit=$edit but_text=__("continue")}
    {/if}

    {if $edit_step == "step_three"}{$edit = true}{else}{$edit = false}{/if}
    {include file="views/checkout/components/steps/step_three.tpl" step="three" complete=$completed_steps.step_three edit=$edit but_text=__("continue")}

    {if $edit_step == "step_four"}{$edit = true}{else}{$edit = false}{/if}    
    {include file="views/checkout/components/steps/step_four.tpl" step="four" edit=$edit complete=$completed_steps.step_four}
{else}
    {$smarty.capture.checkout_error_content nofilter}
    
    {if $edit_step == "step_one"}
        {include file="views/checkout/components/steps/step_one.tpl" complete=$completed_steps.step_one edit=true but_text=__("continue")}
        
    {elseif $edit_step == "step_two"}
        {include file="views/checkout/components/steps/step_two.tpl" complete=$completed_steps.step_two edit=true but_text=__("continue")}
            
    {elseif $edit_step == "step_three"}
        {include file="views/checkout/components/steps/step_three.tpl" complete=$completed_steps.step_three edit=true but_text=__("continue")}
        
    {elseif $edit_step == "step_four"}
        {include file="views/checkout/components/steps/step_four.tpl" edit=true complete=$completed_steps.step_four}
    {/if}
{/if}
<!--checkout_steps--></div>