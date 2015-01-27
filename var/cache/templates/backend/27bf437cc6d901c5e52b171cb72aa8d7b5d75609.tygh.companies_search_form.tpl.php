<?php /* Smarty version Smarty-3.1.18, created on 2014-11-24 17:22:20
         compiled from "/var/www/html/workspace/cscart/design/backend/templates/views/companies/components/companies_search_form.tpl" */ ?>
<?php /*%%SmartyHeaderCode:129356563254733f1cb28d05-94290830%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '27bf437cc6d901c5e52b171cb72aa8d7b5d75609' => 
    array (
      0 => '/var/www/html/workspace/cscart/design/backend/templates/views/companies/components/companies_search_form.tpl',
      1 => 1413383303,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '129356563254733f1cb28d05-94290830',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'in_popup' => 0,
    'form_meta' => 0,
    'selected_section' => 0,
    'search' => 0,
    'company_id' => 0,
    'put_request_vars' => 0,
    'v' => 0,
    'k' => 0,
    'extra' => 0,
    'countries' => 0,
    'code' => 0,
    'country' => 0,
    'dispatch' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_54733f1cbb4cd5_89820663',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54733f1cbb4cd5_89820663')) {function content_54733f1cbb4cd5_89820663($_smarty_tpl) {?><?php if (!is_callable('smarty_block_hook')) include '/var/www/html/workspace/cscart/app/functions/smarty_plugins/block.hook.php';
?><?php
fn_preload_lang_vars(array('search','name','email','address','city','country','select_country','state','select_state','status','active','pending','new','disabled','zip_postal_code','phone','url','fax'));
?>
<?php if ($_smarty_tpl->tpl_vars['in_popup']->value) {?>
<div class="adv-search">
<div class="group">
    <?php } else { ?>
<div class="sidebar-row">
    <h6><?php echo $_smarty_tpl->__("search");?>
</h6>
<?php }?>

<form name="companies_search_form" action="<?php echo htmlspecialchars(fn_url(''), ENT_QUOTES, 'UTF-8');?>
" method="get" class="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['form_meta']->value, ENT_QUOTES, 'UTF-8');?>
">
<?php $_smarty_tpl->_capture_stack[0][] = array("simple_search", null, null); ob_start(); ?>

<?php if ($_REQUEST['redirect_url']) {?>
<input type="hidden" name="redirect_url" value="<?php echo htmlspecialchars($_REQUEST['redirect_url'], ENT_QUOTES, 'UTF-8');?>
" />
<?php }?>

<?php if ($_smarty_tpl->tpl_vars['selected_section']->value!='') {?>
<input type="hidden" id="selected_section" name="selected_section" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['selected_section']->value, ENT_QUOTES, 'UTF-8');?>
" />
<?php }?>

<?php if ($_smarty_tpl->tpl_vars['search']->value['user_type']) {?>
<input type="hidden" name="user_type" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['search']->value['user_type'], ENT_QUOTES, 'UTF-8');?>
" />
<?php }?>

<?php if ($_smarty_tpl->tpl_vars['company_id']->value) {?>
<input type="hidden" name="company_id" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['company_id']->value, ENT_QUOTES, 'UTF-8');?>
" />
<?php }?>

<?php if ($_smarty_tpl->tpl_vars['put_request_vars']->value) {?>
    <?php  $_smarty_tpl->tpl_vars["v"] = new Smarty_Variable; $_smarty_tpl->tpl_vars["v"]->_loop = false;
 $_smarty_tpl->tpl_vars["k"] = new Smarty_Variable;
 $_from = $_REQUEST; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars["v"]->key => $_smarty_tpl->tpl_vars["v"]->value) {
$_smarty_tpl->tpl_vars["v"]->_loop = true;
 $_smarty_tpl->tpl_vars["k"]->value = $_smarty_tpl->tpl_vars["v"]->key;
?>
        <?php if ($_smarty_tpl->tpl_vars['v']->value&&$_smarty_tpl->tpl_vars['k']->value!="callback") {?> 
            <input type="hidden" name="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['k']->value, ENT_QUOTES, 'UTF-8');?>
" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['v']->value, ENT_QUOTES, 'UTF-8');?>
" />
        <?php }?>
    <?php } ?>
<?php }?>

<?php echo $_smarty_tpl->tpl_vars['extra']->value;?>

<div class="sidebar-field">
    <label for="elm_name"><?php echo $_smarty_tpl->__("name");?>
</label>
    <input type="text" name="company" id="elm_name" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['search']->value['company'], ENT_QUOTES, 'UTF-8');?>
" />
</div>

<div class="sidebar-field">
    <label for="elm_email"><?php echo $_smarty_tpl->__("email");?>
</label>
    <input type="text" name="email" id="elm_email" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['search']->value['email'], ENT_QUOTES, 'UTF-8');?>
" />
</div>

<?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>

<?php $_smarty_tpl->_capture_stack[0][] = array("advanced_search", null, null); ob_start(); ?>
<div class="row-fluid">
<div class="group span6 form-horizontal">
    <div class="control-group">
        <label for="elm_address" class='control-label'><?php echo $_smarty_tpl->__("address");?>
</label>
        <div class="controls">
        <input type="text" name="address" id="elm_address" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['search']->value['address'], ENT_QUOTES, 'UTF-8');?>
" />
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="elm_city"><?php echo $_smarty_tpl->__("city");?>
</label>
        <div class="controls">
        <input type="text" name="city" id="elm_city" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['search']->value['city'], ENT_QUOTES, 'UTF-8');?>
" />
        </div>
    </div>
    <div class="control-group">
        <label for="srch_country" class="control-label"><?php echo $_smarty_tpl->__("country");?>
</label>
        <div class="controls">
            <select id="srch_country" name="country" class="cm-country cm-location-search">
                <option value="">- <?php echo $_smarty_tpl->__("select_country");?>
 -</option>
                <?php  $_smarty_tpl->tpl_vars["country"] = new Smarty_Variable; $_smarty_tpl->tpl_vars["country"]->_loop = false;
 $_smarty_tpl->tpl_vars["code"] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['countries']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars["country"]->key => $_smarty_tpl->tpl_vars["country"]->value) {
$_smarty_tpl->tpl_vars["country"]->_loop = true;
 $_smarty_tpl->tpl_vars["code"]->value = $_smarty_tpl->tpl_vars["country"]->key;
?>
                <option value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['code']->value, ENT_QUOTES, 'UTF-8');?>
" <?php if ($_smarty_tpl->tpl_vars['search']->value['country']==$_smarty_tpl->tpl_vars['code']->value) {?>selected="selected"<?php }?>><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['country']->value, ENT_QUOTES, 'UTF-8');?>
</option>
                <?php } ?>
            </select>
        </div>
    </div>

    <div class="control-group">
        <label for="srch_state" class="control-label"><?php echo $_smarty_tpl->__("state");?>
</label>
        <div class="controls">
        <select id="srch_state" class="cm-state cm-location-search hidden" name="state">
            <option value="">- <?php echo $_smarty_tpl->__("select_state");?>
 -</option>
        </select>
        <input class="cm-state cm-location-search" type="text" id="srch_state_d" name="state" maxlength="64" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['search']->value['state'], ENT_QUOTES, 'UTF-8');?>
" disabled="disabled"/>
        </div>
    </div>

    <?php if (!fn_allowed_for("ULTIMATE")) {?>
    <div class="control-group">
        <label class="control-label" for="status"><?php echo $_smarty_tpl->__("status");?>
</label>
        <div class="controls">
        <select name="status" id="status">
            <option value="">--</option>
            <option value="A" <?php if ($_smarty_tpl->tpl_vars['search']->value['status']=="A") {?>selected="selected"<?php }?>><?php echo $_smarty_tpl->__("active");?>
</option>
            <option value="P" <?php if ($_smarty_tpl->tpl_vars['search']->value['status']=="P") {?>selected="selected"<?php }?>><?php echo $_smarty_tpl->__("pending");?>
</option>
            <option value="N" <?php if ($_smarty_tpl->tpl_vars['search']->value['status']=="N") {?>selected="selected"<?php }?>><?php echo $_smarty_tpl->__("new");?>
</option>
            <option value="D" <?php if ($_smarty_tpl->tpl_vars['search']->value['status']=="D") {?>selected="selected"<?php }?>><?php echo $_smarty_tpl->__("disabled");?>
</option>
        </select>
        </div>
    </div>
    <?php }?>

</div>
<div class="group span6 form-horizontal">
    <div class="control-group">
        <label class="control-label" for="elm_zipcode"><?php echo $_smarty_tpl->__("zip_postal_code");?>
</label>
        <div class="controls">
        <input type="text" name="zipcode" id="elm_zipcode" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['search']->value['zipcode'], ENT_QUOTES, 'UTF-8');?>
" />
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="elm_phone"><?php echo $_smarty_tpl->__("phone");?>
</label>
        <div class="controls">
        <input type="text" name="phone" id="elm_phone" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['search']->value['phone'], ENT_QUOTES, 'UTF-8');?>
" />
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="elm_url"><?php echo $_smarty_tpl->__("url");?>
</label>
        <div class="controls">
        <input type="text" name="url" id="elm_url" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['search']->value['url'], ENT_QUOTES, 'UTF-8');?>
"/>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="elm_fax"><?php echo $_smarty_tpl->__("fax");?>
</label>
        <div class="controls">
        <input type="text" name="fax" id="elm_fax" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['search']->value['fax'], ENT_QUOTES, 'UTF-8');?>
" /></div>
    </div>

    <?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"companies:search_form")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"companies:search_form"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

<?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"companies:search_form"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>


</div>
</div>
<?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>

<?php echo $_smarty_tpl->getSubTemplate ("common/advanced_search.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('simple_search'=>Smarty::$_smarty_vars['capture']['simple_search'],'advanced_search'=>Smarty::$_smarty_vars['capture']['advanced_search'],'dispatch'=>$_smarty_tpl->tpl_vars['dispatch']->value,'view_type'=>"companies",'in_popup'=>$_smarty_tpl->tpl_vars['in_popup']->value), 0);?>


</form>

<?php if ($_smarty_tpl->tpl_vars['in_popup']->value) {?>
</div></div>
    <?php } else { ?>
</div><hr>
<?php }?>
<?php }} ?>
