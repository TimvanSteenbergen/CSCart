{$render_placed = false}

<ul>
    {foreach from=$items item="item" name="it" key="item_id"}
        {assign var="forbidden" value=false}
        {if $item.type == "F"}
            {assign var="file_ext" value=""}
            {if $item.ext == "gif"}{assign var="file_ext" value="gif"}{/if}
            {if $item.ext == "jpg"}{assign var="file_ext" value="jpg"}{/if}
            {if $item.ext == "jpeg"}{assign var="file_ext" value="jpg"}{/if}
            {if $item.ext == "png"}{assign var="file_ext" value="png"}{/if}
            {if $item.ext == "html" || $item.ext == "htm"}{assign var="file_ext" value="html"}{/if}
            {if $item.ext == "tgz" || $item.ext == "zip" || $item.ext == "zip2" || $item.ext == "gz" || $item.ext == "bz" || $item.ext == "rar"}{assign var="file_ext" value="zip"}{/if}
            {if $item.ext == "tpl" || $item.ext == "txt"}{assign var="file_ext" value="tpl"}{/if}
            {if $item.ext == "php"}{assign var="file_ext" value="php"}{/if}
            {if $item.ext == "css"}{assign var="file_ext" value="css"}{/if}
            {if $item.ext == "js"}{assign var="file_ext" value="js"}{/if}
            {if $item.ext|in_array:$config.forbidden_file_extensions}{assign var="forbidden" value=true}{/if}
        {/if}
        {assign var="uniqid" value=10|uniqid}
        <li class="{if $item.name == $active_object}parent{/if} {if $last_object && $item.name == $active_object}active{/if}">
            <a data-ca-item-full-path="{$item.full_path}" data-ca-item-path="{$item.path}" data-ca-item-filename="{$item.name}" {if $item.ext}data-ca-item-ext="{$item.ext}"{/if} data-ca-item-type="{$item.type}" data-ca-item-perms="{$item.perms}" class="cm-te-file" id="file_id_{$uniqid}">
                <span class="overlay"></span>
                <span class="item">
                {if $item.type == "D"}
                    <i class="exicon-expand"></i>
                {elseif $file_ext == "gif" || $file_ext == "jpg" || $file_ext == "jpeg" || $file_ext == "png"}
                    <i class="icon-picture"></i>
                {else}
                    <i class="icon-file"></i>
                {/if}
                    <span>{$item.name}</span>
                </span>
            </a>

            {if $item.name == $active_object}
                {$render_placed = true}
                <!--render_place-->
            {/if}
        </li>
    {/foreach}

    {if !$render_placed} 
        <!--render_place-->
    {/if}
</ul>