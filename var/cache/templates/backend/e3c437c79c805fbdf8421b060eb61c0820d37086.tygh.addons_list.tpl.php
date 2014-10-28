<?php /* Smarty version Smarty-3.1.18, created on 2014-10-27 17:31:48
         compiled from "/var/www/html/workspace/cscart/design/backend/templates/views/addons/components/addons_list.tpl" */ ?>
<?php /*%%SmartyHeaderCode:295595716544e4944c6d5d9-57156142%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'e3c437c79c805fbdf8421b060eb61c0820d37086' => 
    array (
      0 => '/var/www/html/workspace/cscart/design/backend/templates/views/addons/components/addons_list.tpl',
      1 => 1413383302,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '295595716544e4944c6d5d9-57156142',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'addons_list' => 0,
    'runtime' => 0,
    'a' => 0,
    'non_editable' => 0,
    'additional_class' => 0,
    'key' => 0,
    'href' => 0,
    'show_installed' => 0,
    'link_text' => 0,
    'act' => 0,
    'hide_for_vendor' => 0,
    'status_meta' => 0,
    'display' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_544e4944ceed70_81609071',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_544e4944ceed70_81609071')) {function content_544e4944ceed70_81609071($_smarty_tpl) {?><?php
fn_preload_lang_vars(array('manage','settings','view','settings','uninstall','install','installed','no_data'));
?>
<?php if ($_smarty_tpl->tpl_vars['addons_list']->value) {?>
    <table class="table table-addons">
<?php  $_smarty_tpl->tpl_vars["a"] = new Smarty_Variable; $_smarty_tpl->tpl_vars["a"]->_loop = false;
 $_smarty_tpl->tpl_vars["key"] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['addons_list']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars["a"]->key => $_smarty_tpl->tpl_vars["a"]->value) {
$_smarty_tpl->tpl_vars["a"]->_loop = true;
 $_smarty_tpl->tpl_vars["key"]->value = $_smarty_tpl->tpl_vars["a"]->key;
?>

    <?php $_smarty_tpl->tpl_vars["non_editable"] = new Smarty_variable(false, null, 0);?>
    <?php $_smarty_tpl->tpl_vars["display"] = new Smarty_variable("text", null, 0);?>

    <?php if ($_smarty_tpl->tpl_vars['runtime']->value['company_id']) {?>
        <?php $_smarty_tpl->tpl_vars["hide_for_vendor"] = new Smarty_variable(true, null, 0);?>
    <?php }?>

    <?php if ($_smarty_tpl->tpl_vars['a']->value['status']=="N") {?>
        <?php $_smarty_tpl->tpl_vars["non_editable"] = new Smarty_variable(true, null, 0);?>
    <?php } else { ?>
        <?php $_smarty_tpl->tpl_vars["display"] = new Smarty_variable("popup", null, 0);?>
        <?php if ($_smarty_tpl->tpl_vars['a']->value['has_options']) {?>
            <?php $_smarty_tpl->tpl_vars["act"] = new Smarty_variable("edit", null, 0);?>
        <?php } else { ?>
            <?php $_smarty_tpl->tpl_vars["act"] = new Smarty_variable("none", null, 0);?>
            <?php $_smarty_tpl->tpl_vars["non_editable"] = new Smarty_variable(true, null, 0);?>
        <?php }?>
    <?php }?>

    <?php if ($_smarty_tpl->tpl_vars['a']->value['separate']&&!$_smarty_tpl->tpl_vars['non_editable']->value) {?>
        <?php $_smarty_tpl->tpl_vars["href"] = new Smarty_variable(fn_url("addons.update?addon=".((string)$_smarty_tpl->tpl_vars['a']->value['addon'])), null, 0);?>
        <?php $_smarty_tpl->tpl_vars["link_text"] = new Smarty_variable($_smarty_tpl->__("manage"), null, 0);?>
    <?php } elseif ($_smarty_tpl->tpl_vars['a']->value['status']!="N") {?>
        <?php $_smarty_tpl->tpl_vars["link_text"] = new Smarty_variable($_smarty_tpl->__("settings"), null, 0);?>
    <?php } else { ?>
        <?php $_smarty_tpl->tpl_vars["link_text"] = new Smarty_variable("&nbsp;", null, 0);?>
    <?php }?>

    <?php $_smarty_tpl->_capture_stack[0][] = array("addons_row", null, null); ob_start(); ?>
        <tr class="cm-row-status-<?php echo htmlspecialchars(mb_strtolower($_smarty_tpl->tpl_vars['a']->value['status'], 'UTF-8'), ENT_QUOTES, 'UTF-8');?>
 <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['additional_class']->value, ENT_QUOTES, 'UTF-8');?>
 cm-row-item" id="addon_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['key']->value, ENT_QUOTES, 'UTF-8');?>
">
            <td class="addon-icon">
                <div class="bg-icon">
                    
                    <?php if ($_smarty_tpl->tpl_vars['a']->value['status']=="N") {?>
                        <i class="exicon-box"></i>
                    <?php } else { ?>
                        <i class="exicon-box-blue"></i>
                    <?php }?>
                </div>
            </td>
            <td width="80%">
                <div class="object-group-link-wrap">
                <?php if (!$_smarty_tpl->tpl_vars['non_editable']->value) {?>
                    <?php if ($_smarty_tpl->tpl_vars['a']->value['separate']) {?>
                        <a href="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['href']->value, ENT_QUOTES, 'UTF-8');?>
"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['a']->value['name'], ENT_QUOTES, 'UTF-8');?>
</a>
                    <?php } else { ?>
                        <a class="row-status cm-external-click<?php if ($_smarty_tpl->tpl_vars['non_editable']->value) {?> no-underline<?php }?> <?php if (!$_smarty_tpl->tpl_vars['a']->value['snapshot_correct']) {?>cm-promo-popup<?php }?>" <?php if ($_smarty_tpl->tpl_vars['a']->value['snapshot_correct']) {?>data-ca-external-click-id="opener_group<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['key']->value, ENT_QUOTES, 'UTF-8');?>
"<?php }?>><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['a']->value['name'], ENT_QUOTES, 'UTF-8');?>
</a>
                    <?php }?>
                <?php } else { ?>
                    <span class="unedited-element block"><?php echo htmlspecialchars((($tmp = @$_smarty_tpl->tpl_vars['a']->value['name'])===null||$tmp==='' ? $_smarty_tpl->__("view") : $tmp), ENT_QUOTES, 'UTF-8');?>
</span>
                <?php }?>
                <br><span class="row-status object-group-details"><?php echo $_smarty_tpl->tpl_vars['a']->value['description'];?>
</span>
                </div>
            </td>
            <td width="10%" class="right nowrap">

                <?php if ($_smarty_tpl->tpl_vars['show_installed']->value&&$_smarty_tpl->tpl_vars['a']->value['status']!='N') {?>
                    <div class="pull-right">
                    <?php $_smarty_tpl->_capture_stack[0][] = array("tools_list", null, null); ob_start(); ?>
                        <?php if ($_smarty_tpl->tpl_vars['a']->value['separate']) {?>
                            <?php if (!$_smarty_tpl->tpl_vars['non_editable']->value) {?>
                                <li><?php smarty_template_function_btn($_smarty_tpl,array('type'=>"list",'text'=>$_smarty_tpl->tpl_vars['link_text']->value,'href'=>$_smarty_tpl->tpl_vars['href']->value));?>
</li>
                            <?php } else { ?>
                                <li class="disabled"><a><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['link_text']->value, ENT_QUOTES, 'UTF-8');?>
</a></li>
                            <?php }?>
                        <?php } else { ?>
                            <li><?php ob_start();?><?php echo $_smarty_tpl->__("settings");?>
<?php $_tmp2=ob_get_clean();?><?php echo $_smarty_tpl->getSubTemplate ("common/popupbox.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('id'=>"group".((string)$_smarty_tpl->tpl_vars['key']->value),'text'=>$_tmp2.": ".((string)$_smarty_tpl->tpl_vars['a']->value['name']),'act'=>(($tmp = @$_smarty_tpl->tpl_vars['act']->value)===null||$tmp==='' ? "link" : $tmp),'link_text'=>$_smarty_tpl->tpl_vars['link_text']->value,'href'=>$_smarty_tpl->tpl_vars['a']->value['url'],'is_promo'=>!$_smarty_tpl->tpl_vars['a']->value['snapshot_correct']), 0);?>
</li>
                        <?php }?>
                        <?php if ($_smarty_tpl->tpl_vars['a']->value['delete_url']) {?>
                            <li><?php smarty_template_function_btn($_smarty_tpl,array('type'=>"list",'class'=>"cm-confirm",'text'=>__("uninstall"),'data'=>array('data-ca-target-id'=>'addons_list,header_navbar,header_subnav'),'href'=>$_smarty_tpl->tpl_vars['a']->value['delete_url']));?>
</li>
                        <?php }?>
                    <?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>
                    <?php smarty_template_function_dropdown($_smarty_tpl,array('content'=>Smarty::$_smarty_vars['capture']['tools_list']));?>

                    </div>
                <?php }?>

            </td>
            <td width="15%">
                <?php if ($_smarty_tpl->tpl_vars['a']->value['status']=='N') {?>
                    <?php if (!$_smarty_tpl->tpl_vars['hide_for_vendor']->value) {?>
                    <div class="pull-right">
                        <a class="btn lowercase <?php if ($_smarty_tpl->tpl_vars['a']->value['snapshot_correct']) {?>cm-ajax cm-ajax-full-render<?php } else { ?>cm-promo-popup<?php }?>" href="<?php echo htmlspecialchars(fn_url("addons.install?addon=".((string)$_smarty_tpl->tpl_vars['key']->value)."&redirect_url=".((string)$_smarty_tpl->tpl_vars['c_url']->value)), ENT_QUOTES, 'UTF-8');?>
" data-ca-target-id="addons_list,header_navbar,header_subnav"><?php echo $_smarty_tpl->__("install");?>
</a>
                    </div>
                    <?php }?>
                <?php } else { ?>
                    <?php if ($_smarty_tpl->tpl_vars['show_installed']->value) {?>
                        <div class="pull-right nowrap">
                            <?php if (!$_smarty_tpl->tpl_vars['a']->value['snapshot_correct']) {?><?php $_smarty_tpl->tpl_vars['status_meta'] = new Smarty_variable("cm-promo-popup", null, 0);?><?php } else { ?><?php $_smarty_tpl->tpl_vars['status_meta'] = new Smarty_variable('', null, 0);?><?php }?>
                            <?php echo $_smarty_tpl->getSubTemplate ("common/select_popup.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('popup_additional_class'=>"dropleft",'id'=>$_smarty_tpl->tpl_vars['key']->value,'status'=>$_smarty_tpl->tpl_vars['a']->value['status'],'hide_for_vendor'=>$_smarty_tpl->tpl_vars['hide_for_vendor']->value,'non_editable'=>false,'status_meta'=>$_smarty_tpl->tpl_vars['status_meta']->value,'display'=>$_smarty_tpl->tpl_vars['display']->value,'update_controller'=>"addons"), 0);?>

                        </div>
                    <?php } else { ?>
                        <span class="pull-right label label-info"><?php echo $_smarty_tpl->__("installed");?>
</span>
                    <?php }?>
                <?php }?>
            </td>
        <!--addon_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['key']->value, ENT_QUOTES, 'UTF-8');?>
--></tr>
    <?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>
    
    <?php if ($_smarty_tpl->tpl_vars['show_installed']->value) {?>
        <?php if ($_smarty_tpl->tpl_vars['a']->value['status']=='A'||$_smarty_tpl->tpl_vars['a']->value['status']=='D') {?>
            <?php echo Smarty::$_smarty_vars['capture']['addons_row'];?>

        <?php }?>
    <?php } else { ?>
        <?php echo Smarty::$_smarty_vars['capture']['addons_row'];?>

    <?php }?>

<?php } ?>
</table>
<?php } else { ?>
    <p class="no-items"><?php echo $_smarty_tpl->__("no_data");?>
</p>
<?php }?>
<?php }} ?>
