{if $content|trim}
    <div class="sidebar-row">
        <h6>{$title}</h6>
        {$content|default:"&nbsp;" nofilter}
    </div>
    <hr />
{/if}