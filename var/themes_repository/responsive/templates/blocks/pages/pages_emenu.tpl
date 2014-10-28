{** block-description:dropdown_vertical **} 

<div class="ty-menu ty-menu-vertical">
    <ul id="vmenu_{$block.block_id}" class="ty-menu__items cm-responsive-menu">
        {include file="blocks/sidebox_dropdown.tpl" items=$items separated=true submenu=false name="page" item_id="page_id" childs="subpages"}
    </ul>
</div>
