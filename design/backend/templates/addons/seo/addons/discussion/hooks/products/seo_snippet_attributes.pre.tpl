{if $discussion && $discussion.average_rating}

{$stars = $discussion.average_rating|fn_get_discussion_rating}
<p class="nowrap gstars">
    {section name="full_star" loop=$stars.full}<i class="gicon-star"></i>{/section}
    {if $stars.part}<i class="gicon-star-half"></i>{/if}
    {section name="full_star" loop=$stars.empty}<i class="gicon-star-empty"></i>{/section}
</p>
&nbsp;{__("seo.rich_snippets_rating")}: {$discussion.average_rating} - {__("seo.rich_snippets_reviews", [$discussion.search.total_items])} - {/if}‎