<ul class="entry-page-countries">
    <ul>
    {foreach name="countries" from=$countries key="code" item="url"}
        <li><a href="{$url}"><i class="flag flag-{$code|lower}"></i>{$country_descriptions.$code.country}</a></li>
    {/foreach}
    </ul>
</ul>