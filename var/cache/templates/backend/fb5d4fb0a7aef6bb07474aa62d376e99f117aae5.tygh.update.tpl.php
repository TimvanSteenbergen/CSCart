<?php /* Smarty version Smarty-3.1.18, created on 2014-11-24 17:22:27
         compiled from "/var/www/html/workspace/cscart/design/backend/templates/views/companies/update.tpl" */ ?>
<?php /*%%SmartyHeaderCode:70091608354733f23937101-77884875%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'fb5d4fb0a7aef6bb07474aa62d376e99f117aae5' => 
    array (
      0 => '/var/www/html/workspace/cscart/design/backend/templates/views/companies/update.tpl',
      1 => 1413383303,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '70091608354733f23937101-77884875',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'company_data' => 0,
    'form_class' => 0,
    'id' => 0,
    'runtime' => 0,
    'clone_schema' => 0,
    'object' => 0,
    'label' => 0,
    'object_data' => 0,
    'theme_info' => 0,
    'current_style' => 0,
    'languages' => 0,
    'lang_code' => 0,
    'language' => 0,
    'settings' => 0,
    'primary_currency' => 0,
    'currencies' => 0,
    'countries' => 0,
    '_country' => 0,
    'code' => 0,
    'country' => 0,
    'states' => 0,
    '_state' => 0,
    'state' => 0,
    'company_settings' => 0,
    'item' => 0,
    'section' => 0,
    'countries_list' => 0,
    'shippings' => 0,
    'shipping_id' => 0,
    'shipping' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_54733f23b03a38_70748831',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54733f23b03a38_70748831')) {function content_54733f23b03a38_70748831($_smarty_tpl) {?><?php if (!is_callable('smarty_block_hook')) include '/var/www/html/workspace/cscart/app/functions/smarty_plugins/block.hook.php';
if (!is_callable('smarty_modifier_unpuny')) include '/var/www/html/workspace/cscart/app/functions/smarty_plugins/modifier.unpuny.php';
if (!is_callable('smarty_modifier_in_array')) include '/var/www/html/workspace/cscart/app/functions/smarty_plugins/modifier.in_array.php';
?><?php
fn_preload_lang_vars(array('use_existing_store','store','none','recommended','information','vendor_name','storefront_url','ttc_storefront_url','secure_storefront_url','design','store_theme','goto_theme_configuration','status','active','pending','disabled','language','create_administrator_account','account_name','first_name','last_name','vendor_commission','request_account_name','contact_information','contact_information','email','phone','url','fax','shipping_address','shipping_address','address','city','country','select_country','state','select_state','zip_postal_code','settings','company','description','text_all_categories_included','redirect_customer_from_storefront','entry_page','none','index','all_pages','countries','shipping_methods','available_for_vendor','disabled','no_data','menu','view_vendor_products','view_vendor_categories','view_vendor_users','view_vendor_orders','editing_vendor','new_vendor'));
?>
<?php if ($_smarty_tpl->tpl_vars['company_data']->value['company_id']) {?>
    <?php $_smarty_tpl->tpl_vars["id"] = new Smarty_variable($_smarty_tpl->tpl_vars['company_data']->value['company_id'], null, 0);?>
<?php } else { ?>
    <?php $_smarty_tpl->tpl_vars["id"] = new Smarty_variable(0, null, 0);?>
<?php }?>


<?php echo $_smarty_tpl->getSubTemplate ("views/profiles/components/profiles_scripts.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>


<?php $_smarty_tpl->_capture_stack[0][] = array("mainbox", null, null); ob_start(); ?>

<?php $_smarty_tpl->_capture_stack[0][] = array("tabsbox", null, null); ob_start(); ?>


<form class="form-horizontal form-edit <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['form_class']->value, ENT_QUOTES, 'UTF-8');?>
 <?php if (!fn_check_view_permissions("companies.update","POST")) {?>cm-hide-inputs<?php }?> <?php if (!$_smarty_tpl->tpl_vars['id']->value) {?>cm-ajax cm-comet cm-disable-check-changes<?php }?>" action="<?php echo htmlspecialchars(fn_url(''), ENT_QUOTES, 'UTF-8');?>
" method="post" id="company_update_form" enctype="multipart/form-data"> 

<input type="hidden" name="fake" value="1" />
<input type="hidden" name="selected_section" id="selected_section" value="<?php echo htmlspecialchars($_REQUEST['selected_section'], ENT_QUOTES, 'UTF-8');?>
" />
<input type="hidden" name="company_id" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
" />


<div id="content_detailed" class="hidden"> 
<fieldset>

<?php if (fn_allowed_for("ULTIMATE")&&!$_smarty_tpl->tpl_vars['id']->value&&!$_smarty_tpl->tpl_vars['runtime']->value['company_id']) {?>
    <?php echo $_smarty_tpl->getSubTemplate ("common/subheader.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('title'=>__("use_existing_store")), 0);?>

    
    <div class="control-group">
        <label class="control-label" for="elm_company_exists_store"><?php echo $_smarty_tpl->__("store");?>
:</label>
        <div class="controls">
            <input type="hidden" name="company_data[clone_from]" id="elm_company_exists_store" value="" onchange="fn_switch_store_settings(this);" />
            <?php echo $_smarty_tpl->getSubTemplate ("common/ajax_select_object.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('data_url'=>"companies.get_companies_list?show_all=Y&default_label=none",'text'=>__("none"),'result_elm'=>"elm_company_exists_store",'id'=>"exists_store_selector"), 0);?>

        </div>
    </div>
    
    <div id="clone_settings_container" class="hidden">       
        <?php  $_smarty_tpl->tpl_vars["object_data"] = new Smarty_Variable; $_smarty_tpl->tpl_vars["object_data"]->_loop = false;
 $_smarty_tpl->tpl_vars["object"] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['clone_schema']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars["object_data"]->key => $_smarty_tpl->tpl_vars["object_data"]->value) {
$_smarty_tpl->tpl_vars["object_data"]->_loop = true;
 $_smarty_tpl->tpl_vars["object"]->value = $_smarty_tpl->tpl_vars["object_data"]->key;
?>
            <div class="control-group">
                <?php $_smarty_tpl->tpl_vars["label"] = new Smarty_variable("clone_".((string)$_smarty_tpl->tpl_vars['object']->value), null, 0);?>
                <label class="control-label" for="elm_company_clone_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['object']->value, ENT_QUOTES, 'UTF-8');?>
"><?php echo $_smarty_tpl->__($_smarty_tpl->tpl_vars['label']->value);?>
<?php if ($_smarty_tpl->tpl_vars['object_data']->value['tooltip']) {?><?php echo $_smarty_tpl->getSubTemplate ("common/tooltip.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('tooltip'=>__($_smarty_tpl->tpl_vars['object_data']->value['tooltip'])), 0);?>
<?php }?>:</label>
                <div class="controls">
                    <label class="checkbox"><input type="checkbox" name="company_data[clone][<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['object']->value, ENT_QUOTES, 'UTF-8');?>
]" id="elm_company_clone_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['object']->value, ENT_QUOTES, 'UTF-8');?>
" <?php if ($_smarty_tpl->tpl_vars['object_data']->value['checked_by_default']) {?>checked="checked"<?php }?> class="cm-dependence-<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['object']->value, ENT_QUOTES, 'UTF-8');?>
" value="Y" <?php if ($_smarty_tpl->tpl_vars['object_data']->value['dependence']) {?>onchange="fn_check_dependence('<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['object_data']->value['dependence'], ENT_QUOTES, 'UTF-8');?>
', this.checked)" onclick="fn_check_dependence('<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['object_data']->value['dependence'], ENT_QUOTES, 'UTF-8');?>
', this.checked)"<?php }?> /><?php if ($_smarty_tpl->tpl_vars['object_data']->value['checked_by_default']) {?>&nbsp;<span class="small-note">(<?php echo $_smarty_tpl->__("recommended");?>
)</span><?php }?></label>
                </div>
            </div>
        <?php } ?>
    </div>
<?php }?>

<?php echo $_smarty_tpl->getSubTemplate ("common/subheader.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('title'=>__("information")), 0);?>


<?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"companies:general_information")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"companies:general_information"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>


<div class="control-group">
    <label for="elm_company_name" class="control-label cm-required"><?php echo $_smarty_tpl->__("vendor_name");?>
:</label>
    <div class="controls">
        <input type="text" name="company_data[company]" id="elm_company_name" size="32" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['company_data']->value['company'], ENT_QUOTES, 'UTF-8');?>
" class="input-large" />
    </div>
</div>

<?php if (fn_allowed_for("ULTIMATE")) {?>
<?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"companies:storefronts")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"companies:storefronts"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

<div class="control-group">
    <label for="elm_company_storefront" class="control-label cm-required"><?php echo $_smarty_tpl->__("storefront_url");?>
<?php echo $_smarty_tpl->getSubTemplate ("common/tooltip.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('tooltip'=>__("ttc_storefront_url")), 0);?>
:</label>
    <div class="controls">
    <?php if ($_smarty_tpl->tpl_vars['runtime']->value['company_id']) {?>
        http://<?php echo htmlspecialchars(smarty_modifier_unpuny($_smarty_tpl->tpl_vars['company_data']->value['storefront']), ENT_QUOTES, 'UTF-8');?>

    <?php } else { ?>
        <input type="text" name="company_data[storefront]" id="elm_company_storefront" size="32" value="<?php echo htmlspecialchars(smarty_modifier_unpuny($_smarty_tpl->tpl_vars['company_data']->value['storefront']), ENT_QUOTES, 'UTF-8');?>
" class="input-large" placeholder="http://" />
    <?php }?>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_company_secure_storefront"><?php echo $_smarty_tpl->__("secure_storefront_url");?>
:</label>
    <div class="controls">
    <?php if ($_smarty_tpl->tpl_vars['runtime']->value['company_id']) {?>
        https://<?php echo htmlspecialchars(smarty_modifier_unpuny($_smarty_tpl->tpl_vars['company_data']->value['secure_storefront']), ENT_QUOTES, 'UTF-8');?>

    <?php } else { ?>
        <input type="text" name="company_data[secure_storefront]" id="elm_company_secure_storefront" size="32" value="<?php echo htmlspecialchars(smarty_modifier_unpuny($_smarty_tpl->tpl_vars['company_data']->value['secure_storefront']), ENT_QUOTES, 'UTF-8');?>
" class="input-large" placeholder="https://" />
    <?php }?>
    </div>
</div>
<?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"companies:storefronts"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>


<?php if ($_smarty_tpl->tpl_vars['id']->value) {?>
<?php echo $_smarty_tpl->getSubTemplate ("common/subheader.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('title'=>__("design")), 0);?>


<div class="control-group">
    <label class="control-label"><?php echo $_smarty_tpl->__("store_theme");?>
:</label>
    <div class="controls">
        <p><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['theme_info']->value['title'], ENT_QUOTES, 'UTF-8');?>
: <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['current_style']->value['name'], ENT_QUOTES, 'UTF-8');?>
</p>
        <a href="<?php echo htmlspecialchars(fn_url("themes.manage?switch_company_id=".((string)$_smarty_tpl->tpl_vars['id']->value)), ENT_QUOTES, 'UTF-8');?>
"><?php echo $_smarty_tpl->__("goto_theme_configuration");?>
</a>
    </div>
</div>
<?php } else { ?>
    
    <input type="hidden" value="responsive" name="company_data[theme_name]">
<?php }?>
<?php }?>

<?php if (fn_allowed_for("MULTIVENDOR")) {?>
    <?php if (!$_smarty_tpl->tpl_vars['runtime']->value['company_id']) {?>
        <?php echo $_smarty_tpl->getSubTemplate ("common/select_status.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('input_name'=>"company_data[status]",'id'=>"company_data",'obj'=>$_smarty_tpl->tpl_vars['company_data']->value), 0);?>

    <?php } else { ?>
        <div class="control-group">
            <label class="control-label"><?php echo $_smarty_tpl->__("status");?>
:</label>
            <div class="controls">
                <label class="radio"><input type="radio" checked="checked" /><?php if ($_smarty_tpl->tpl_vars['company_data']->value['status']=="A") {?><?php echo $_smarty_tpl->__("active");?>
<?php } elseif ($_smarty_tpl->tpl_vars['company_data']->value['status']=="P") {?><?php echo $_smarty_tpl->__("pending");?>
<?php } elseif ($_smarty_tpl->tpl_vars['company_data']->value['status']=="D") {?><?php echo $_smarty_tpl->__("disabled");?>
<?php }?></label>
            </div>
        </div>
    <?php }?>

    <div class="control-group">
        <label class="control-label" for="elm_company_language"><?php echo $_smarty_tpl->__("language");?>
:</label>
        <div class="controls">
        <select name="company_data[lang_code]" id="elm_company_language">
            <?php  $_smarty_tpl->tpl_vars["language"] = new Smarty_Variable; $_smarty_tpl->tpl_vars["language"]->_loop = false;
 $_smarty_tpl->tpl_vars["lang_code"] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['languages']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars["language"]->key => $_smarty_tpl->tpl_vars["language"]->value) {
$_smarty_tpl->tpl_vars["language"]->_loop = true;
 $_smarty_tpl->tpl_vars["lang_code"]->value = $_smarty_tpl->tpl_vars["language"]->key;
?>
                <option value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['lang_code']->value, ENT_QUOTES, 'UTF-8');?>
" <?php if ($_smarty_tpl->tpl_vars['lang_code']->value==$_smarty_tpl->tpl_vars['company_data']->value['lang_code']) {?>selected="selected"<?php }?>><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['language']->value['name'], ENT_QUOTES, 'UTF-8');?>
</option>
            <?php } ?>
        </select>
        </div>
    </div>
<?php }?>


<?php if (!$_smarty_tpl->tpl_vars['id']->value) {?>
    
    <script type="text/javascript">
    function fn_toggle_required_fields()
    {
        var $ = Tygh.$;
        var checked = $('#company_description_vendor_admin').prop('checked');

        $('#company_description_username').prop('disabled', !checked);
        $('#company_description_first_name').prop('disabled', !checked);
        $('#company_description_last_name').prop('disabled', !checked);

        $('.cm-profile-field').each(function(index){
            $('#' + Tygh.$(this).prop('for')).prop('disabled', !checked);
        });
    }

    function fn_switch_store_settings(elm)
    {
        jelm = Tygh.$(elm);
        var close = true;
        if (jelm.val() != 'all' && jelm.val() != '' && jelm.val() != 0) {
            close = false;
        }
        
        Tygh.$('#clone_settings_container').toggleBy(close);
    }

    function fn_check_dependence(object, enabled)
    {
        if (enabled) {
            Tygh.$('.cm-dependence-' + object).prop('checked', 'checked').prop('readonly', true).on('click', function(e) {
                return false
            });
        } else {
            Tygh.$('.cm-dependence-' + object).prop('readonly', false).off('click');
        }
    }
    </script>
    

    <?php if (!fn_allowed_for("ULTIMATE")) {?>
        <div class="control-group">
            <label class="control-label" for="elm_company_vendor_admin"><?php echo $_smarty_tpl->__("create_administrator_account");?>
:</label>
            <div class="controls">
                <label class="checkbox">
                    <input type="checkbox" name="company_data[is_create_vendor_admin]" id="elm_company_vendor_admin" checked="checked" value="Y" onchange="fn_toggle_required_fields();" />
                </label>
            </div>
        </div>
        <?php if ($_smarty_tpl->tpl_vars['settings']->value['General']['use_email_as_login']!='Y') {?>
        <div class="control-group" id="company_description_admin">
            <label for="elm_company_vendor_username" class="control-label cm-required"><?php echo $_smarty_tpl->__("account_name");?>
:</label>
            <div class="controls">
                <input type="text" name="company_data[admin_username]" id="elm_company_vendor_username" size="32" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['company_data']->value['admin_username'], ENT_QUOTES, 'UTF-8');?>
" class="input-large" />
            </div>
        </div>
        <div class="control-group">
            <label for="elm_company_vendor_firstname" class="control-label cm-required"><?php echo $_smarty_tpl->__("first_name");?>
:</label>
            <div class="controls">
                <input type="text" name="company_data[admin_firstname]" id="elm_company_vendor_firstname" size="32" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['company_data']->value['admin_first_name'], ENT_QUOTES, 'UTF-8');?>
" class="input-large" />
            </div>
        </div>
        <div class="control-group">
            <label for="elm_company_vendor_lastname" class="control-label cm-required"><?php echo $_smarty_tpl->__("last_name");?>
:</label>
            <div class="controls">
                <input type="text" name="company_data[admin_lastname]" id="elm_company_vendor_lastname" size="32" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['company_data']->value['admin_last_name'], ENT_QUOTES, 'UTF-8');?>
" class="input-large" />
            </div>
        </div>
    <?php }?>
    <?php }?>
<?php }?>
<?php if (!$_smarty_tpl->tpl_vars['runtime']->value['company_id']&&fn_allowed_for("MULTIVENDOR")) {?>
<div class="control-group">
    <label class="control-label" for="elm_company_vendor_commission"><?php echo $_smarty_tpl->__("vendor_commission");?>
:</label>
    <div class="controls">
    <input type="text" name="company_data[commission]" id="elm_company_vendor_commission" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['company_data']->value['commission'], ENT_QUOTES, 'UTF-8');?>
"  />
    <select name="company_data[commission_type]" class="span1">
        <option value="A" <?php if ($_smarty_tpl->tpl_vars['company_data']->value['commission_type']=="A") {?>selected="selected"<?php }?>><?php echo $_smarty_tpl->tpl_vars['currencies']->value[$_smarty_tpl->tpl_vars['primary_currency']->value]['symbol'];?>
</option>
        <option value="P" <?php if ($_smarty_tpl->tpl_vars['company_data']->value['commission_type']=="P") {?>selected="selected"<?php }?>>%</option>
    </select>
    </div>
</div>
<?php }?>


<?php if (fn_allowed_for("MULTIVENDOR")) {?>
<?php if ($_smarty_tpl->tpl_vars['company_data']->value['status']=="N"&&$_smarty_tpl->tpl_vars['settings']->value['General']['use_email_as_login']!='Y') {?>
<div class="control-group">
    <label class="control-label" for="elm_company_request_account_name"><?php echo $_smarty_tpl->__("request_account_name");?>
:</label>
    <div class="controls">
        <input type="text" name="company_data[request_account_name]" id="elm_company_request_account_name" size="32" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['company_data']->value['request_account_name'], ENT_QUOTES, 'UTF-8');?>
" />
    </div>
</div>
<?php }?>

<?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"companies:contact_information")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"companies:contact_information"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

<?php if (!$_smarty_tpl->tpl_vars['id']->value) {?>
    <?php echo $_smarty_tpl->getSubTemplate ("views/profiles/components/profile_fields.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('section'=>"C",'title'=>__("contact_information")), 0);?>

<?php } else { ?>
    <?php echo $_smarty_tpl->getSubTemplate ("common/subheader.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('title'=>__("contact_information")), 0);?>

<?php }?>

<div class="control-group">
    <label for="elm_company_email" class="control-label cm-required cm-email"><?php echo $_smarty_tpl->__("email");?>
:</label>
    <div class="controls">
        <input type="text" name="company_data[email]" id="elm_company_email" size="32" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['company_data']->value['email'], ENT_QUOTES, 'UTF-8');?>
" class="input-large" />
    </div>
</div>

<div class="control-group">
    <label for="elm_company_phone" class="control-label cm-required"><?php echo $_smarty_tpl->__("phone");?>
:</label>
    <div class="controls">
        <input type="text" class="input-large" name="company_data[phone]" id="elm_company_phone" size="32" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['company_data']->value['phone'], ENT_QUOTES, 'UTF-8');?>
" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_company_url"><?php echo $_smarty_tpl->__("url");?>
:</label>
    <div class="controls">
        <input type="text" class="input-large" name="company_data[url]" id="elm_company_url" size="32" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['company_data']->value['url'], ENT_QUOTES, 'UTF-8');?>
" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_company_fax"><?php echo $_smarty_tpl->__("fax");?>
:</label>
    <div class="controls">
        <input type="text" class="input-large" name="company_data[fax]" id="elm_company_fax" size="32" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['company_data']->value['fax'], ENT_QUOTES, 'UTF-8');?>
"  />
    </div>
</div>
<?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"companies:contact_information"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>


<?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"companies:shipping_address")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"companies:shipping_address"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

<?php if (!$_smarty_tpl->tpl_vars['id']->value) {?>
    <?php echo $_smarty_tpl->getSubTemplate ("views/profiles/components/profile_fields.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('section'=>"B",'title'=>__("shipping_address"),'shipping_flag'=>false), 0);?>

<?php } else { ?>
    <?php echo $_smarty_tpl->getSubTemplate ("common/subheader.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('title'=>__("shipping_address")), 0);?>

<?php }?>

<div class="control-group">
    <label for="elm_company_address" class="control-label cm-required"><?php echo $_smarty_tpl->__("address");?>
:</label>
    <div class="controls">
        <input type="text" class="input-large" name="company_data[address]" id="elm_company_address" size="32" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['company_data']->value['address'], ENT_QUOTES, 'UTF-8');?>
" />
    </div>
</div>

<div class="control-group">
    <label for="elm_company_city" class="control-label cm-required"><?php echo $_smarty_tpl->__("city");?>
:</label>
    <div class="controls">
        <input type="text" class="input-large" name="company_data[city]" id="elm_company_city" size="32" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['company_data']->value['city'], ENT_QUOTES, 'UTF-8');?>
" />
    </div>
</div>

<div class="control-group">
    <label for="elm_company_country" class="control-label cm-required"><?php echo $_smarty_tpl->__("country");?>
:</label>
    <div class="controls">
    <?php $_smarty_tpl->tpl_vars["_country"] = new Smarty_variable((($tmp = @$_smarty_tpl->tpl_vars['company_data']->value['country'])===null||$tmp==='' ? $_smarty_tpl->tpl_vars['settings']->value['General']['default_country'] : $tmp), null, 0);?>
    <select class="cm-country cm-location-shipping" id="elm_company_country" name="company_data[country]">
        <option value="">- <?php echo $_smarty_tpl->__("select_country");?>
 -</option>
        <?php  $_smarty_tpl->tpl_vars["country"] = new Smarty_Variable; $_smarty_tpl->tpl_vars["country"]->_loop = false;
 $_smarty_tpl->tpl_vars["code"] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['countries']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars["country"]->key => $_smarty_tpl->tpl_vars["country"]->value) {
$_smarty_tpl->tpl_vars["country"]->_loop = true;
 $_smarty_tpl->tpl_vars["code"]->value = $_smarty_tpl->tpl_vars["country"]->key;
?>
        <option <?php if ($_smarty_tpl->tpl_vars['_country']->value==$_smarty_tpl->tpl_vars['code']->value) {?>selected="selected"<?php }?> value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['code']->value, ENT_QUOTES, 'UTF-8');?>
"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['country']->value, ENT_QUOTES, 'UTF-8');?>
</option>
        <?php } ?>
    </select>
    </div>
</div>

<div class="control-group">
    <?php $_smarty_tpl->tpl_vars['_country'] = new Smarty_variable((($tmp = @$_smarty_tpl->tpl_vars['company_data']->value['country'])===null||$tmp==='' ? $_smarty_tpl->tpl_vars['settings']->value['General']['default_country'] : $tmp), null, 0);?>
    <?php $_smarty_tpl->tpl_vars['_state'] = new Smarty_variable((($tmp = @$_smarty_tpl->tpl_vars['company_data']->value['state'])===null||$tmp==='' ? $_smarty_tpl->tpl_vars['settings']->value['General']['default_state'] : $tmp), null, 0);?>

    <label for="elm_company_state" class="control-label cm-required"><?php echo $_smarty_tpl->__("state");?>
:</label>
    <div class="controls">
    <select id="elm_company_state" name="company_data[state]" class="cm-state cm-location-shipping <?php if (!$_smarty_tpl->tpl_vars['states']->value[$_smarty_tpl->tpl_vars['_country']->value]) {?>hidden<?php }?>">
        <option value="">- <?php echo $_smarty_tpl->__("select_state");?>
 -</option>
        <?php if ($_smarty_tpl->tpl_vars['states']->value[$_smarty_tpl->tpl_vars['_country']->value]) {?>
            <?php  $_smarty_tpl->tpl_vars['state'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['state']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['states']->value[$_smarty_tpl->tpl_vars['_country']->value]; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['state']->key => $_smarty_tpl->tpl_vars['state']->value) {
$_smarty_tpl->tpl_vars['state']->_loop = true;
?>
                <option <?php if ($_smarty_tpl->tpl_vars['_state']->value==$_smarty_tpl->tpl_vars['state']->value['code']) {?>selected="selected"<?php }?> value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['state']->value['code'], ENT_QUOTES, 'UTF-8');?>
"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['state']->value['state'], ENT_QUOTES, 'UTF-8');?>
</option>
            <?php } ?>
        <?php }?>
    </select>
    <input type="text" id="elm_company_state_d" name="company_data[state]" size="32" maxlength="64" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['_state']->value, ENT_QUOTES, 'UTF-8');?>
" <?php if ($_smarty_tpl->tpl_vars['states']->value[$_smarty_tpl->tpl_vars['_country']->value]) {?>disabled="disabled"<?php }?> class="cm-state cm-location-shipping <?php if ($_smarty_tpl->tpl_vars['states']->value[$_smarty_tpl->tpl_vars['_country']->value]) {?>hidden<?php }?> cm-skip-avail-switch" />
    </div>
</div>

<div class="control-group">
    <label for="elm_company_zipcode" class="control-label cm-required cm-zipcode cm-location-shipping"><?php echo $_smarty_tpl->__("zip_postal_code");?>
:</label>
    <div class="controls">
        <input type="text" name="company_data[zipcode]" id="elm_company_zipcode" size="32" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['company_data']->value['zipcode'], ENT_QUOTES, 'UTF-8');?>
" />
    </div>
</div>
<?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"companies:shipping_address"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

<?php }?>

<?php if (fn_allowed_for("ULTIMATE")) {?>
    <?php ob_start();?><?php echo $_smarty_tpl->__("settings");?>
<?php $_tmp1=ob_get_clean();?><?php ob_start();?><?php echo $_smarty_tpl->__("company");?>
<?php $_tmp2=ob_get_clean();?><?php echo $_smarty_tpl->getSubTemplate ("common/subheader.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('title'=>$_tmp1.": ".$_tmp2), 0);?>

    
    <?php  $_smarty_tpl->tpl_vars["item"] = new Smarty_Variable; $_smarty_tpl->tpl_vars["item"]->_loop = false;
 $_smarty_tpl->tpl_vars["field_id"] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['company_settings']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars["item"]->key => $_smarty_tpl->tpl_vars["item"]->value) {
$_smarty_tpl->tpl_vars["item"]->_loop = true;
 $_smarty_tpl->tpl_vars["field_id"]->value = $_smarty_tpl->tpl_vars["item"]->key;
?>
        <?php echo $_smarty_tpl->getSubTemplate ("common/settings_fields.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('item'=>$_smarty_tpl->tpl_vars['item']->value,'section'=>"Company",'html_id'=>"field_".((string)$_smarty_tpl->tpl_vars['section']->value)."_".((string)$_smarty_tpl->tpl_vars['item']->value['name'])."_".((string)$_smarty_tpl->tpl_vars['item']->value['object_id']),'html_name'=>"update[".((string)$_smarty_tpl->tpl_vars['item']->value['object_id'])."]"), 0);?>

    <?php } ?>
<?php }?>

<?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"companies:general_information"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>


</fieldset>
</div> 





<div id="content_description" class="hidden"> 
<fieldset>
<?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"companies:description")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"companies:description"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

<div class="control-group">
    <label class="control-label" for="elm_company_description"><?php echo $_smarty_tpl->__("description");?>
:</label>
    <div class="controls">
        <textarea id="elm_company_description" name="company_data[company_description]" cols="55" rows="8" class="cm-wysiwyg input-large"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['company_data']->value['company_description'], ENT_QUOTES, 'UTF-8');?>
</textarea>
    </div>
</div>
<?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"companies:description"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

</fieldset>
</div> 



<?php if (fn_allowed_for("MULTIVENDOR")) {?>
    
    <div id="content_logos" class="hidden"> 
    <?php echo $_smarty_tpl->getSubTemplate ("views/companies/components/logos_list.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('logos'=>$_smarty_tpl->tpl_vars['company_data']->value['logos'],'company_id'=>$_smarty_tpl->tpl_vars['id']->value), 0);?>


    </div> 
    

    
    <div id="content_categories" class="hidden"> 
        <?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"companies:categories")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"companies:categories"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

        <?php echo $_smarty_tpl->getSubTemplate ("pickers/categories/picker.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('multiple'=>true,'input_name'=>"company_data[categories]",'item_ids'=>$_smarty_tpl->tpl_vars['company_data']->value['categories'],'data_id'=>"category_ids",'no_item_text'=>__("text_all_categories_included"),'use_keys'=>"N",'but_meta'=>"pull-right"), 0);?>

        <?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"companies:categories"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

    </div> 
    
<?php }?>


<?php if (fn_allowed_for("ULTIMATE")) {?>

<div id="content_regions" class="hidden"> 
    <fieldset>
        <div class="control-group">
            <div class="controls">
            <input type="hidden" name="company_data[redirect_customer]" value="N" checked="checked"/>
            <label class="checkbox"><input type="checkbox" name="company_data[redirect_customer]" id="sw_company_redirect" <?php if ($_smarty_tpl->tpl_vars['company_data']->value['redirect_customer']=="Y") {?>checked="checked"<?php }?> value="Y" class="cm-switch-availability cm-switch-inverse" /><?php echo $_smarty_tpl->__("redirect_customer_from_storefront");?>
</label>
            </div>
        </div>

        <div class="control-group" id="company_redirect">
            <label class="control-label" for="elm_company_entry_page"><?php echo $_smarty_tpl->__("entry_page");?>
</label>
            <div class="controls">
            <select name="company_data[entry_page]" id="elm_company_entry_page" <?php if ($_smarty_tpl->tpl_vars['company_data']->value['redirect_customer']=="Y") {?>disabled="disabled"<?php }?>>
                <option value="none" <?php if ($_smarty_tpl->tpl_vars['company_data']->value['entry_page']=="none") {?>selected="selected"<?php }?>><?php echo $_smarty_tpl->__("none");?>
</option>
                <option value="index" <?php if ($_smarty_tpl->tpl_vars['company_data']->value['entry_page']=="index") {?>selected="selected"<?php }?>><?php echo $_smarty_tpl->__("index");?>
</option>
                <option value="all_pages" <?php if ($_smarty_tpl->tpl_vars['company_data']->value['entry_page']=="all_pages") {?>selected="selected"<?php }?>><?php echo $_smarty_tpl->__("all_pages");?>
</option>
            </select>
            </div>
        </div>
        
        <?php echo $_smarty_tpl->getSubTemplate ("common/double_selectboxes.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('title'=>__("countries"),'first_name'=>"company_data[countries_list]",'first_data'=>$_smarty_tpl->tpl_vars['company_data']->value['countries_list'],'second_name'=>"all_countries",'second_data'=>$_smarty_tpl->tpl_vars['countries_list']->value), 0);?>

    </fieldset>
</div>


<?php }?>

<?php if (fn_allowed_for("MULTIVENDOR")&&!$_smarty_tpl->tpl_vars['runtime']->value['company_id']) {?>

<div id="content_shipping_methods" class="hidden"> 
    <?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"companies:shipping_methods")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"companies:shipping_methods"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

        <?php if ($_smarty_tpl->tpl_vars['shippings']->value) {?>
        <input type="hidden" name="company_data[shippings]" value="" />
        <table width="100%" class="table table-middle">
        <thead>
        <tr>
            <th width="50%"><?php echo $_smarty_tpl->__("shipping_methods");?>
</th>
            <th class="center"><?php echo $_smarty_tpl->__("available_for_vendor");?>
</th>
        </tr>
        </thead>
        <?php  $_smarty_tpl->tpl_vars["shipping"] = new Smarty_Variable; $_smarty_tpl->tpl_vars["shipping"]->_loop = false;
 $_smarty_tpl->tpl_vars["shipping_id"] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['shippings']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars["shipping"]->key => $_smarty_tpl->tpl_vars["shipping"]->value) {
$_smarty_tpl->tpl_vars["shipping"]->_loop = true;
 $_smarty_tpl->tpl_vars["shipping_id"]->value = $_smarty_tpl->tpl_vars["shipping"]->key;
?>
        <tr>
            <td><a href="<?php echo htmlspecialchars(fn_url("shippings.update?shipping_id=".((string)$_smarty_tpl->tpl_vars['shipping_id']->value)), ENT_QUOTES, 'UTF-8');?>
"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['shipping']->value['shipping'], ENT_QUOTES, 'UTF-8');?>
<?php if ($_smarty_tpl->tpl_vars['shipping']->value['status']=="D") {?> (<?php echo mb_strtolower($_smarty_tpl->__("disabled"), 'UTF-8');?>
)<?php }?></a></td>
            <td class="center">
                <input type="checkbox" <?php if (!$_smarty_tpl->tpl_vars['id']->value||smarty_modifier_in_array($_smarty_tpl->tpl_vars['shipping_id']->value,$_smarty_tpl->tpl_vars['company_data']->value['shippings_ids'])) {?> checked="checked"<?php }?> name="company_data[shippings][]" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['shipping_id']->value, ENT_QUOTES, 'UTF-8');?>
">
            </td>
        </tr>
        <?php } ?>
        </table>
        <?php } else { ?>
            <p class="no-items"><?php echo $_smarty_tpl->__("no_data");?>
</p>
        <?php }?>
    <?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"companies:shipping_methods"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

</div> 

<?php }?>

<div id="content_addons" class="hidden">
    <?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"companies:detailed_content")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"companies:detailed_content"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>
<?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"companies:detailed_content"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

</div>

<?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"companies:tabs_content")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"companies:tabs_content"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>
<?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"companies:tabs_content"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>


</form> 

<?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"companies:tabs_extra")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"companies:tabs_extra"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>
<?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"companies:tabs_extra"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>


<?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>
<?php echo $_smarty_tpl->getSubTemplate ("common/tabsbox.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('content'=>Smarty::$_smarty_vars['capture']['tabsbox'],'group_name'=>"companies",'active_tab'=>$_REQUEST['selected_section'],'track'=>true), 0);?>


<?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>

<?php $_smarty_tpl->_capture_stack[0][] = array("sidebar", null, null); ob_start(); ?>
<?php if ($_smarty_tpl->tpl_vars['runtime']->value['company_id']) {?>
<div class="sidebar-row">
    <h6><?php echo $_smarty_tpl->__("menu");?>
</h6>
    <ul class="nav nav-list">
        <li><a href="<?php echo htmlspecialchars(fn_url("products.manage?company_id=".((string)$_smarty_tpl->tpl_vars['id']->value)), ENT_QUOTES, 'UTF-8');?>
"><?php echo $_smarty_tpl->__("view_vendor_products");?>
</a></li>
        <?php if (fn_allowed_for("ULTIMATE")) {?>
            <li><a href="<?php echo htmlspecialchars(fn_url("categories.manage?company_id=".((string)$_smarty_tpl->tpl_vars['id']->value)), ENT_QUOTES, 'UTF-8');?>
"><?php echo $_smarty_tpl->__("view_vendor_categories");?>
</a></li>
        <?php }?>
        <li><a href="<?php echo htmlspecialchars(fn_url("profiles.manage?company_id=".((string)$_smarty_tpl->tpl_vars['id']->value)), ENT_QUOTES, 'UTF-8');?>
"><?php echo $_smarty_tpl->__("view_vendor_users");?>
</a></li>
        <li><a href="<?php echo htmlspecialchars(fn_url("orders.manage?company_id=".((string)$_smarty_tpl->tpl_vars['id']->value)), ENT_QUOTES, 'UTF-8');?>
"><?php echo $_smarty_tpl->__("view_vendor_orders");?>
</a></li>
    </ul>
</div>
<?php }?>
<?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>


<?php $_smarty_tpl->_capture_stack[0][] = array("buttons", null, null); ob_start(); ?>   
    <?php if ($_smarty_tpl->tpl_vars['id']->value) {?>
        <?php echo $_smarty_tpl->getSubTemplate ("buttons/save_cancel.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('but_name'=>"dispatch[companies.update]",'but_target_form'=>"company_update_form",'save'=>$_smarty_tpl->tpl_vars['id']->value), 0);?>

    <?php } else { ?>
        <?php echo $_smarty_tpl->getSubTemplate ("buttons/save_cancel.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('but_name'=>"dispatch[companies.add]",'but_target_form'=>"company_update_form",'but_meta'=>"cm-comet"), 0);?>

    <?php }?>
<?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>


<?php if ($_smarty_tpl->tpl_vars['id']->value) {?>
    <?php ob_start();?><?php echo $_smarty_tpl->__("editing_vendor");?>
<?php $_tmp3=ob_get_clean();?><?php echo $_smarty_tpl->getSubTemplate ("common/mainbox.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('title'=>$_tmp3.": ".((string)$_smarty_tpl->tpl_vars['company_data']->value['company']),'content'=>Smarty::$_smarty_vars['capture']['mainbox'],'select_languages'=>true,'buttons'=>Smarty::$_smarty_vars['capture']['buttons'],'sidebar'=>Smarty::$_smarty_vars['capture']['sidebar']), 0);?>

<?php } else { ?>
    <?php echo $_smarty_tpl->getSubTemplate ("common/mainbox.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('title'=>__("new_vendor"),'content'=>Smarty::$_smarty_vars['capture']['mainbox'],'sidebar'=>Smarty::$_smarty_vars['capture']['sidebar'],'buttons'=>Smarty::$_smarty_vars['capture']['buttons']), 0);?>

<?php }?>
<?php }} ?>
