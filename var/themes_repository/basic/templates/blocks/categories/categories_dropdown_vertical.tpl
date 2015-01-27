{** block-description:dropdown_vertical **}

<div class="clearfix">
    <ul id="vmenu_{$block.block_id}" class="dropdown dropdown-vertical{if $block.properties.right_to_left_orientation =="Y"} rtl{/if}">
        {include file="blocks/sidebox_dropdown.tpl" items=$items separated=true submenu=false name="category" item_id="category_id" childs="subcategories"}
    </ul>
</div>