<?php /* Smarty version Smarty-3.1.18, created on 2014-10-27 16:10:19
         compiled from "/var/www/html/workspace/cscart/design/backend/templates/common/popupbox.tpl" */ ?>
<?php /*%%SmartyHeaderCode:384023904544e362b902524-04499256%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '9798b92319af1c10abd6e3fe13bf77377e959a4c' => 
    array (
      0 => '/var/www/html/workspace/cscart/design/backend/templates/common/popupbox.tpl',
      1 => 1413383301,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '384023904544e362b902524-04499256',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'method' => 0,
    'id' => 0,
    'content' => 0,
    'popup_params' => 0,
    'text' => 0,
    'runtime' => 0,
    'act' => 0,
    'href' => 0,
    '_href' => 0,
    'edit_onclick' => 0,
    'no_icon_link' => 0,
    'update_controller' => 0,
    'icon' => 0,
    'is_promo' => 0,
    'opener_ajax_class' => 0,
    'link_class' => 0,
    'link_text' => 0,
    'drop_left' => 0,
    'default_link_text' => 0,
    'but_text' => 0,
    'but_meta' => 0,
    'meta' => 0,
    'title' => 0,
    'but_href' => 0,
    'but_role' => 0,
    'onclick' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_544e362b99b614_43486440',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_544e362b99b614_43486440')) {function content_544e362b99b614_43486440($_smarty_tpl) {?><?php if (!is_callable('smarty_modifier_replace')) include '/var/www/html/workspace/cscart/app/lib/other/smarty/plugins/modifier.replace.php';
?><?php
fn_preload_lang_vars(array('edit','view','view','edit','edit','add'));
?>
<?php $_smarty_tpl->tpl_vars["method"] = new Smarty_variable((($tmp = @$_smarty_tpl->tpl_vars['method']->value)===null||$tmp==='' ? "POST" : $tmp), null, 0);?>
<?php $_smarty_tpl->tpl_vars['popup_params'] = new Smarty_variable(" id=\"opener_".((string)$_smarty_tpl->tpl_vars['id']->value)."\" data-ca-target-id=\"content_".((string)$_smarty_tpl->tpl_vars['id']->value)."\"", null, 0);?>
<?php if (!$_smarty_tpl->tpl_vars['content']->value) {?>
<?php $_smarty_tpl->tpl_vars['popup_params'] = new Smarty_variable(((string)$_smarty_tpl->tpl_vars['popup_params']->value)."  data-ca-dialog-title=\"".((string)smarty_modifier_replace($_smarty_tpl->tpl_vars['text']->value,'"',''))."\"", null, 0);?>
<?php }?>
<?php if (($_smarty_tpl->tpl_vars['runtime']->value['action']&&fn_check_view_permissions($_smarty_tpl->tpl_vars['runtime']->value['action'],$_smarty_tpl->tpl_vars['method']->value))||(!$_smarty_tpl->tpl_vars['runtime']->value['action']&&fn_check_html_view_permissions($_smarty_tpl->tpl_vars['content']->value,$_smarty_tpl->tpl_vars['method']->value))) {?>
<?php if ($_smarty_tpl->tpl_vars['act']->value=="edit") {?>
    <?php $_smarty_tpl->tpl_vars["_href"] = new Smarty_variable(fn_url($_smarty_tpl->tpl_vars['href']->value), null, 0);?>
    <?php $_smarty_tpl->tpl_vars["default_link_text"] = new Smarty_variable($_smarty_tpl->__("edit"), null, 0);?>
    <?php if (!fn_check_view_permissions($_smarty_tpl->tpl_vars['_href']->value)) {?>
        <?php $_smarty_tpl->tpl_vars["_link_text"] = new Smarty_variable($_smarty_tpl->__("view"), null, 0);?>
        <?php $_smarty_tpl->tpl_vars["default_link_text"] = new Smarty_variable($_smarty_tpl->__("view"), null, 0);?>
    <?php }?>
    <a <?php if ($_smarty_tpl->tpl_vars['edit_onclick']->value) {?>onclick="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['edit_onclick']->value, ENT_QUOTES, 'UTF-8');?>
"<?php }?> class="hand <?php if (!$_smarty_tpl->tpl_vars['no_icon_link']->value) {?><?php if ($_smarty_tpl->tpl_vars['update_controller']->value=="addons") {?>exicon-cog<?php }?><?php if ($_smarty_tpl->tpl_vars['icon']->value) {?><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['icon']->value, ENT_QUOTES, 'UTF-8');?>
<?php }?><?php }?> <?php if (!$_smarty_tpl->tpl_vars['is_promo']->value) {?>cm-dialog-opener<?php }?><?php if ($_smarty_tpl->tpl_vars['is_promo']->value) {?>cm-promo-popup<?php }?> <?php if ($_smarty_tpl->tpl_vars['_href']->value&&!$_smarty_tpl->tpl_vars['is_promo']->value) {?> <?php echo htmlspecialchars((($tmp = @$_smarty_tpl->tpl_vars['opener_ajax_class']->value)===null||$tmp==='' ? 'cm-ajax' : $tmp), ENT_QUOTES, 'UTF-8');?>
<?php }?><?php if ($_smarty_tpl->tpl_vars['link_class']->value) {?> <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['link_class']->value, ENT_QUOTES, 'UTF-8');?>
<?php }?>" <?php if ($_smarty_tpl->tpl_vars['_href']->value&&!$_smarty_tpl->tpl_vars['is_promo']->value) {?> href="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['_href']->value, ENT_QUOTES, 'UTF-8');?>
"<?php }?> <?php echo $_smarty_tpl->tpl_vars['popup_params']->value;?>
 title="<?php echo htmlspecialchars((($tmp = @$_smarty_tpl->tpl_vars['link_text']->value)===null||$tmp==='' ? $_smarty_tpl->__("edit") : $tmp), ENT_QUOTES, 'UTF-8');?>
" <?php if ($_smarty_tpl->tpl_vars['drop_left']->value) {?>data-placement="left"<?php }?>><?php if ($_smarty_tpl->tpl_vars['icon']->value) {?><i class="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['icon']->value, ENT_QUOTES, 'UTF-8');?>
"></i><?php }?><?php echo (($tmp = @$_smarty_tpl->tpl_vars['link_text']->value)===null||$tmp==='' ? $_smarty_tpl->tpl_vars['default_link_text']->value : $tmp);?>
</a>
<?php } elseif ($_smarty_tpl->tpl_vars['act']->value=="edit_outside") {?>
    <a <?php if ($_smarty_tpl->tpl_vars['edit_onclick']->value) {?>onclick="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['edit_onclick']->value, ENT_QUOTES, 'UTF-8');?>
"<?php }?> class="hand btn cm-tooltip cm-dialog-opener <?php if ($_smarty_tpl->tpl_vars['_href']->value) {?> <?php echo htmlspecialchars((($tmp = @$_smarty_tpl->tpl_vars['opener_ajax_class']->value)===null||$tmp==='' ? 'cm-ajax' : $tmp), ENT_QUOTES, 'UTF-8');?>
<?php }?><?php if ($_smarty_tpl->tpl_vars['link_class']->value) {?> <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['link_class']->value, ENT_QUOTES, 'UTF-8');?>
<?php }?>" <?php if ($_smarty_tpl->tpl_vars['_href']->value) {?> href="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['_href']->value, ENT_QUOTES, 'UTF-8');?>
"<?php }?> <?php echo $_smarty_tpl->tpl_vars['popup_params']->value;?>
 title="<?php echo htmlspecialchars((($tmp = @$_smarty_tpl->tpl_vars['link_text']->value)===null||$tmp==='' ? $_smarty_tpl->__("edit") : $tmp), ENT_QUOTES, 'UTF-8');?>
" <?php if ($_smarty_tpl->tpl_vars['drop_left']->value) {?>data-placement="left"<?php }?>>
        <?php echo (($tmp = @$_smarty_tpl->tpl_vars['link_text']->value)===null||$tmp==='' ? $_smarty_tpl->tpl_vars['default_link_text']->value : $tmp);?>

    </a>
<?php } elseif ($_smarty_tpl->tpl_vars['act']->value=="create") {?>
    <?php echo $_smarty_tpl->getSubTemplate ("buttons/button.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('but_onclick'=>$_smarty_tpl->tpl_vars['edit_onclick']->value,'but_text'=>$_smarty_tpl->tpl_vars['but_text']->value,'but_role'=>"add",'but_target_id'=>"content_".((string)$_smarty_tpl->tpl_vars['id']->value),'but_meta'=>"btn cm-dialog-opener ".((string)$_smarty_tpl->tpl_vars['but_meta']->value),'but_icon'=>$_smarty_tpl->tpl_vars['icon']->value), 0);?>

<?php } elseif ($_smarty_tpl->tpl_vars['act']->value=="notes") {?>
    <a <?php echo $_smarty_tpl->tpl_vars['popup_params']->value;?>
 class="cm-dialog-opener <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['meta']->value, ENT_QUOTES, 'UTF-8');?>
"><i class="<?php echo htmlspecialchars((($tmp = @$_smarty_tpl->tpl_vars['icon']->value)===null||$tmp==='' ? 'icon-question-sign' : $tmp), ENT_QUOTES, 'UTF-8');?>
"></i></a>
<?php } elseif ($_smarty_tpl->tpl_vars['act']->value=="general") {?>
        <div class="btn-group <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['meta']->value, ENT_QUOTES, 'UTF-8');?>
">
            <a class="btn cm-dialog-opener <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['link_class']->value, ENT_QUOTES, 'UTF-8');?>
 cm-tooltip" <?php echo $_smarty_tpl->tpl_vars['popup_params']->value;?>
 <?php if ($_smarty_tpl->tpl_vars['edit_onclick']->value) {?>onclick="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['edit_onclick']->value, ENT_QUOTES, 'UTF-8');?>
"<?php }?> <?php if ($_smarty_tpl->tpl_vars['href']->value) {?>href="<?php echo htmlspecialchars(fn_url($_smarty_tpl->tpl_vars['href']->value), ENT_QUOTES, 'UTF-8');?>
"<?php }?> <?php if ($_smarty_tpl->tpl_vars['title']->value) {?>title="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['title']->value, ENT_QUOTES, 'UTF-8');?>
"<?php }?>><?php if ($_smarty_tpl->tpl_vars['icon']->value) {?><i class="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['icon']->value, ENT_QUOTES, 'UTF-8');?>
"></i><?php }?> <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['link_text']->value, ENT_QUOTES, 'UTF-8');?>
</a>
        </div>
<?php } elseif ($_smarty_tpl->tpl_vars['act']->value=="button") {?>
    <?php echo $_smarty_tpl->getSubTemplate ("buttons/button.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('but_text'=>$_smarty_tpl->tpl_vars['link_text']->value,'but_href'=>$_smarty_tpl->tpl_vars['but_href']->value,'but_role'=>$_smarty_tpl->tpl_vars['but_role']->value,'but_id'=>"opener_".((string)$_smarty_tpl->tpl_vars['id']->value),'but_onclick'=>((string)$_smarty_tpl->tpl_vars['edit_onclick']->value),'but_target_id'=>"content_".((string)$_smarty_tpl->tpl_vars['id']->value),'but_meta'=>"btn cm-dialog-opener"), 0);?>

<?php } elseif ($_smarty_tpl->tpl_vars['act']->value=="link") {?>
    <a class="cm-dialog-opener <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['link_class']->value, ENT_QUOTES, 'UTF-8');?>
" <?php echo $_smarty_tpl->tpl_vars['popup_params']->value;?>
 <?php if ($_smarty_tpl->tpl_vars['edit_onclick']->value) {?>onclick="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['edit_onclick']->value, ENT_QUOTES, 'UTF-8');?>
"<?php }?> <?php if ($_smarty_tpl->tpl_vars['href']->value) {?>href="<?php echo htmlspecialchars(fn_url($_smarty_tpl->tpl_vars['href']->value), ENT_QUOTES, 'UTF-8');?>
"<?php }?>><?php echo (($tmp = @$_smarty_tpl->tpl_vars['link_text']->value)===null||$tmp==='' ? $_smarty_tpl->__("add") : $tmp);?>
</a>
<?php } elseif ($_smarty_tpl->tpl_vars['act']->value=="default") {?>
    <a<?php if ($_smarty_tpl->tpl_vars['onclick']->value) {?> onclick="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['onclick']->value, ENT_QUOTES, 'UTF-8');?>
"<?php }?><?php if ($_smarty_tpl->tpl_vars['href']->value) {?> href="<?php echo htmlspecialchars(fn_url($_smarty_tpl->tpl_vars['href']->value), ENT_QUOTES, 'UTF-8');?>
"<?php }?> class="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['link_class']->value, ENT_QUOTES, 'UTF-8');?>
"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['link_text']->value, ENT_QUOTES, 'UTF-8');?>
</a>
<?php }?>

<?php if ($_smarty_tpl->tpl_vars['content']->value) {?>
<div class="hidden <?php if (fn_allowed_for("ULTIMATE")) {?>ufa<?php }?>" title="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['text']->value, ENT_QUOTES, 'UTF-8');?>
" id="content_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
">
    <?php echo $_smarty_tpl->tpl_vars['content']->value;?>

<!--content_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
--></div>
<?php }?>

<?php } else { ?><?php }?>
<?php }} ?>
