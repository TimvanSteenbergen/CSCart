<?php /* Smarty version Smarty-3.1.18, created on 2014-10-27 16:10:19
         compiled from "/var/www/html/workspace/cscart/design/backend/templates/common/mainbox.tpl" */ ?>
<?php /*%%SmartyHeaderCode:1039358099544e362b590aa0-50493578%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '1d6d65b5673a99fbb21a03de71b663a2b739a756' => 
    array (
      0 => '/var/www/html/workspace/cscart/design/backend/templates/common/mainbox.tpl',
      1 => 1413383301,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '1039358099544e362b590aa0-50493578',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'sidebar_position' => 0,
    'anchor' => 0,
    'data' => 0,
    'sticky_scroll' => 0,
    'sticky_padding' => 0,
    'no_sidebar' => 0,
    'title_alt' => 0,
    'title' => 0,
    'main_buttons_meta' => 0,
    'content_id' => 0,
    'adv_buttons' => 0,
    'navigation' => 0,
    'm' => 0,
    'buttons' => 0,
    's_id' => 0,
    'sidebar' => 0,
    'notes' => 0,
    'note' => 0,
    'sidebar_content' => 0,
    'box_id' => 0,
    'select_languages' => 0,
    'languages' => 0,
    'config' => 0,
    'tools' => 0,
    'title_extra' => 0,
    'extra_tools' => 0,
    'content' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_544e362b645917_90031017',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_544e362b645917_90031017')) {function content_544e362b645917_90031017($_smarty_tpl) {?><?php if (!is_callable('smarty_block_hook')) include '/var/www/html/workspace/cscart/app/functions/smarty_plugins/block.hook.php';
if (!is_callable('smarty_block_notes')) include '/var/www/html/workspace/cscart/app/functions/smarty_plugins/block.notes.php';
?><?php
fn_preload_lang_vars(array('choose_action','notes','language'));
?>
<?php if (defined("THEMES_PANEL")) {?>
    <?php $_smarty_tpl->tpl_vars['sticky_scroll'] = new Smarty_variable(5, null, 0);?>
    <?php $_smarty_tpl->tpl_vars['sticky_padding'] = new Smarty_variable(73, null, 0);?>
<?php } else { ?>
    <?php $_smarty_tpl->tpl_vars['sticky_scroll'] = new Smarty_variable(41, null, 0);?>
    <?php $_smarty_tpl->tpl_vars['sticky_padding'] = new Smarty_variable(37, null, 0);?>
<?php }?>

<?php if (!$_smarty_tpl->tpl_vars['sidebar_position']->value) {?>
    <?php $_smarty_tpl->tpl_vars['sidebar_position'] = new Smarty_variable("right", null, 0);?>
<?php }?>

<?php if ($_smarty_tpl->tpl_vars['anchor']->value) {?>
<a name="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['anchor']->value, ENT_QUOTES, 'UTF-8');?>
"></a>
<?php }?>

<script type="text/javascript">
// Init ajax callback (rebuild)
var menu_content = <?php echo (($tmp = @htmlspecialchars_decode($_smarty_tpl->tpl_vars['data']->value, ENT_QUOTES))===null||$tmp==='' ? "''" : $tmp);?>
;
</script>

<!-- Actions -->
<div class="actions cm-sticky-scroll" data-ce-top="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['sticky_scroll']->value, ENT_QUOTES, 'UTF-8');?>
" data-ce-padding="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['sticky_padding']->value, ENT_QUOTES, 'UTF-8');?>
" id="actions_panel">
    <?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"index:actions")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"index:actions"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

    <?php if (!$_smarty_tpl->tpl_vars['no_sidebar']->value) {?>
        <div class="btn-bar-left pull-left">
            <div class="pull-left"><?php echo $_smarty_tpl->getSubTemplate ("common/last_viewed_items.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>
</div>
        </div>
    <?php }?>
    <div class="title pull-left">
        <h2 title="<?php echo htmlspecialchars(preg_replace('!\s+!u', ' ',preg_replace('!<[^>]*?>!', ' ', (($tmp = @$_smarty_tpl->tpl_vars['title_alt']->value)===null||$tmp==='' ? $_smarty_tpl->tpl_vars['title']->value : $tmp))), ENT_QUOTES, 'UTF-8');?>
"><?php echo (($tmp = @$_smarty_tpl->tpl_vars['title']->value)===null||$tmp==='' ? "&nbsp;" : $tmp);?>
</h2>
    </div>
    <div class="<?php if (isset($_smarty_tpl->tpl_vars['main_buttons_meta']->value)) {?><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['main_buttons_meta']->value, ENT_QUOTES, 'UTF-8');?>
<?php } else { ?>btn-bar btn-toolbar<?php }?> dropleft pull-right" <?php if ($_smarty_tpl->tpl_vars['content_id']->value) {?>id="tools_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['content_id']->value, ENT_QUOTES, 'UTF-8');?>
_buttons"<?php }?>>
        
        <?php if ($_smarty_tpl->tpl_vars['adv_buttons']->value) {?>
        <div class="adv-buttons" <?php if ($_smarty_tpl->tpl_vars['content_id']->value) {?>id="tools_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['content_id']->value, ENT_QUOTES, 'UTF-8');?>
_adv_buttons"<?php }?>>
        <?php echo $_smarty_tpl->tpl_vars['adv_buttons']->value;?>

        <?php if ($_smarty_tpl->tpl_vars['content_id']->value) {?><!--tools_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['content_id']->value, ENT_QUOTES, 'UTF-8');?>
_adv_buttons--><?php }?></div>
        <?php }?>
        
        <?php if ($_smarty_tpl->tpl_vars['navigation']->value['dynamic']['actions']) {?>
            <?php $_smarty_tpl->_capture_stack[0][] = array("tools_list", null, null); ob_start(); ?>
                <?php  $_smarty_tpl->tpl_vars['m'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['m']->_loop = false;
 $_smarty_tpl->tpl_vars['title'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['navigation']->value['dynamic']['actions']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['m']->key => $_smarty_tpl->tpl_vars['m']->value) {
$_smarty_tpl->tpl_vars['m']->_loop = true;
 $_smarty_tpl->tpl_vars['title']->value = $_smarty_tpl->tpl_vars['m']->key;
?>
                    <li><a href="<?php echo htmlspecialchars(fn_url($_smarty_tpl->tpl_vars['m']->value['href']), ENT_QUOTES, 'UTF-8');?>
" class="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['m']->value['meta'], ENT_QUOTES, 'UTF-8');?>
" target="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['m']->value['target'], ENT_QUOTES, 'UTF-8');?>
"><?php echo $_smarty_tpl->__($_smarty_tpl->tpl_vars['title']->value);?>
</a></li>
                <?php } ?>
            <?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>
            <?php echo $_smarty_tpl->getSubTemplate ("common/tools.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('hide_actions'=>true,'tools_list'=>Smarty::$_smarty_vars['capture']['tools_list'],'link_text'=>__("choose_action")), 0);?>

        <?php }?>

        <?php echo $_smarty_tpl->tpl_vars['buttons']->value;?>

    <?php if ($_smarty_tpl->tpl_vars['content_id']->value) {?><!--tools_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['content_id']->value, ENT_QUOTES, 'UTF-8');?>
_buttons--><?php }?></div>
    <?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"index:actions"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

<!--actions_panel--></div>

<?php $_smarty_tpl->_capture_stack[0][] = array("sidebar_content", "sidebar_content", null); ob_start(); ?>
    <?php if ($_smarty_tpl->tpl_vars['navigation']->value&&$_smarty_tpl->tpl_vars['navigation']->value['dynamic']['sections']) {?>
        <div class="sidebar-row">
            <ul class="nav nav-list">
                <?php  $_smarty_tpl->tpl_vars['m'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['m']->_loop = false;
 $_smarty_tpl->tpl_vars["s_id"] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['navigation']->value['dynamic']['sections']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['m']->total= $_smarty_tpl->_count($_from);
 $_smarty_tpl->tpl_vars['m']->iteration=0;
foreach ($_from as $_smarty_tpl->tpl_vars['m']->key => $_smarty_tpl->tpl_vars['m']->value) {
$_smarty_tpl->tpl_vars['m']->_loop = true;
 $_smarty_tpl->tpl_vars["s_id"]->value = $_smarty_tpl->tpl_vars['m']->key;
 $_smarty_tpl->tpl_vars['m']->iteration++;
 $_smarty_tpl->tpl_vars['m']->last = $_smarty_tpl->tpl_vars['m']->iteration === $_smarty_tpl->tpl_vars['m']->total;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']["first_level"]['last'] = $_smarty_tpl->tpl_vars['m']->last;
?>
                    <?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"index:dynamic_menu_item")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"index:dynamic_menu_item"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

                        <?php if ($_smarty_tpl->tpl_vars['m']->value['type']=="divider") {?>
                            <li class="divider"></li>
                            <?php } else { ?>
                            <li class="<?php if ($_smarty_tpl->tpl_vars['m']->value['js']==true) {?>cm-js<?php }?><?php if ($_smarty_tpl->getVariable('smarty')->value['foreach']['first_level']['last']) {?> last-item<?php }?><?php if ($_smarty_tpl->tpl_vars['navigation']->value['dynamic']['active_section']==$_smarty_tpl->tpl_vars['s_id']->value) {?> active<?php }?>"><a href="<?php echo htmlspecialchars(fn_url($_smarty_tpl->tpl_vars['m']->value['href']), ENT_QUOTES, 'UTF-8');?>
"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['m']->value['title'], ENT_QUOTES, 'UTF-8');?>
</a></li>
                        <?php }?>
                    <?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"index:dynamic_menu_item"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

                <?php } ?>
            </ul>
        </div>
    <hr>
    <?php }?>
    <?php echo $_smarty_tpl->tpl_vars['sidebar']->value;?>


    <?php $_smarty_tpl->smarty->_tag_stack[] = array('notes', array('assign'=>"notes")); $_block_repeat=true; echo smarty_block_notes(array('assign'=>"notes"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>
<?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_notes(array('assign'=>"notes"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

    <?php if ($_smarty_tpl->tpl_vars['notes']->value) {?>
        <?php  $_smarty_tpl->tpl_vars["note"] = new Smarty_Variable; $_smarty_tpl->tpl_vars["note"]->_loop = false;
 $_smarty_tpl->tpl_vars["title"] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['notes']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars["note"]->key => $_smarty_tpl->tpl_vars["note"]->value) {
$_smarty_tpl->tpl_vars["note"]->_loop = true;
 $_smarty_tpl->tpl_vars["title"]->value = $_smarty_tpl->tpl_vars["note"]->key;
?>
            <?php $_smarty_tpl->_capture_stack[0][] = array("note_title", null, null); ob_start(); ?>
                <?php if ($_smarty_tpl->tpl_vars['title']->value=="_note_") {?><?php echo $_smarty_tpl->__("notes");?>
<?php } else { ?><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['title']->value, ENT_QUOTES, 'UTF-8');?>
<?php }?>
            <?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>
            <?php echo $_smarty_tpl->getSubTemplate ("common/sidebox.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('content'=>$_smarty_tpl->tpl_vars['note']->value,'title'=>Smarty::$_smarty_vars['capture']['note_title']), 0);?>

        <?php } ?>
    <?php }?>
<?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>

<!-- Sidebar left -->
<?php if (!$_smarty_tpl->tpl_vars['no_sidebar']->value&&trim($_smarty_tpl->tpl_vars['sidebar_content']->value)!=''&&$_smarty_tpl->tpl_vars['sidebar_position']->value=="left") {?>
<div class="sidebar sidebar-left" id="elm_sidebar">
    <div class="sidebar-wrapper">
    <?php echo $_smarty_tpl->tpl_vars['sidebar_content']->value;?>

    </div>
<!--elm_sidebar--></div>
<?php }?>


<!--Content-->
<div class="content<?php if ($_smarty_tpl->tpl_vars['no_sidebar']->value) {?> content-no-sidebar<?php }?><?php if (trim($_smarty_tpl->tpl_vars['sidebar_content']->value)=='') {?> no-sidebar<?php }?> <?php if (fn_allowed_for("ULTIMATE")) {?>ufa<?php }?>" <?php if ($_smarty_tpl->tpl_vars['box_id']->value) {?>id="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['box_id']->value, ENT_QUOTES, 'UTF-8');?>
"<?php }?>>
    <div class="content-wrap">

    <?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"index:content_top")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"index:content_top"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

        <?php if ($_smarty_tpl->tpl_vars['select_languages']->value&&sizeof($_smarty_tpl->tpl_vars['languages']->value)>1) {?>
            <div class="language-wrap">
                <h6 class="muted"><?php echo $_smarty_tpl->__("language");?>
:</h6>
                <?php if (!fn_allowed_for("ULTIMATE:FREE")) {?>
                    <?php echo $_smarty_tpl->getSubTemplate ("common/select_object.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('style'=>"graphic",'link_tpl'=>fn_link_attach($_smarty_tpl->tpl_vars['config']->value['current_url'],"descr_sl="),'items'=>$_smarty_tpl->tpl_vars['languages']->value,'selected_id'=>@constant('DESCR_SL'),'key_name'=>"name",'suffix'=>"content",'display_icons'=>true), 0);?>

                <?php }?>
            </div>
        <?php }?>

        <?php if ($_smarty_tpl->tpl_vars['tools']->value) {?><?php echo $_smarty_tpl->tpl_vars['tools']->value;?>
<?php }?>

        <?php if ($_smarty_tpl->tpl_vars['title_extra']->value) {?><div class="title">-&nbsp;</div>
            <?php echo $_smarty_tpl->tpl_vars['title_extra']->value;?>

        <?php }?>

        <?php if (trim($_smarty_tpl->tpl_vars['extra_tools']->value)) {?>
            <div class="extra-tools">
                <?php echo $_smarty_tpl->tpl_vars['extra_tools']->value;?>

            </div>
        <?php }?>
    <?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"index:content_top"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>


    <?php if ($_smarty_tpl->tpl_vars['content_id']->value) {?><div id="content_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['content_id']->value, ENT_QUOTES, 'UTF-8');?>
"><?php }?>
        <?php echo (($tmp = @$_smarty_tpl->tpl_vars['content']->value)===null||$tmp==='' ? "&nbsp;" : $tmp);?>

    <?php if ($_smarty_tpl->tpl_vars['content_id']->value) {?><!--content_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['content_id']->value, ENT_QUOTES, 'UTF-8');?>
--></div><?php }?>

    <?php if ($_smarty_tpl->tpl_vars['box_id']->value) {?><!--<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['box_id']->value, ENT_QUOTES, 'UTF-8');?>
--><?php }?></div>
</div>

<!--/Content-->


<!-- Sidebar -->
<?php if (!$_smarty_tpl->tpl_vars['no_sidebar']->value&&trim($_smarty_tpl->tpl_vars['sidebar_content']->value)!=''&&$_smarty_tpl->tpl_vars['sidebar_position']->value=="right") {?>
<div class="sidebar" id="elm_sidebar">
    <div class="sidebar-wrapper">
    <?php echo $_smarty_tpl->tpl_vars['sidebar_content']->value;?>

    </div>
<!--elm_sidebar--></div>
<?php }?>



<script type="text/javascript">
    var ajax_callback_data = menu_content;
</script><?php }} ?>
