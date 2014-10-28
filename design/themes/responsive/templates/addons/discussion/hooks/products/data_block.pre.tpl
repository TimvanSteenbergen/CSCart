{if $show_rating}

{if $product.average_rating}
    {$average_rating = $product.average_rating}
{elseif $product.discussion.average_rating}
    {$average_rating = $product.discussion.average_rating}
{/if}

{if $average_rating}
    {include file="addons/discussion/views/discussion/components/stars.tpl" stars=$average_rating|fn_get_discussion_rating is_link=true}
{/if}

{/if}