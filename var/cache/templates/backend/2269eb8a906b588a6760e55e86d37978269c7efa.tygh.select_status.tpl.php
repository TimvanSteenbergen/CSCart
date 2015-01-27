<?php /* Smarty version Smarty-3.1.18, created on 2014-11-24 17:22:57
         compiled from "/var/www/html/workspace/cscart/design/backend/templates/common/select_status.tpl" */ ?>
<?php /*%%SmartyHeaderCode:126953983554733f4132dd94-97028965%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '2269eb8a906b588a6760e55e86d37978269c7efa' => 
    array (
      0 => '/var/www/html/workspace/cscart/design/backend/templates/common/select_status.tpl',
      1 => 1413383301,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '126953983554733f4132dd94-97028965',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'display' => 0,
    'obj' => 0,
    'selected_st' => 0,
    'meta' => 0,
    'input_name' => 0,
    'input_id' => 0,
    'hidden' => 0,
    'status' => 0,
    'items_status' => 0,
    'st' => 0,
    'statuses' => 0,
    'val' => 0,
    'id' => 0,
    'obj_id' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_54733f413da4b6_62160228',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54733f413da4b6_62160228')) {function content_54733f413da4b6_62160228($_smarty_tpl) {?><?php if (!is_callable('smarty_function_script')) include '/var/www/html/workspace/cscart/app/functions/smarty_plugins/function.script.php';
?><?php
fn_preload_lang_vars(array('active','hidden','disabled','active','hidden','disabled','status','status','active','hidden','pending','disabled'));
?>
<?php if ($_smarty_tpl->tpl_vars['display']->value=="select"||$_smarty_tpl->tpl_vars['display']->value=="popup") {?>
<?php $_smarty_tpl->tpl_vars["selected_st"] = new Smarty_variable((($tmp = @$_smarty_tpl->tpl_vars['obj']->value['status'])===null||$tmp==='' ? "A" : $tmp), null, 0);?>
<?php $_smarty_tpl->_capture_stack[0][] = array("status_title", null, null); ob_start(); ?>
    <?php if ($_smarty_tpl->tpl_vars['selected_st']->value=="A") {?>
        <?php echo $_smarty_tpl->__("active");?>

    <?php } elseif ($_smarty_tpl->tpl_vars['selected_st']->value=="H") {?>
        <?php echo $_smarty_tpl->__("hidden");?>

    <?php } elseif ($_smarty_tpl->tpl_vars['selected_st']->value=="D") {?>
        <?php echo $_smarty_tpl->__("disabled");?>

    <?php }?>
<?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>
<?php }?>
<?php if ($_smarty_tpl->tpl_vars['display']->value=="select") {?>
<select class="input-small <?php if ($_smarty_tpl->tpl_vars['meta']->value) {?><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['meta']->value, ENT_QUOTES, 'UTF-8');?>
<?php }?>" name="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['input_name']->value, ENT_QUOTES, 'UTF-8');?>
" <?php if ($_smarty_tpl->tpl_vars['input_id']->value) {?>id="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['input_id']->value, ENT_QUOTES, 'UTF-8');?>
"<?php }?>>
    <option value="A" <?php if ($_smarty_tpl->tpl_vars['obj']->value['status']=="A") {?>selected="selected"<?php }?>><?php echo $_smarty_tpl->__("active");?>
</option>
    <?php if ($_smarty_tpl->tpl_vars['hidden']->value) {?>
    <option value="H" <?php if ($_smarty_tpl->tpl_vars['obj']->value['status']=="H") {?>selected="selected"<?php }?>><?php echo $_smarty_tpl->__("hidden");?>
</option>
    <?php }?>
    <option value="D" <?php if ($_smarty_tpl->tpl_vars['obj']->value['status']=="D") {?>selected="selected"<?php }?>><?php echo $_smarty_tpl->__("disabled");?>
</option>
</select>
<?php } elseif ($_smarty_tpl->tpl_vars['display']->value=="popup") {?>
<input <?php if ($_smarty_tpl->tpl_vars['meta']->value) {?>class="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['meta']->value, ENT_QUOTES, 'UTF-8');?>
"<?php }?> type="hidden" name="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['input_name']->value, ENT_QUOTES, 'UTF-8');?>
" id="<?php echo htmlspecialchars((($tmp = @$_smarty_tpl->tpl_vars['input_id']->value)===null||$tmp==='' ? $_smarty_tpl->tpl_vars['input_name']->value : $tmp), ENT_QUOTES, 'UTF-8');?>
" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['selected_st']->value, ENT_QUOTES, 'UTF-8');?>
" />
<div class="cm-popup-box btn-group dropleft">
    <a id="sw_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['input_name']->value, ENT_QUOTES, 'UTF-8');?>
" class="dropdown-toggle btn-text" data-toggle="dropdown">
    <?php echo Smarty::$_smarty_vars['capture']['status_title'];?>

    <span class="caret"></span>
    </a>
    <ul class="dropdown-menu cm-select">
        <?php $_smarty_tpl->tpl_vars["items_status"] = new Smarty_variable(fn_get_default_statuses($_smarty_tpl->tpl_vars['status']->value,$_smarty_tpl->tpl_vars['hidden']->value), null, 0);?>
        <?php  $_smarty_tpl->tpl_vars["val"] = new Smarty_Variable; $_smarty_tpl->tpl_vars["val"]->_loop = false;
 $_smarty_tpl->tpl_vars["st"] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['items_status']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars["val"]->key => $_smarty_tpl->tpl_vars["val"]->value) {
$_smarty_tpl->tpl_vars["val"]->_loop = true;
 $_smarty_tpl->tpl_vars["st"]->value = $_smarty_tpl->tpl_vars["val"]->key;
?>
            <li <?php if ($_smarty_tpl->tpl_vars['selected_st']->value==$_smarty_tpl->tpl_vars['st']->value) {?>class="disabled"<?php }?>><a class="status-link-<?php echo htmlspecialchars(mb_strtolower($_smarty_tpl->tpl_vars['st']->value, 'UTF-8'), ENT_QUOTES, 'UTF-8');?>
 <?php if ($_smarty_tpl->tpl_vars['selected_st']->value==$_smarty_tpl->tpl_vars['st']->value) {?>active<?php }?>"  onclick="return fn_check_object_status(this, '<?php echo htmlspecialchars(mb_strtolower($_smarty_tpl->tpl_vars['st']->value, 'UTF-8'), ENT_QUOTES, 'UTF-8');?>
', '<?php if ($_smarty_tpl->tpl_vars['statuses']->value) {?><?php echo htmlspecialchars((($tmp = @$_smarty_tpl->tpl_vars['statuses']->value[$_smarty_tpl->tpl_vars['st']->value]['color'])===null||$tmp==='' ? '' : $tmp), ENT_QUOTES, 'UTF-8');?>
<?php }?>');" data-ca-result-id="<?php echo htmlspecialchars((($tmp = @$_smarty_tpl->tpl_vars['input_id']->value)===null||$tmp==='' ? $_smarty_tpl->tpl_vars['input_name']->value : $tmp), ENT_QUOTES, 'UTF-8');?>
"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['val']->value, ENT_QUOTES, 'UTF-8');?>
</a></li>
        <?php } ?>
    </ul>
</div>
<?php if (!Smarty::$_smarty_vars['capture']['avail_box']) {?>
    <?php echo smarty_function_script(array('src'=>"js/tygh/select_popup.js"),$_smarty_tpl);?>

    <?php $_smarty_tpl->_capture_stack[0][] = array("avail_box", null, null); ob_start(); ?>Y<?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>
<?php }?>
<?php } elseif ($_smarty_tpl->tpl_vars['display']->value=="text") {?>
<div class="control-group">
    <label class="control-label cm-required"><?php echo $_smarty_tpl->__("status");?>
</label>
    <div class="controls">
    <span>
    <?php echo Smarty::$_smarty_vars['capture']['status_title'];?>

    </span>
    </div>
</div>
<?php } else { ?>
<div class="control-group">
    <label class="control-label cm-required"><?php echo $_smarty_tpl->__("status");?>
</label>
    <div class="controls">
        <?php if ($_smarty_tpl->tpl_vars['items_status']->value) {?>
            <?php  $_smarty_tpl->tpl_vars["val"] = new Smarty_Variable; $_smarty_tpl->tpl_vars["val"]->_loop = false;
 $_smarty_tpl->tpl_vars["st"] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['items_status']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars["val"]->index=-1;
foreach ($_from as $_smarty_tpl->tpl_vars["val"]->key => $_smarty_tpl->tpl_vars["val"]->value) {
$_smarty_tpl->tpl_vars["val"]->_loop = true;
 $_smarty_tpl->tpl_vars["st"]->value = $_smarty_tpl->tpl_vars["val"]->key;
 $_smarty_tpl->tpl_vars["val"]->index++;
 $_smarty_tpl->tpl_vars["val"]->first = $_smarty_tpl->tpl_vars["val"]->index === 0;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']["status_cycle"]['first'] = $_smarty_tpl->tpl_vars["val"]->first;
?>
                <label class="radio inline" for="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
_<?php echo htmlspecialchars((($tmp = @$_smarty_tpl->tpl_vars['obj_id']->value)===null||$tmp==='' ? 0 : $tmp), ENT_QUOTES, 'UTF-8');?>
_<?php echo htmlspecialchars(mb_strtolower($_smarty_tpl->tpl_vars['st']->value, 'UTF-8'), ENT_QUOTES, 'UTF-8');?>
"><input type="radio" name="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['input_name']->value, ENT_QUOTES, 'UTF-8');?>
" id="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
_<?php echo htmlspecialchars((($tmp = @$_smarty_tpl->tpl_vars['obj_id']->value)===null||$tmp==='' ? 0 : $tmp), ENT_QUOTES, 'UTF-8');?>
_<?php echo htmlspecialchars(mb_strtolower($_smarty_tpl->tpl_vars['st']->value, 'UTF-8'), ENT_QUOTES, 'UTF-8');?>
" <?php if ($_smarty_tpl->tpl_vars['obj']->value['status']==$_smarty_tpl->tpl_vars['st']->value||(!$_smarty_tpl->tpl_vars['obj']->value['status']&&$_smarty_tpl->getVariable('smarty')->value['foreach']['status_cycle']['first'])) {?>checked="checked"<?php }?> value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['st']->value, ENT_QUOTES, 'UTF-8');?>
" /><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['val']->value, ENT_QUOTES, 'UTF-8');?>
</label>
            <?php } ?>
        <?php } else { ?>
            <label class="radio inline" for="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
_<?php echo htmlspecialchars((($tmp = @$_smarty_tpl->tpl_vars['obj_id']->value)===null||$tmp==='' ? 0 : $tmp), ENT_QUOTES, 'UTF-8');?>
_a"><input type="radio" name="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['input_name']->value, ENT_QUOTES, 'UTF-8');?>
" id="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
_<?php echo htmlspecialchars((($tmp = @$_smarty_tpl->tpl_vars['obj_id']->value)===null||$tmp==='' ? 0 : $tmp), ENT_QUOTES, 'UTF-8');?>
_a" <?php if ($_smarty_tpl->tpl_vars['obj']->value['status']=="A"||!$_smarty_tpl->tpl_vars['obj']->value['status']) {?>checked="checked"<?php }?> value="A" /><?php echo $_smarty_tpl->__("active");?>
</label>

        <?php if ($_smarty_tpl->tpl_vars['hidden']->value) {?>
            <label class="radio inline" for="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
_<?php echo htmlspecialchars((($tmp = @$_smarty_tpl->tpl_vars['obj_id']->value)===null||$tmp==='' ? 0 : $tmp), ENT_QUOTES, 'UTF-8');?>
_h"><input type="radio" name="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['input_name']->value, ENT_QUOTES, 'UTF-8');?>
" id="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
_<?php echo htmlspecialchars((($tmp = @$_smarty_tpl->tpl_vars['obj_id']->value)===null||$tmp==='' ? 0 : $tmp), ENT_QUOTES, 'UTF-8');?>
_h" <?php if ($_smarty_tpl->tpl_vars['obj']->value['status']=="H") {?>checked="checked"<?php }?> value="H" /><?php echo $_smarty_tpl->__("hidden");?>
</label>
        <?php }?>

        <?php if ($_smarty_tpl->tpl_vars['obj']->value['status']=="P") {?>
            <label class="radio inline" for="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
_<?php echo htmlspecialchars((($tmp = @$_smarty_tpl->tpl_vars['obj_id']->value)===null||$tmp==='' ? 0 : $tmp), ENT_QUOTES, 'UTF-8');?>
_p"><input type="radio" name="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['input_name']->value, ENT_QUOTES, 'UTF-8');?>
" id="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
_<?php echo htmlspecialchars((($tmp = @$_smarty_tpl->tpl_vars['obj_id']->value)===null||$tmp==='' ? 0 : $tmp), ENT_QUOTES, 'UTF-8');?>
_p" checked="checked" value="P"/><?php echo $_smarty_tpl->__("pending");?>
</label>
        <?php }?>

            <label class="radio inline" for="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
_<?php echo htmlspecialchars((($tmp = @$_smarty_tpl->tpl_vars['obj_id']->value)===null||$tmp==='' ? 0 : $tmp), ENT_QUOTES, 'UTF-8');?>
_d"><input type="radio" name="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['input_name']->value, ENT_QUOTES, 'UTF-8');?>
" id="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
_<?php echo htmlspecialchars((($tmp = @$_smarty_tpl->tpl_vars['obj_id']->value)===null||$tmp==='' ? 0 : $tmp), ENT_QUOTES, 'UTF-8');?>
_d" <?php if ($_smarty_tpl->tpl_vars['obj']->value['status']=="D") {?>checked="checked"<?php }?> value="D" /><?php echo $_smarty_tpl->__("disabled");?>
</label>
        <?php }?>
    </div>
</div>
<?php }?><?php }} ?>
