<?php /* Smarty version Smarty-3.1.18, created on 2014-11-24 17:20:04
         compiled from "/var/www/html/workspace/cscart/design/backend/mail/templates/profiles/profile_fields_info.tpl" */ ?>
<?php /*%%SmartyHeaderCode:133128310454733e94e95691-49649765%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '80b9441c2d83a30e80b234ac1b284366f881304b' => 
    array (
      0 => '/var/www/html/workspace/cscart/design/backend/mail/templates/profiles/profile_fields_info.tpl',
      1 => 1413383298,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '133128310454733e94e95691-49649765',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'title' => 0,
    'fields' => 0,
    'user_data' => 0,
    'field' => 0,
    'value' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_54733e94ea3417_00261183',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54733e94ea3417_00261183')) {function content_54733e94ea3417_00261183($_smarty_tpl) {?><tr>
    <td colspan="2" class="form-title"><?php echo htmlspecialchars((($tmp = @$_smarty_tpl->tpl_vars['title']->value)===null||$tmp==='' ? "&nbsp;" : $tmp), ENT_QUOTES, 'UTF-8');?>
<hr size="1" noshade="noshade" /></td>
</tr>
<?php  $_smarty_tpl->tpl_vars['field'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['field']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['fields']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['field']->key => $_smarty_tpl->tpl_vars['field']->value) {
$_smarty_tpl->tpl_vars['field']->_loop = true;
?>
<?php $_smarty_tpl->tpl_vars["value"] = new Smarty_variable(fn_get_profile_field_value($_smarty_tpl->tpl_vars['user_data']->value,$_smarty_tpl->tpl_vars['field']->value), null, 0);?>
<?php if ($_smarty_tpl->tpl_vars['value']->value) {?>
<tr>
    <td class="form-field-caption" width="30%" nowrap="nowrap"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['field']->value['description'], ENT_QUOTES, 'UTF-8');?>
:&nbsp;</td>
    <td>
        <?php echo htmlspecialchars((($tmp = @$_smarty_tpl->tpl_vars['value']->value)===null||$tmp==='' ? "-" : $tmp), ENT_QUOTES, 'UTF-8');?>

    </td>
</tr>
<?php }?>
<?php } ?><?php }} ?>
