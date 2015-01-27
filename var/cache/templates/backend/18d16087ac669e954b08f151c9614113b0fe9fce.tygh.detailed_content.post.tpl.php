<?php /* Smarty version Smarty-3.1.18, created on 2014-11-24 17:22:27
         compiled from "/var/www/html/workspace/cscart/design/backend/templates/addons/seo/hooks/companies/detailed_content.post.tpl" */ ?>
<?php /*%%SmartyHeaderCode:77953552454733f23be19c1-37272112%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '18d16087ac669e954b08f151c9614113b0fe9fce' => 
    array (
      0 => '/var/www/html/workspace/cscart/design/backend/templates/addons/seo/hooks/companies/detailed_content.post.tpl',
      1 => 1413383300,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '77953552454733f23be19c1-37272112',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'runtime' => 0,
    'company_data' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_54733f23beac15_48065168',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54733f23beac15_48065168')) {function content_54733f23beac15_48065168($_smarty_tpl) {?><?php if (!fn_allowed_for("ULTIMATE")&&!$_smarty_tpl->tpl_vars['runtime']->value['company_id']) {?>
<?php echo $_smarty_tpl->getSubTemplate ("addons/seo/common/seo_name_field.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('object_data'=>$_smarty_tpl->tpl_vars['company_data']->value,'object_name'=>"company_data",'object_id'=>$_smarty_tpl->tpl_vars['company_data']->value['company_id'],'object_type'=>"m"), 0);?>

<?php }?><?php }} ?>
