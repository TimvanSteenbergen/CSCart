<div class="search-result">
    <a href="{"news.view?news_id=`$n.news_id`#`$n.news_id`"|fn_url}" class="product-title">{$n.news}</a>
    <p>{__("date_added")}: {$n.date|date_format:"`$settings.Appearance.date_format`"}</p>
    <p>{$n.description|strip_tags|truncate:280:"..." nofilter}{if $n.description|strlen > 280}{/if}
    </p>
</div>