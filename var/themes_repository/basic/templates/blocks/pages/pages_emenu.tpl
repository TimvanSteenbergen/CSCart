{** block-description:dropdown_vertical **}

<div class="clearfix">
    <ul id="vmenu_{$block.block_id}" class="dropdown dropdown-vertical">
        {include file="blocks/sidebox_dropdown.tpl" items=$items separated=true submenu=false name="page" item_id="page_id" childs="subpages"}
    </ul>
</div>
