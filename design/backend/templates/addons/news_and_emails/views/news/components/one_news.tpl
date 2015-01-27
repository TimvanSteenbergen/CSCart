<div class="control-group">
    <span>{$n.result_number}.</span> <a href="{"news.update?news_id=`$n.news_id`#`$n.news_id`"|fn_url}" class="list-product-title">{$n.news nofilter}</a>
    
    <p>
    {__("date_added")}: {$n.date|date_format:"`$settings.Appearance.date_format`"}<br />
    {assign var="news_link" value="news.update?news_id=`$n.news_id`#`$n.news_id`"|fn_url}
    {assign var="more_link" value=__("more_link")}
    {$n.description|strip_tags|truncate:280:"<a href=\"`$news_link`\" class=\"underlined\">`$more_link`</a>" nofilter}</p>
</div>