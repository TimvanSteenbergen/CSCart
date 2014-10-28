<?php /* Smarty version Smarty-3.1.18, created on 2014-10-27 17:31:48
         compiled from "/var/www/html/workspace/cscart/design/backend/templates/common/select_popup.tpl" */ ?>
<?php /*%%SmartyHeaderCode:758243925544e4944cfb242-52911098%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '6ce8e707782f7cfabe487dad32aa507cec2e5329' => 
    array (
      0 => '/var/www/html/workspace/cscart/design/backend/templates/common/select_popup.tpl',
      1 => 1413383301,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '758243925544e4944cfb242-52911098',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'non_editable' => 0,
    'display' => 0,
    'status' => 0,
    'prefix' => 0,
    'btn_meta' => 0,
    'popup_additional_class' => 0,
    'hide_for_vendor' => 0,
    'id' => 0,
    'items_status' => 0,
    'default_status_text' => 0,
    'update_controller' => 0,
    'table' => 0,
    'object_id_name' => 0,
    'hidden' => 0,
    'extra' => 0,
    'st_return_url' => 0,
    'extra_params' => 0,
    'return_url' => 0,
    'st' => 0,
    'confirm' => 0,
    'status_meta' => 0,
    'status_target_id' => 0,
    '_update_controller' => 0,
    'dynamic_object' => 0,
    'statuses' => 0,
    'st_result_ids' => 0,
    'val' => 0,
    'notify' => 0,
    'notify_text' => 0,
    'notify_department' => 0,
    'notify_vendor' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_544e4944d980c9_37785414',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_544e4944d980c9_37785414')) {function content_544e4944d980c9_37785414($_smarty_tpl) {?><?php if (!is_callable('smarty_block_hook')) include '/var/www/html/workspace/cscart/app/functions/smarty_plugins/block.hook.php';
if (!is_callable('smarty_function_script')) include '/var/www/html/workspace/cscart/app/functions/smarty_plugins/function.script.php';
?><?php
fn_preload_lang_vars(array('active','hidden','disabled','pending','new','active','disabled','hidden','pending','new','notify_customer','notify_orders_department','notify_vendor'));
?>
<?php if ($_smarty_tpl->tpl_vars['non_editable']->value||$_smarty_tpl->tpl_vars['display']->value=="text") {?>
    <span class="view-status">
        <?php if ($_smarty_tpl->tpl_vars['status']->value=="A") {?>
            <?php echo $_smarty_tpl->__("active");?>

        <?php } elseif ($_smarty_tpl->tpl_vars['status']->value=="H") {?>
            <?php echo $_smarty_tpl->__("hidden");?>

        <?php } elseif ($_smarty_tpl->tpl_vars['status']->value=="D") {?>
            <?php echo $_smarty_tpl->__("disabled");?>

        <?php } elseif ($_smarty_tpl->tpl_vars['status']->value=="P") {?>
            <?php echo $_smarty_tpl->__("pending");?>

        <?php } elseif ($_smarty_tpl->tpl_vars['status']->value=="N") {?>
            <?php echo $_smarty_tpl->__("new");?>

        <?php }?>
    </span>
<?php } else { ?>
<?php $_smarty_tpl->tpl_vars["prefix"] = new Smarty_variable((($tmp = @$_smarty_tpl->tpl_vars['prefix']->value)===null||$tmp==='' ? "select" : $tmp), null, 0);?>
<?php $_smarty_tpl->tpl_vars["btn_meta"] = new Smarty_variable((($tmp = @$_smarty_tpl->tpl_vars['btn_meta']->value)===null||$tmp==='' ? "btn-text" : $tmp), null, 0);?>
<?php $_smarty_tpl->tpl_vars["popup_additional_class"] = new Smarty_variable($_smarty_tpl->tpl_vars['popup_additional_class']->value, null, 0);?>
<div class="cm-popup-box dropleft <?php if (!$_smarty_tpl->tpl_vars['hide_for_vendor']->value) {?>dropdown<?php }?> <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['popup_additional_class']->value, ENT_QUOTES, 'UTF-8');?>
">
    <?php if (!$_smarty_tpl->tpl_vars['hide_for_vendor']->value) {?>
        <a <?php if ($_smarty_tpl->tpl_vars['id']->value) {?>id="sw_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['prefix']->value, ENT_QUOTES, 'UTF-8');?>
_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
_wrap"<?php }?> class="<?php if ($_smarty_tpl->tpl_vars['btn_meta']->value) {?><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['btn_meta']->value, ENT_QUOTES, 'UTF-8');?>
<?php }?> btn dropdown-toggle<?php if ($_smarty_tpl->tpl_vars['id']->value) {?> cm-combination<?php }?>" data-toggle="dropdown">
    <?php }?>
        <?php if ($_smarty_tpl->tpl_vars['items_status']->value) {?>
            <?php echo htmlspecialchars((($tmp = @$_smarty_tpl->tpl_vars['items_status']->value[$_smarty_tpl->tpl_vars['status']->value])===null||$tmp==='' ? $_smarty_tpl->tpl_vars['default_status_text']->value : $tmp), ENT_QUOTES, 'UTF-8');?>

        <?php } else { ?>
            <?php if ($_smarty_tpl->tpl_vars['status']->value=="A") {?>
                <?php echo $_smarty_tpl->__("active");?>

            <?php } elseif ($_smarty_tpl->tpl_vars['status']->value=="D") {?>
                <?php echo $_smarty_tpl->__("disabled");?>

            <?php } elseif ($_smarty_tpl->tpl_vars['status']->value=="H") {?>
                <?php echo $_smarty_tpl->__("hidden");?>

            <?php } elseif ($_smarty_tpl->tpl_vars['status']->value=="P") {?>
                <?php echo $_smarty_tpl->__("pending");?>

            <?php } elseif ($_smarty_tpl->tpl_vars['status']->value=="N") {?>
                <?php echo $_smarty_tpl->__("new");?>

            <?php }?>
        <?php }?>
    <?php if (!$_smarty_tpl->tpl_vars['hide_for_vendor']->value) {?>
        <span class="caret"></span>
        </a>
    <?php }?>
    <?php if ($_smarty_tpl->tpl_vars['id']->value&&!$_smarty_tpl->tpl_vars['hide_for_vendor']->value) {?>
        <?php $_smarty_tpl->tpl_vars["_update_controller"] = new Smarty_variable((($tmp = @$_smarty_tpl->tpl_vars['update_controller']->value)===null||$tmp==='' ? "tools" : $tmp), null, 0);?>
        <?php if ($_smarty_tpl->tpl_vars['table']->value&&$_smarty_tpl->tpl_vars['object_id_name']->value) {?><?php $_smarty_tpl->_capture_stack[0][] = array("_extra", null, null); ob_start(); ?>&table=<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['table']->value, ENT_QUOTES, 'UTF-8');?>
&id_name=<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['object_id_name']->value, ENT_QUOTES, 'UTF-8');?>
<?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?><?php }?>
            <ul class="dropdown-menu">
            <?php if (!$_smarty_tpl->tpl_vars['items_status']->value) {?>
            <?php $_smarty_tpl->tpl_vars["items_status"] = new Smarty_variable(fn_get_default_statuses($_smarty_tpl->tpl_vars['status']->value,$_smarty_tpl->tpl_vars['hidden']->value), null, 0);?>
            <?php $_smarty_tpl->tpl_vars["extra_params"] = new Smarty_variable("&table=".((string)$_smarty_tpl->tpl_vars['table']->value)."&id_name=".((string)$_smarty_tpl->tpl_vars['object_id_name']->value), null, 0);?>
            <?php } else { ?>
            <?php $_smarty_tpl->tpl_vars["extra_params"] = new Smarty_variable(((string)Smarty::$_smarty_vars['capture']['_extra']).((string)$_smarty_tpl->tpl_vars['extra']->value), null, 0);?>
            <?php }?>
            <?php if ($_smarty_tpl->tpl_vars['st_return_url']->value) {?>
                <?php $_smarty_tpl->tpl_vars['return_url'] = new Smarty_variable(rawurlencode($_smarty_tpl->tpl_vars['st_return_url']->value), null, 0);?>
                <?php $_smarty_tpl->tpl_vars['extra_params'] = new Smarty_variable(((string)$_smarty_tpl->tpl_vars['extra_params']->value)."&redirect_url=".((string)$_smarty_tpl->tpl_vars['return_url']->value), null, 0);?>
            <?php }?>
            <?php if ($_smarty_tpl->tpl_vars['items_status']->value) {?>
                <?php  $_smarty_tpl->tpl_vars["val"] = new Smarty_Variable; $_smarty_tpl->tpl_vars["val"]->_loop = false;
 $_smarty_tpl->tpl_vars["st"] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['items_status']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars["val"]->key => $_smarty_tpl->tpl_vars["val"]->value) {
$_smarty_tpl->tpl_vars["val"]->_loop = true;
 $_smarty_tpl->tpl_vars["st"]->value = $_smarty_tpl->tpl_vars["val"]->key;
?>
                <li <?php if ($_smarty_tpl->tpl_vars['status']->value==$_smarty_tpl->tpl_vars['st']->value) {?>class="disabled"<?php }?>><a class="<?php if ($_smarty_tpl->tpl_vars['confirm']->value) {?>cm-confirm <?php }?>status-link-<?php echo htmlspecialchars(mb_strtolower($_smarty_tpl->tpl_vars['st']->value, 'UTF-8'), ENT_QUOTES, 'UTF-8');?>
 <?php if ($_smarty_tpl->tpl_vars['status']->value==$_smarty_tpl->tpl_vars['st']->value) {?>active<?php } else { ?>cm-ajax<?php }?> <?php if ($_smarty_tpl->tpl_vars['status_meta']->value) {?><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['status_meta']->value, ENT_QUOTES, 'UTF-8');?>
<?php }?>"<?php if ($_smarty_tpl->tpl_vars['status_target_id']->value) {?> data-ca-target-id="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['status_target_id']->value, ENT_QUOTES, 'UTF-8');?>
"<?php }?> href="<?php echo htmlspecialchars(fn_url(((string)$_smarty_tpl->tpl_vars['_update_controller']->value).".update_status?id=".((string)$_smarty_tpl->tpl_vars['id']->value)."&status=".((string)$_smarty_tpl->tpl_vars['st']->value).((string)$_smarty_tpl->tpl_vars['extra_params']->value).((string)$_smarty_tpl->tpl_vars['dynamic_object']->value)), ENT_QUOTES, 'UTF-8');?>
" onclick="return fn_check_object_status(this, '<?php echo htmlspecialchars(mb_strtolower($_smarty_tpl->tpl_vars['st']->value, 'UTF-8'), ENT_QUOTES, 'UTF-8');?>
', '<?php if ($_smarty_tpl->tpl_vars['statuses']->value) {?><?php echo htmlspecialchars((($tmp = @$_smarty_tpl->tpl_vars['statuses']->value[$_smarty_tpl->tpl_vars['st']->value]['params']['color'])===null||$tmp==='' ? '' : $tmp), ENT_QUOTES, 'UTF-8');?>
<?php }?>');" <?php if ($_smarty_tpl->tpl_vars['st_result_ids']->value) {?>data-ca-target-id="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['st_result_ids']->value, ENT_QUOTES, 'UTF-8');?>
"<?php }?> data-ca-event="ce.update_object_status_callback"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['val']->value, ENT_QUOTES, 'UTF-8');?>
</a></li>
                <?php } ?>
            <?php }?>
            <?php $_smarty_tpl->_capture_stack[0][] = array("list_items", null, null); ob_start(); ?>
            <?php if ($_smarty_tpl->tpl_vars['notify']->value) {?>
                <li class="divider"></li>
                <li><a><label for="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['prefix']->value, ENT_QUOTES, 'UTF-8');?>
_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
_notify">
                    <input type="checkbox" name="__notify_user" id="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['prefix']->value, ENT_QUOTES, 'UTF-8');?>
_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
_notify" value="Y" checked="checked" onclick="Tygh.$('input[name=__notify_user]').prop('checked', this.checked);" />
                    <?php echo htmlspecialchars((($tmp = @$_smarty_tpl->tpl_vars['notify_text']->value)===null||$tmp==='' ? $_smarty_tpl->__("notify_customer") : $tmp), ENT_QUOTES, 'UTF-8');?>
</label></a>
                </li>
            <?php }?>
            <?php if ($_smarty_tpl->tpl_vars['notify_department']->value) {?>
                <li><a><label for="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['prefix']->value, ENT_QUOTES, 'UTF-8');?>
_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
_notify_department">
                    <input type="checkbox" name="__notify_department" id="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['prefix']->value, ENT_QUOTES, 'UTF-8');?>
_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
_notify_department" value="Y" checked="checked" onclick="Tygh.$('input[name=__notify_department]').prop('checked', this.checked);" />
                    <?php echo $_smarty_tpl->__("notify_orders_department");?>
</label></a>
                </li>
            <?php }?>
            <?php if (fn_allowed_for("MULTIVENDOR")&&$_smarty_tpl->tpl_vars['notify_vendor']->value) {?>
                <li><a><label for="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['prefix']->value, ENT_QUOTES, 'UTF-8');?>
_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
_notify_vendor">
                    <input type="checkbox" name="__notify_vendor" id="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['prefix']->value, ENT_QUOTES, 'UTF-8');?>
_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
_notify_vendor" value="Y" checked="checked" onclick="Tygh.$('input[name=__notify_vendor]').prop('checked', this.checked);" />
                    <?php echo $_smarty_tpl->__("notify_vendor");?>
</label></a>
                </li>
            <?php }?>

            <?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"select_popup:notify_checkboxes")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"select_popup:notify_checkboxes"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

            <?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"select_popup:notify_checkboxes"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>


            <?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>
            
            <?php if (trim(Smarty::$_smarty_vars['capture']['list_items'])) {?>
                <?php echo Smarty::$_smarty_vars['capture']['list_items'];?>

            <?php }?>
        </ul>
        <?php if (!Smarty::$_smarty_vars['capture']['avail_box']) {?>
        <?php echo smarty_function_script(array('src'=>"js/tygh/select_popup.js"),$_smarty_tpl);?>

        <?php $_smarty_tpl->_capture_stack[0][] = array("avail_box", null, null); ob_start(); ?>Y<?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>
        <?php }?>
    <?php }?>
</div>
<?php }?>
<?php }} ?>
