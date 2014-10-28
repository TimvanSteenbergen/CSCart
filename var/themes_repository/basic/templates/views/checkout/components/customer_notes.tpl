{hook name="checkout:notes"}
<div class="customer-notes">
    <p class="strong">{__("type_comments_here")}</p>
    <textarea class="input-textarea checkout-textarea" name="customer_notes" cols="60" rows="3">{$cart.notes}</textarea>
</div>
{/hook}