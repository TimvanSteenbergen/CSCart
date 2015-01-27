<?php /* Smarty version Smarty-3.1.18, created on 2014-11-24 17:22:27
         compiled from "/var/www/html/workspace/cscart/design/backend/templates/addons/discussion/hooks/companies/tabs_content.post.tpl" */ ?>
<?php /*%%SmartyHeaderCode:163305992354733f23c05d88-61929978%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'c47b81b5cb9087761a69b80398d54648d0f65d31' => 
    array (
      0 => '/var/www/html/workspace/cscart/design/backend/templates/addons/discussion/hooks/companies/tabs_content.post.tpl',
      1 => 1413383299,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '163305992354733f23c05d88-61929978',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'company_data' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_54733f23c0b0b9_72867569',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54733f23c0b0b9_72867569')) {function content_54733f23c0b0b9_72867569($_smarty_tpl) {?><?php if (!fn_allowed_for("ULTIMATE")) {?>
	<?php echo $_smarty_tpl->getSubTemplate ("addons/discussion/views/discussion_manager/components/discussion.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('object_company_id'=>$_smarty_tpl->tpl_vars['company_data']->value['company_id']), 0);?>

<?php }?><?php }} ?>
