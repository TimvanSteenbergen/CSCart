<div class="ty-multi-checkout">
    <span class="ty-multi-checkout__step {if $edit_step == "step_one"} ty-multi-checkout__active{elseif $completed_steps.step_one == true} ty-multi-checkout__complete{/if}" data-ct-checkout="user_info">
        <span class="ty-multi-checkout__number">1</span>
        {if $edit_step != "step_one"}
            <a class="ty-multi-checkout__a" href="{"checkout.checkout?edit_step=step_one"|fn_url}">{__("user_info")}</a>
        {else}
            <span class="ty-multi-checkout__name">{__("user_info")}</span>
        {/if}
    </span>

    <i class="ty-multi-checkout__arrow ty-icon-right-thin"></i>

    <span class="ty-multi-checkout__step {if $edit_step == "step_two"} ty-multi-checkout__active{elseif $completed_steps.step_two == true} ty-multi-checkout__complete{/if}" data-ct-checkout="billing_shipping_address">
        <span class="ty-multi-checkout__number">2</span>
        {if $edit_step != "step_two"}
            <a class="ty-multi-checkout__a" href="{"checkout.checkout?edit_step=step_two"|fn_url}">{__("billing_shipping_address")}</a>
        {else}
            <span class="ty-multi-checkout__name">{__("billing_shipping_address")}</span>
        {/if}
    </span>

     <i class="ty-multi-checkout__arrow ty-icon-right-thin"></i>

    <span class="ty-multi-checkout__step {if $edit_step == "step_three"} ty-multi-checkout__active{elseif $completed_steps.step_three == true} ty-multi-checkout__complete{/if}" data-ct-checkout="shipping_options">
        <span class="ty-multi-checkout__number">3</span>
        {if $edit_step != "step_three"}
            <a class="ty-multi-checkout__a" href="{"checkout.checkout?edit_step=step_three"|fn_url}">{__("shipping_options")}</a>
        {else}
            <span class="ty-multi-checkout__name">{__("shipping_options")}</span>
        {/if}
    </span>

    <i class="ty-multi-checkout__arrow ty-icon-right-thin"></i>

    <span class="ty-multi-checkout__step {if $edit_step == "step_four"} ty-multi-checkout__active{elseif $completed_steps.step_four == true} ty-multi-checkout__complete{/if}" data-ct-checkout="billing_options">
        <span class="ty-multi-checkout__number">4</span>
        {if $edit_step != "step_four"}
            <a class="ty-multi-checkout__a" href="{"checkout.checkout?edit_step=step_four"|fn_url}">{__("billing_options")}</a>
        {else}
            <span class="ty-multi-checkout__name">{__("billing_options")}</span>
        {/if}
    </span>
</div>