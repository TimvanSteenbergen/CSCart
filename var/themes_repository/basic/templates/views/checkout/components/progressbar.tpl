<div class="pb-container">
    <span class="{if $edit_step == "step_one"}active{elseif $completed_steps.step_one == true}complete{/if}" data-ct-checkout="user_info">
        <em>1</em>
        {if $edit_step != "step_one"}<a href="{"checkout.checkout?edit_step=step_one"|fn_url}">{/if}<span>{__("user_info")}</span>{if $edit_step != "step_one"}</a>{/if}
    </span>

    <i class="icon-right-thin"></i>

    <span class="{if $edit_step == "step_two"}active{elseif $completed_steps.step_two == true}complete{/if}" data-ct-checkout="billing_shipping_address">
        <em>2</em>
        {if $edit_step != "step_two"}<a href="{"checkout.checkout?edit_step=step_two"|fn_url}">{/if}<span>{__("billing_shipping_address")}</span>{if $edit_step != "step_two"}</a>{/if}
    </span>

    <i class="icon-right-thin"></i>

    <span class="{if $edit_step == "step_three"}active{elseif $completed_steps.step_three == true}complete{/if}" data-ct-checkout="shipping_options">
        <em>3</em>
        {if $edit_step != "step_three"}<a href="{"checkout.checkout?edit_step=step_three"|fn_url}">{/if}<span>{__("shipping_options")}</span>{if $edit_step != "step_three"}</a>{/if}
    </span>

    <i class="icon-right-thin"></i>

    <span class="{if $edit_step == "step_four"}active{elseif $completed_steps.step_four == true}complete{/if}" data-ct-checkout="billing_options">
        <em>4</em>
        {if $edit_step != "step_four"}<a href="{"checkout.checkout?edit_step=step_four"|fn_url}">{/if}<span>{__("billing_options")}</span>{if $edit_step != "step_four"}</a>{/if}
    </span>
</div>