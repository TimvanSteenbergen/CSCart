<?php /* Smarty version Smarty-3.1.18, created on 2014-11-24 17:19:46
         compiled from "/var/www/html/workspace/cscart/design/backend/templates/views/profiles/update.tpl" */ ?>
<?php /*%%SmartyHeaderCode:251564154733e82eb51a3-53989251%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '88af5ed7aaaf602226cc6447866bc950e3bab331' => 
    array (
      0 => '/var/www/html/workspace/cscart/design/backend/templates/views/profiles/update.tpl',
      1 => 1413383305,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '251564154733e82eb51a3-53989251',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'user_data' => 0,
    'runtime' => 0,
    'id' => 0,
    'auth' => 0,
    'hide_inputs' => 0,
    'selected_section' => 0,
    'user_type' => 0,
    'zero_company_id_name_lang_var' => 0,
    'settings' => 0,
    'profile_fields' => 0,
    'usergroups' => 0,
    'usergroup' => 0,
    'ug_status' => 0,
    'show_api_tab' => 0,
    'hide_api_checkbox' => 0,
    'new_api_key' => 0,
    '_user_desc' => 0,
    '_title' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_54733e830bf912_17283170',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54733e830bf912_17283170')) {function content_54733e830bf912_17283170($_smarty_tpl) {?><?php if (!is_callable('smarty_block_hook')) include '/var/www/html/workspace/cscart/app/functions/smarty_plugins/block.hook.php';
?><?php
fn_preload_lang_vars(array('contact_information','user_profile_info','text_multiprofile_notice','billing_address','shipping_address','shipping_address','usergroup','status','no_data','allow_api_access','api_key','new_profile','editing_profile','editing_profile','editing_profile','view_all_orders','act_on_behalf','create','notify_user'));
?>
<?php if ($_smarty_tpl->tpl_vars['user_data']->value) {?>
    <?php $_smarty_tpl->tpl_vars["id"] = new Smarty_variable($_smarty_tpl->tpl_vars['user_data']->value['user_id'], null, 0);?>
<?php } else { ?>
    <?php $_smarty_tpl->tpl_vars["id"] = new Smarty_variable(0, null, 0);?>
<?php }?>

<?php echo $_smarty_tpl->getSubTemplate ("views/profiles/components/profiles_scripts.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>


<form name="profile_form" action="<?php echo htmlspecialchars(fn_url(''), ENT_QUOTES, 'UTF-8');?>
" method="post" class="form-horizontal form-edit form-table <?php if (($_smarty_tpl->tpl_vars['runtime']->value['company_id']&&$_smarty_tpl->tpl_vars['id']->value&&$_smarty_tpl->tpl_vars['user_data']->value['company_id']!=$_smarty_tpl->tpl_vars['runtime']->value['company_id']&&$_smarty_tpl->tpl_vars['id']->value!=$_smarty_tpl->tpl_vars['auth']->value['user_id'])||$_smarty_tpl->tpl_vars['hide_inputs']->value) {?> cm-hide-inputs<?php }?>">
<?php $_smarty_tpl->_capture_stack[0][] = array("mainbox", null, null); ob_start(); ?>

<?php $_smarty_tpl->_capture_stack[0][] = array("tabsbox", null, null); ob_start(); ?>
    <?php $_smarty_tpl->tpl_vars['hide_inputs'] = new Smarty_variable(false, null, 0);?>

    <?php if ($_smarty_tpl->tpl_vars['user_data']->value['user_type']==$_smarty_tpl->tpl_vars['auth']->value['user_type']&&$_smarty_tpl->tpl_vars['user_data']->value['is_root']=='Y'&&$_smarty_tpl->tpl_vars['user_data']->value['user_id']!=$_smarty_tpl->tpl_vars['auth']->value['user_id']&&(!$_smarty_tpl->tpl_vars['user_data']->value['company_id']||$_smarty_tpl->tpl_vars['user_data']->value['company_id']==$_smarty_tpl->tpl_vars['auth']->value['company_id'])) {?>
        <?php $_smarty_tpl->tpl_vars['hide_inputs'] = new Smarty_variable(true, null, 0);?>
    <?php }?>

    <?php if (fn_allowed_for("ULTIMATE")&&!fn_allow_save_object($_smarty_tpl->tpl_vars['user_data']->value,"users")&&$_smarty_tpl->tpl_vars['id']->value&&!fn_ult_check_users_usergroup_companies($_smarty_tpl->tpl_vars['id']->value)&&$_smarty_tpl->tpl_vars['user_data']->value['user_id']!=$_smarty_tpl->tpl_vars['auth']->value['user_id']) {?>
        <?php $_smarty_tpl->tpl_vars['hide_inputs'] = new Smarty_variable(true, null, 0);?>
    <?php }?>

    <?php if (fn_allowed_for("MULTIVENDOR")&&(!fn_allow_save_object($_smarty_tpl->tpl_vars['user_data']->value,"users")||$_smarty_tpl->tpl_vars['runtime']->value['company_id']&&($_REQUEST['user_type']=='C'||fn_string_not_empty($_smarty_tpl->tpl_vars['user_data']->value['company_id'])&&$_smarty_tpl->tpl_vars['user_data']->value['company_id']!=$_smarty_tpl->tpl_vars['runtime']->value['company_id']))&&$_smarty_tpl->tpl_vars['user_data']->value['user_id']!=$_smarty_tpl->tpl_vars['auth']->value['user_id']) {?>
        <?php $_smarty_tpl->tpl_vars['hide_inputs'] = new Smarty_variable(true, null, 0);?>
    <?php }?>

    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
" />
    <input type="hidden" class="cm-no-hide-input" name="selected_section" id="selected_section" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['selected_section']->value, ENT_QUOTES, 'UTF-8');?>
" />
    <input type="hidden" class="cm-no-hide-input" name="user_type" value="<?php echo htmlspecialchars($_REQUEST['user_type'], ENT_QUOTES, 'UTF-8');?>
" />
    
    <div id="content_general">
        <?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"profiles:general_content")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"profiles:general_content"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

            <?php echo $_smarty_tpl->getSubTemplate ("views/profiles/components/profiles_account.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>


            <?php if ((fn_allowed_for("ULTIMATE")||$_smarty_tpl->tpl_vars['user_type']->value=="V")&&$_smarty_tpl->tpl_vars['id']->value!=$_smarty_tpl->tpl_vars['auth']->value['user_id']) {?>

                <?php $_smarty_tpl->tpl_vars['zero_company_id_name_lang_var'] = new Smarty_variable(false, null, 0);?>
                <?php if (fn_allowed_for("ULTIMATE")&&fn_check_user_type_admin_area($_smarty_tpl->tpl_vars['user_type']->value)) {?>
                    <?php $_smarty_tpl->tpl_vars['zero_company_id_name_lang_var'] = new Smarty_variable('all_vendors', null, 0);?>
                <?php }?>

                <?php echo $_smarty_tpl->getSubTemplate ("views/companies/components/company_field.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('name'=>"user_data[company_id]",'id'=>"user_data_company_id",'selected'=>$_smarty_tpl->tpl_vars['user_data']->value['company_id'],'zero_company_id_name_lang_var'=>$_smarty_tpl->tpl_vars['zero_company_id_name_lang_var']->value,'disable_company_picker'=>$_smarty_tpl->tpl_vars['hide_inputs']->value), 0);?>


            <?php } else { ?>
                <input type="hidden" name="user_data[company_id]" value="<?php echo htmlspecialchars((($tmp = @$_smarty_tpl->tpl_vars['user_data']->value['company_id'])===null||$tmp==='' ? 0 : $tmp), ENT_QUOTES, 'UTF-8');?>
">
            <?php }?>
        <?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"profiles:general_content"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

        
        <?php echo $_smarty_tpl->getSubTemplate ("views/profiles/components/profile_fields.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('section'=>"C",'title'=>__("contact_information")), 0);?>


        <?php if ($_smarty_tpl->tpl_vars['settings']->value['General']['user_multiple_profiles']=="Y"&&$_smarty_tpl->tpl_vars['id']->value) {?>
            <?php echo $_smarty_tpl->getSubTemplate ("common/subheader.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('title'=>__("user_profile_info")), 0);?>

            <p class="form-note"><?php echo $_smarty_tpl->__("text_multiprofile_notice");?>
</p>
            <?php echo $_smarty_tpl->getSubTemplate ("views/profiles/components/multiple_profiles.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

        <?php }?>

        <?php if ($_smarty_tpl->tpl_vars['profile_fields']->value['B']) {?>
            <?php echo $_smarty_tpl->getSubTemplate ("views/profiles/components/profile_fields.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('section'=>"B",'title'=>__("billing_address")), 0);?>

            <?php echo $_smarty_tpl->getSubTemplate ("views/profiles/components/profile_fields.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('section'=>"S",'title'=>__("shipping_address"),'body_id'=>"sa",'shipping_flag'=>fn_compare_shipping_billing($_smarty_tpl->tpl_vars['profile_fields']->value)), 0);?>

        <?php } else { ?>
            <?php echo $_smarty_tpl->getSubTemplate ("views/profiles/components/profile_fields.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('section'=>"S",'title'=>__("shipping_address"),'shipping_flag'=>false), 0);?>

        <?php }?>
        </div>
    <?php if (!fn_allowed_for("ULTIMATE:FREE")) {?>
        <?php if ($_smarty_tpl->tpl_vars['id']->value&&(((!fn_check_user_type_admin_area($_smarty_tpl->tpl_vars['user_data']->value)||!$_smarty_tpl->tpl_vars['user_data']->value['user_id'])&&!$_smarty_tpl->tpl_vars['runtime']->value['company_id'])||(fn_check_user_type_admin_area($_smarty_tpl->tpl_vars['user_data']->value)&&$_smarty_tpl->tpl_vars['user_data']->value['user_id']&&$_smarty_tpl->tpl_vars['usergroups']->value&&!$_smarty_tpl->tpl_vars['runtime']->value['company_id']&&$_smarty_tpl->tpl_vars['auth']->value['is_root']=='Y'&&($_smarty_tpl->tpl_vars['user_data']->value['company_id']!=0||($_smarty_tpl->tpl_vars['user_data']->value['company_id']==0&&$_smarty_tpl->tpl_vars['user_data']->value['is_root']!='Y')))||($_smarty_tpl->tpl_vars['user_data']->value['user_type']=='V'&&$_smarty_tpl->tpl_vars['runtime']->value['company_id']&&$_smarty_tpl->tpl_vars['auth']->value['is_root']=='Y'&&$_smarty_tpl->tpl_vars['user_data']->value['user_id']!=$_smarty_tpl->tpl_vars['auth']->value['user_id']&&$_smarty_tpl->tpl_vars['user_data']->value['company_id']==$_smarty_tpl->tpl_vars['runtime']->value['company_id']))) {?>

            <div id="content_usergroups" class="cm-hide-save-button">
                <?php if ($_smarty_tpl->tpl_vars['usergroups']->value) {?>
                <table width="100%" class="table table-middle">
                <thead>
                <tr>
                    <th width="50%"><?php echo $_smarty_tpl->__("usergroup");?>
</th>
                    <th class="right" width="10%"><?php echo $_smarty_tpl->__("status");?>
</th>
                </tr>
                </thead>
                <?php  $_smarty_tpl->tpl_vars['usergroup'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['usergroup']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['usergroups']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['usergroup']->key => $_smarty_tpl->tpl_vars['usergroup']->value) {
$_smarty_tpl->tpl_vars['usergroup']->_loop = true;
?>
                    <tr>
                        <td><a href="<?php echo htmlspecialchars(fn_url("usergroups.manage#group".((string)$_smarty_tpl->tpl_vars['usergroup']->value['usergroup_id'])), ENT_QUOTES, 'UTF-8');?>
"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['usergroup']->value['usergroup'], ENT_QUOTES, 'UTF-8');?>
</a></td>
                        <td class="right">
                            <?php if ($_smarty_tpl->tpl_vars['user_data']->value['usergroups'][$_smarty_tpl->tpl_vars['usergroup']->value['usergroup_id']]) {?>
                                <?php $_smarty_tpl->tpl_vars["ug_status"] = new Smarty_variable($_smarty_tpl->tpl_vars['user_data']->value['usergroups'][$_smarty_tpl->tpl_vars['usergroup']->value['usergroup_id']]['status'], null, 0);?>
                            <?php } else { ?>
                                <?php $_smarty_tpl->tpl_vars["ug_status"] = new Smarty_variable("F", null, 0);?>
                            <?php }?>
                            <?php echo $_smarty_tpl->getSubTemplate ("common/select_popup.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('id'=>$_smarty_tpl->tpl_vars['usergroup']->value['usergroup_id'],'status'=>$_smarty_tpl->tpl_vars['ug_status']->value,'hidden'=>'','items_status'=>fn_get_predefined_statuses("profiles"),'extra'=>"&user_id=".((string)$_smarty_tpl->tpl_vars['id']->value),'update_controller'=>"usergroups",'notify'=>true,'hide_for_vendor'=>$_smarty_tpl->tpl_vars['runtime']->value['company_id']), 0);?>

                        </td>
                    </tr>
                <?php } ?>
                </table>
                <?php } else { ?>
                    <p class="no-items"><?php echo $_smarty_tpl->__("no_data");?>
</p>
                <?php }?>
            </div>
        <?php }?>
    <?php }?>

    <div id="content_addons">
        <?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"profiles:detailed_content")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"profiles:detailed_content"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

        <?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"profiles:detailed_content"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

    </div>
    <?php if ($_smarty_tpl->tpl_vars['show_api_tab']->value) {?>
        <div id="content_api">
            <div class="control-group <?php if ($_smarty_tpl->tpl_vars['hide_api_checkbox']->value) {?>hidden<?php }?>">
                <div class="controls">
                    <label class="checkbox" for="sw_api_container">
                    <input <?php if ($_smarty_tpl->tpl_vars['user_data']->value['api_key']!='') {?>checked="checked"<?php }?> class="cm-combination" type="checkbox" name="user_api_status" value="Y" id="sw_api_container" /><?php echo $_smarty_tpl->__("allow_api_access");?>
</label>
                </div>
            </div>

            <div id="api_container" <?php if ($_smarty_tpl->tpl_vars['user_data']->value['api_key']=='') {?>class="hidden"<?php }?>>
                <div class="control-group">
                    <label class="control-label"><?php echo $_smarty_tpl->__("api_key");?>
</label>
                    <div class="controls">
                        <input type="text" class="input-large" name="user_data[api_key]" value="<?php if ($_smarty_tpl->tpl_vars['user_data']->value['api_key']) {?><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['user_data']->value['api_key'], ENT_QUOTES, 'UTF-8');?>
<?php } else { ?><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['new_api_key']->value, ENT_QUOTES, 'UTF-8');?>
<?php }?>" readonly="readonly"/>
                    </div>
                </div>
            </div>
        </div>
    <?php }?>

    <?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"profiles:tabs_content")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"profiles:tabs_content"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

    <?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"profiles:tabs_content"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

    <?php if (!fn_allow_save_object($_smarty_tpl->tpl_vars['user_data']->value,"users")&&$_smarty_tpl->tpl_vars['id']->value&&$_smarty_tpl->tpl_vars['user_data']->value['user_id']!=$_smarty_tpl->tpl_vars['auth']->value['user_id']||$_smarty_tpl->tpl_vars['hide_inputs']->value) {?>
        <?php $_smarty_tpl->tpl_vars["hide_first_button"] = new Smarty_variable(true, null, 0);?>
    <?php }?>

    <?php if ($_smarty_tpl->tpl_vars['id']->value) {?>
        <?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"profiles:tabs_extra")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"profiles:tabs_extra"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

        <?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"profiles:tabs_extra"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

    <?php }?>
<?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>

<?php echo $_smarty_tpl->getSubTemplate ("common/tabsbox.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('content'=>Smarty::$_smarty_vars['capture']['tabsbox'],'group_name'=>$_smarty_tpl->tpl_vars['runtime']->value['controller'],'active_tab'=>$_smarty_tpl->tpl_vars['selected_section']->value,'track'=>true), 0);?>


<?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>

<?php if (!$_smarty_tpl->tpl_vars['id']->value) {?>
    <?php $_smarty_tpl->tpl_vars["_user_desc"] = new Smarty_variable(fn_get_user_type_description($_smarty_tpl->tpl_vars['user_type']->value), null, 0);?>
    <?php ob_start();?><?php echo $_smarty_tpl->__("new_profile");?>
<?php $_tmp1=ob_get_clean();?><?php $_smarty_tpl->tpl_vars["_title"] = new Smarty_variable($_tmp1." (".((string)$_smarty_tpl->tpl_vars['_user_desc']->value).")", null, 0);?>
<?php } else { ?>
    <?php if ($_smarty_tpl->tpl_vars['user_data']->value['firstname']) {?>
        <?php ob_start();?><?php echo $_smarty_tpl->__("editing_profile");?>
<?php $_tmp2=ob_get_clean();?><?php $_smarty_tpl->tpl_vars["_title"] = new Smarty_variable($_tmp2.": ".((string)$_smarty_tpl->tpl_vars['user_data']->value['firstname'])." ".((string)$_smarty_tpl->tpl_vars['user_data']->value['lastname']), null, 0);?>
        <?php } elseif ($_smarty_tpl->tpl_vars['user_data']->value['b_firstname']) {?>
        <?php ob_start();?><?php echo $_smarty_tpl->__("editing_profile");?>
<?php $_tmp3=ob_get_clean();?><?php $_smarty_tpl->tpl_vars["_title"] = new Smarty_variable($_tmp3.": ".((string)$_smarty_tpl->tpl_vars['user_data']->value['b_firstname'])." ".((string)$_smarty_tpl->tpl_vars['user_data']->value['b_lastname']), null, 0);?>
        <?php } else { ?>
        <?php ob_start();?><?php echo $_smarty_tpl->__("editing_profile");?>
<?php $_tmp4=ob_get_clean();?><?php $_smarty_tpl->tpl_vars["_title"] = new Smarty_variable($_tmp4.": ".((string)$_smarty_tpl->tpl_vars['user_data']->value['email']), null, 0);?>
    <?php }?>
<?php }?>

<?php $_smarty_tpl->tpl_vars['_title'] = new Smarty_variable(preg_replace('!<[^>]*?>!', ' ', $_smarty_tpl->tpl_vars['_title']->value), null, 0);?>

<?php $_smarty_tpl->_capture_stack[0][] = array("buttons", null, null); ob_start(); ?>
    <?php $_smarty_tpl->_capture_stack[0][] = array("tools_list", null, null); ob_start(); ?>
        <?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"profiles:update_tools_list")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"profiles:update_tools_list"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

        <?php if ($_smarty_tpl->tpl_vars['user_data']->value['user_type']=="C") {?>
            <li><?php smarty_template_function_btn($_smarty_tpl,array('type'=>"list",'text'=>__("view_all_orders"),'href'=>"orders.manage?user_id=".((string)$_smarty_tpl->tpl_vars['id']->value)));?>
</li>
        <?php }?>
        <?php if (fn_user_need_login($_smarty_tpl->tpl_vars['user_data']->value['user_type'])&&(!$_smarty_tpl->tpl_vars['runtime']->value['company_id']||$_smarty_tpl->tpl_vars['runtime']->value['company_id']==$_smarty_tpl->tpl_vars['auth']->value['company_id'])&&$_smarty_tpl->tpl_vars['user_data']->value['user_id']!=$_smarty_tpl->tpl_vars['auth']->value['user_id']&&!($_smarty_tpl->tpl_vars['user_data']->value['user_type']=='A'&&$_smarty_tpl->tpl_vars['user_data']->value['is_root']=='Y'&&!$_smarty_tpl->tpl_vars['user_data']->value['company_id'])) {?>
            <li><?php smarty_template_function_btn($_smarty_tpl,array('type'=>"list",'target'=>"_blank",'text'=>__("act_on_behalf"),'href'=>"profiles.act_as_user?user_id=".((string)$_smarty_tpl->tpl_vars['id']->value)));?>
</li>
        <?php }?>
        <?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"profiles:update_tools_list"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

    <?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>
    <?php if ($_smarty_tpl->tpl_vars['id']->value&&trim(Smarty::$_smarty_vars['capture']['tools_list'])!=='') {?>
        <?php smarty_template_function_dropdown($_smarty_tpl,array('content'=>Smarty::$_smarty_vars['capture']['tools_list']));?>

    <?php }?>
<div class="btn-group btn-hover dropleft">
    <?php if ($_smarty_tpl->tpl_vars['id']->value) {?>
        <?php echo $_smarty_tpl->getSubTemplate ("buttons/save_changes.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('but_meta'=>"dropdown-toggle",'but_role'=>"submit-link",'but_name'=>"dispatch[profiles.".((string)$_smarty_tpl->tpl_vars['runtime']->value['mode'])."]",'but_target_form'=>"profile_form",'save'=>$_smarty_tpl->tpl_vars['id']->value), 0);?>

    <?php } else { ?>
        <?php echo $_smarty_tpl->getSubTemplate ("buttons/button.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('but_text'=>__("create"),'but_meta'=>"dropdown-toggle",'but_role'=>"submit-link",'but_name'=>"dispatch[profiles.".((string)$_smarty_tpl->tpl_vars['runtime']->value['mode'])."]",'but_target_form'=>"profile_form",'save'=>$_smarty_tpl->tpl_vars['id']->value), 0);?>

    <?php }?>
    <ul class="dropdown-menu">
        <li><a><input type="checkbox" name="notify_customer" value="Y" checked="checked"  id="notify_customer" />
            <?php echo $_smarty_tpl->__("notify_user");?>
</a></li>
    </ul>
</div>

<?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>

<?php echo $_smarty_tpl->getSubTemplate ("common/mainbox.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('title'=>$_smarty_tpl->tpl_vars['_title']->value,'content'=>Smarty::$_smarty_vars['capture']['mainbox'],'buttons'=>Smarty::$_smarty_vars['capture']['buttons']), 0);?>

</form><?php }} ?>
