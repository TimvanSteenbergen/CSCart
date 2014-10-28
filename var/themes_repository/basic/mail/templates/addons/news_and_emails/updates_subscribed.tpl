{include file="common/letter_header.tpl"}

{__("text_success_subscription")}<br />
<br />
<br />
{__("text_unsubscribe_instructions")}<br />
<a href="{"news.unsubscribe?key=`$unsubscribe_key`"|fn_url:'C':'http'}">{"news.unsubscribe?key=`$unsubscribe_key`"|fn_url:'C':'http'}</a>

{include file="common/letter_footer.tpl"}
