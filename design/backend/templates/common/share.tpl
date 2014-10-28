{if $mode == "notification"}
    {__("share.congratulations_first_order")}
{/if}

{$url = ""|fn_url:"C"}
{$tweet_text = __("share.first_order_tweet", ["[product]" => $smarty.const.PRODUCT_NAME])}

<ul class="inline social-share">
    <li><a href="#" class="uibutton large confirm" onclick=" window.open('https://www.facebook.com/sharer/sharer.php?s=100&p[url]={$url}&p[images][0]={$logos.theme.image.http_image_path}&p[title]={if $mode == "notification"}{$tweet_text}{else}{__("share.installation_tweet", ['[product_name]' => $product_name])}{/if}', 'facebook-share-dialog', 'width=626,height=436'); return false;"> Share on Facebook</a></li>
    <li><a href="https://twitter.com/share" class="twitter-share-button" data-count="none" data-text="{if $mode == "notification"}{$tweet_text}{else}{__("share.installation_tweet", ['[product_name]' => $product_name])}{/if}" data-url="{$url}" data-via="{$config.resources.twitter}" data-size="large">Tweet</a>
        {literal}
            <script type="text/javascript">!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
        {/literal}</li>
</ul>
