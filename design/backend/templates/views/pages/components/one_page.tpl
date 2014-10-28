<div class="search-result">
    <span>{$page.result_number}.</span> <a href="{"pages.update?page_id=`$page.page_id`"|fn_url}">{$page.page nofilter}</a>
    {assign var="more_link" value="pages.update?page_id=`$page.page_id`"|fn_url}
    {assign var="lang_more_link" value=__("more_link")}
    <p>{$page.description|strip_tags|truncate:280:"<a href=\"`$more_link`\" >`$lang_more_link`</a>" nofilter}</p>
</div>