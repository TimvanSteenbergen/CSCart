<?php /* Smarty version Smarty-3.1.18, created on 2014-11-24 17:22:27
         compiled from "/var/www/html/workspace/cscart/design/backend/templates/common/sidebox.tpl" */ ?>
<?php /*%%SmartyHeaderCode:154854311454733f23c330c4-26629951%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'a973ab6dff59635e0fb99a3f18a48266df5ae608' => 
    array (
      0 => '/var/www/html/workspace/cscart/design/backend/templates/common/sidebox.tpl',
      1 => 1413383301,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '154854311454733f23c330c4-26629951',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'content' => 0,
    'title' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_54733f23c3b414_01699606',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54733f23c3b414_01699606')) {function content_54733f23c3b414_01699606($_smarty_tpl) {?><?php if (trim($_smarty_tpl->tpl_vars['content']->value)) {?>
    <div class="sidebar-row">
        <h6><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['title']->value, ENT_QUOTES, 'UTF-8');?>
</h6>
        <?php echo (($tmp = @$_smarty_tpl->tpl_vars['content']->value)===null||$tmp==='' ? "&nbsp;" : $tmp);?>

    </div>
    <hr />
<?php }?><?php }} ?>
