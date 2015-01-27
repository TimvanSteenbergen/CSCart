DROP TABLE IF EXISTS `?:addons`;
CREATE TABLE `?:addons` (
  `addon` varchar(32) NOT NULL default '',
  `status` char(1) NOT NULL default 'A',
  `version` varchar(16) NOT NULL default '',
  `priority` int(11) unsigned NOT NULL default '0',
  `dependencies` varchar(255) NOT NULL default '',
  `conflicts` varchar(255) NOT NULL default '',
  `separate` tinyint(1) NOT NULL,
  PRIMARY KEY  (`addon`),
  KEY (`priority`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `?:addon_descriptions`;
CREATE TABLE IF NOT EXISTS `?:addon_descriptions` (
  `addon` varchar(32) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `lang_code` varchar(2) NOT NULL DEFAULT '',
  PRIMARY KEY (`addon`,`lang_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS ?:bm_block_statuses;
CREATE TABLE `?:bm_block_statuses` (
   `snapping_id` int(11) NOT NULL,
   `object_ids` text NOT NULL,
   `object_type` varchar(32) NOT NULL,
   UNIQUE KEY `snapping_id` (`snapping_id`,`object_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS ?:bm_blocks;
CREATE TABLE `?:bm_blocks` (
   `block_id` int(11) unsigned NOT NULL auto_increment,
   `type` varchar(64) NOT NULL default '',
   `properties` text NOT NULL,
   `company_id` int(11) unsigned default NULL,
   PRIMARY KEY  (`block_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO ?:bm_blocks (`block_id`, `type`, `properties`, `company_id`) VALUES ('16', 'template', 'a:1:{s:8:\"template\";s:32:\"blocks/static_templates/logo.tpl\";}', NULL);
INSERT INTO ?:bm_blocks (`block_id`, `type`, `properties`, `company_id`) VALUES ('18', 'categories', 'a:3:{s:8:\"template\";s:52:\"blocks/categories/categories_dropdown_horizontal.tpl\";s:30:\"dropdown_second_level_elements\";s:2:\"12\";s:29:\"dropdown_third_level_elements\";s:1:\"6\";}', NULL);
INSERT INTO ?:bm_blocks (`block_id`, `type`, `properties`, `company_id`) VALUES ('19', 'main', '', NULL);
INSERT INTO ?:bm_blocks (`block_id`, `type`, `properties`, `company_id`) VALUES ('20', 'banners', 'a:3:{s:8:\"template\";s:34:\"addons/banners/blocks/carousel.tpl\";s:10:\"navigation\";s:1:\"D\";s:5:\"delay\";s:1:\"7\";}', NULL);
INSERT INTO ?:bm_blocks (`block_id`, `type`, `properties`, `company_id`) VALUES ('24', 'template', 'a:1:{s:8:\"template\";s:37:\"blocks/static_templates/copyright.tpl\";}', NULL);
INSERT INTO ?:bm_blocks (`block_id`, `type`, `properties`, `company_id`) VALUES ('26', 'breadcrumbs', 'a:1:{s:8:\"template\";s:32:\"common_templates/breadcrumbs.tpl\";}', NULL);
INSERT INTO ?:bm_blocks (`block_id`, `type`, `properties`, `company_id`) VALUES ('27', 'product_filters', 'a:1:{s:8:\"template\";s:26:\"blocks/product_filters.tpl\";}', NULL);
INSERT INTO ?:bm_blocks (`block_id`, `type`, `properties`, `company_id`) VALUES ('28', 'my_account', 'a:1:{s:8:\"template\";s:21:\"blocks/my_account.tpl\";}', NULL);
INSERT INTO ?:bm_blocks (`block_id`, `type`, `properties`, `company_id`) VALUES ('29', 'products', 'a:3:{s:8:\"template\";s:39:\"blocks/products/products_text_links.tpl\";s:11:\"item_number\";s:1:\"N\";s:23:\"hide_add_to_cart_button\";s:1:\"Y\";}', NULL);
INSERT INTO ?:bm_blocks (`block_id`, `type`, `properties`, `company_id`) VALUES ('35', 'currencies', 'a:4:{s:8:\"template\";s:21:\"blocks/currencies.tpl\";s:4:\"text\";s:0:\"\";s:6:\"format\";s:6:\"symbol\";s:14:\"dropdown_limit\";s:1:\"3\";}', NULL);
INSERT INTO ?:bm_blocks (`block_id`, `type`, `properties`, `company_id`) VALUES ('37', 'template', 'a:1:{s:8:\"template\";s:34:\"blocks/static_templates/search.tpl\";}', NULL);
INSERT INTO ?:bm_blocks (`block_id`, `type`, `properties`, `company_id`) VALUES ('38', 'cart_content', 'a:5:{s:8:\"template\";s:23:\"blocks/cart_content.tpl\";s:22:\"display_bottom_buttons\";s:1:\"Y\";s:20:\"display_delete_icons\";s:1:\"Y\";s:19:\"products_links_type\";s:5:\"thumb\";s:20:\"generate_block_title\";s:1:\"Y\";}', NULL);
INSERT INTO ?:bm_blocks (`block_id`, `type`, `properties`, `company_id`) VALUES ('40', 'template', 'a:1:{s:8:\"template\";s:41:\"blocks/static_templates/payment_icons.tpl\";}', NULL);
INSERT INTO ?:bm_blocks (`block_id`, `type`, `properties`, `company_id`) VALUES ('41', 'html_block', 'a:1:{s:8:\"template\";s:21:\"blocks/html_block.tpl\";}', NULL);
INSERT INTO ?:bm_blocks (`block_id`, `type`, `properties`, `company_id`) VALUES ('42', 'template', 'a:1:{s:8:\"template\";s:44:\"blocks/static_templates/my_account_links.tpl\";}', NULL);
INSERT INTO ?:bm_blocks (`block_id`, `type`, `properties`, `company_id`) VALUES ('43', 'html_block', 'a:1:{s:8:\"template\";s:21:\"blocks/html_block.tpl\";}', NULL);
INSERT INTO ?:bm_blocks (`block_id`, `type`, `properties`, `company_id`) VALUES ('44', 'html_block', 'a:1:{s:8:\"template\";s:21:\"blocks/html_block.tpl\";}', NULL);
INSERT INTO ?:bm_blocks (`block_id`, `type`, `properties`, `company_id`) VALUES ('47', 'template', 'a:1:{s:8:\"template\";s:60:\"addons/news_and_emails/blocks/static_templates/subscribe.tpl\";}', NULL);
INSERT INTO ?:bm_blocks (`block_id`, `type`, `properties`, `company_id`) VALUES ('48', 'checkout', 'a:1:{s:8:\"template\";s:27:\"blocks/checkout/summary.tpl\";}', NULL);
INSERT INTO ?:bm_blocks (`block_id`, `type`, `properties`, `company_id`) VALUES ('49', 'checkout', 'a:1:{s:8:\"template\";s:36:\"blocks/checkout/products_in_cart.tpl\";}', NULL);
INSERT INTO ?:bm_blocks (`block_id`, `type`, `properties`, `company_id`) VALUES ('51', 'html_block', 'a:1:{s:8:\"template\";s:21:\"blocks/html_block.tpl\";}', NULL);
INSERT INTO ?:bm_blocks (`block_id`, `type`, `properties`, `company_id`) VALUES ('52', 'menu', 'a:2:{s:8:\"template\";s:26:\"blocks/menu/text_links.tpl\";s:18:\"show_items_in_line\";s:1:\"Y\";}', '1');
INSERT INTO ?:bm_blocks (`block_id`, `type`, `properties`, `company_id`) VALUES ('53', 'languages', 'a:4:{s:8:\"template\";s:20:\"blocks/languages.tpl\";s:4:\"text\";s:0:\"\";s:6:\"format\";s:4:\"name\";s:14:\"dropdown_limit\";s:1:\"0\";}', '1');
INSERT INTO ?:bm_blocks (`block_id`, `type`, `properties`, `company_id`) VALUES ('54', 'checkout', 'a:1:{s:8:\"template\";s:30:\"blocks/checkout/order_info.tpl\";}', NULL);
INSERT INTO ?:bm_blocks (`block_id`, `type`, `properties`, `company_id`) VALUES ('55', 'products', 'a:11:{s:8:\"template\";s:37:\"blocks/products/products_scroller.tpl\";s:10:\"show_price\";s:1:\"N\";s:17:\"enable_quick_view\";s:1:\"N\";s:24:\"not_scroll_automatically\";s:1:\"Y\";s:18:\"scroller_direction\";s:4:\"left\";s:5:\"speed\";s:6:\"normal\";s:6:\"easing\";s:5:\"swing\";s:11:\"pause_delay\";s:1:\"3\";s:13:\"item_quantity\";s:1:\"3\";s:15:\"thumbnail_width\";s:3:\"160\";s:23:\"hide_add_to_cart_button\";s:1:\"Y\";}', NULL);
INSERT INTO ?:bm_blocks (`block_id`, `type`, `properties`, `company_id`) VALUES ('57', 'template', 'a:1:{s:8:\"template\";s:40:\"blocks/static_templates/profile_info.tpl\";}', NULL);
INSERT INTO ?:bm_blocks (`block_id`, `type`, `properties`, `company_id`) VALUES ('58', 'template', 'a:1:{s:8:\"template\";s:37:\"blocks/static_templates/auth_info.tpl\";}', NULL);
INSERT INTO ?:bm_blocks (`block_id`, `type`, `properties`, `company_id`) VALUES ('61', 'template', 'a:1:{s:8:\"template\";s:31:\"blocks/static_templates/404.tpl\";}', NULL);
INSERT INTO ?:bm_blocks (`block_id`, `type`, `properties`, `company_id`) VALUES ('59', 'vendors', 'a:2:{s:8:\"template\";s:25:\"blocks/companies_list.tpl\";s:17:\"displayed_vendors\";s:2:\"10\";}', NULL);
INSERT INTO ?:bm_blocks (`block_id`, `type`, `properties`, `company_id`) VALUES ('60', 'html_block', 'a:1:{s:8:\"template\";s:21:\"blocks/html_block.tpl\";}', NULL);

DROP TABLE IF EXISTS ?:bm_blocks_content;
CREATE TABLE `?:bm_blocks_content` (
  `snapping_id` int(11) unsigned NOT NULL,
  `object_id` int(11) unsigned NOT NULL default '0',
  `object_type` varchar(64) NOT NULL default '',
  `block_id` int(11) unsigned NOT NULL,
  `lang_code` varchar(2) NOT NULL default 'EN',
  `content` text NOT NULL,
  PRIMARY KEY  (`block_id`,`snapping_id`,`lang_code`,`object_id`,`object_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO ?:bm_blocks_content (`snapping_id`, `object_id`, `object_type`, `block_id`, `lang_code`, `content`) VALUES ('0', '0', '', '16', 'EN', '');
INSERT INTO ?:bm_blocks_content (`snapping_id`, `object_id`, `object_type`, `block_id`, `lang_code`, `content`) VALUES ('0', '0', '', '18', 'EN', 'a:1:{s:5:\"items\";a:2:{s:7:\"filling\";s:13:\"full_tree_cat\";s:17:\"parent_element_id\";s:1:\"0\";}}');
INSERT INTO ?:bm_blocks_content (`snapping_id`, `object_id`, `object_type`, `block_id`, `lang_code`, `content`) VALUES ('0', '0', '', '19', 'EN', '');
INSERT INTO ?:bm_blocks_content (`snapping_id`, `object_id`, `object_type`, `block_id`, `lang_code`, `content`) VALUES ('0', '0', '', '20', 'EN', 'a:1:{s:5:\"items\";a:4:{s:7:\"filling\";s:6:\"newest\";s:6:\"period\";s:1:\"A\";s:9:\"last_days\";s:2:\"10\";s:5:\"limit\";s:1:\"3\";}}');
INSERT INTO ?:bm_blocks_content (`snapping_id`, `object_id`, `object_type`, `block_id`, `lang_code`, `content`) VALUES ('0', '0', '', '24', 'EN', '');
INSERT INTO ?:bm_blocks_content (`snapping_id`, `object_id`, `object_type`, `block_id`, `lang_code`, `content`) VALUES ('0', '0', '', '26', 'EN', '');
INSERT INTO ?:bm_blocks_content (`snapping_id`, `object_id`, `object_type`, `block_id`, `lang_code`, `content`) VALUES ('0', '0', '', '27', 'EN', 'a:1:{s:5:\"items\";a:1:{s:7:\"filling\";s:7:\"dynamic\";}}');
INSERT INTO ?:bm_blocks_content (`snapping_id`, `object_id`, `object_type`, `block_id`, `lang_code`, `content`) VALUES ('0', '0', '', '28', 'EN', '');
INSERT INTO ?:bm_blocks_content (`snapping_id`, `object_id`, `object_type`, `block_id`, `lang_code`, `content`) VALUES ('0', '0', '', '29', 'EN', 'a:1:{s:5:\"items\";a:2:{s:7:\"filling\";s:15:\"recent_products\";s:5:\"limit\";s:1:\"3\";}}');
INSERT INTO ?:bm_blocks_content (`snapping_id`, `object_id`, `object_type`, `block_id`, `lang_code`, `content`) VALUES ('0', '0', '', '30', 'EN', 'a:1:{s:5:\"items\";a:2:{s:7:\"filling\";s:8:\"manually\";s:8:\"item_ids\";s:19:\"114,112,116,111,110\";}}');
INSERT INTO ?:bm_blocks_content (`snapping_id`, `object_id`, `object_type`, `block_id`, `lang_code`, `content`) VALUES ('0', '0', '', '35', 'EN', '');
INSERT INTO ?:bm_blocks_content (`snapping_id`, `object_id`, `object_type`, `block_id`, `lang_code`, `content`) VALUES ('0', '0', '', '37', 'EN', '');
INSERT INTO ?:bm_blocks_content (`snapping_id`, `object_id`, `object_type`, `block_id`, `lang_code`, `content`) VALUES ('0', '0', '', '38', 'EN', '');
INSERT INTO ?:bm_blocks_content (`snapping_id`, `object_id`, `object_type`, `block_id`, `lang_code`, `content`) VALUES ('0', '0', '', '40', 'EN', '');
INSERT INTO ?:bm_blocks_content (`snapping_id`, `object_id`, `object_type`, `block_id`, `lang_code`, `content`) VALUES ('0', '0', '', '47', 'EN', '');
INSERT INTO ?:bm_blocks_content (`snapping_id`, `object_id`, `object_type`, `block_id`, `lang_code`, `content`) VALUES ('0', '0', '', '48', 'EN', '');
INSERT INTO ?:bm_blocks_content (`snapping_id`, `object_id`, `object_type`, `block_id`, `lang_code`, `content`) VALUES ('0', '0', '', '49', 'EN', '');
INSERT INTO ?:bm_blocks_content (`snapping_id`, `object_id`, `object_type`, `block_id`, `lang_code`, `content`) VALUES ('0', '0', '', '41', 'EN', 'a:1:{s:7:\"content\";s:539:\"<p><span>Demo Store</span></p>
\n<ul>
\n<li><a href=\"index.php?dispatch=pages.view&amp;page_id=2\">About us</a></li>
\n<li><a href=\"index.php?dispatch=pages.view&amp;page_id=30\">Contact Us</a></li>
\n<li><a href=\"index.php?dispatch=gift_certificates.add\">Gift certificates</a></li>
\n<li><a href=\"index.php?dispatch=promotions.list\">Promotions</a></li>
\n<li><a href=\"index.php?dispatch=events.search\">Events</a></li>
\n<li><a href=\"index.php?dispatch=sitemap.view\">Sitemap</a></li>
\n<li><a href=\"index.php?dispatch=news.list\">News</a></li>
\n</ul>\";}');
INSERT INTO ?:bm_blocks_content (`snapping_id`, `object_id`, `object_type`, `block_id`, `lang_code`, `content`) VALUES ('0', '0', '', '44', 'EN', 'a:1:{s:7:\"content\";s:214:\"<p><span>About CS-Cart</span></p>
\n<ul>
\n<li><a href=\"index.php?dispatch=pages.view&amp;page_id=1\">What is CS-Cart?</a></li>
\n<li><a href=\"index.php?dispatch=pages.view&amp;page_id=3\">Privacy policy</a></li>
\n</ul>\";}');
INSERT INTO ?:bm_blocks_content (`snapping_id`, `object_id`, `object_type`, `block_id`, `lang_code`, `content`) VALUES ('0', '0', '', '43', 'EN', 'a:1:{s:7:\"content\";s:348:\"<p><span>Customer Service</span></p>
\n<ul>
\n<li><a href=\"index.php?dispatch=orders.search\">About your order</a></li>
\n<li><a href=\"index.php?dispatch=wishlist.view\">Wishlist</a></li>
\n<li><a href=\"index.php?dispatch=product_features.compare\">Compare list</a></li>
\n<li><a href=\"index.php?dispatch=subscriptions.search\">Subscriptions</a></li>
\n</ul>\";}');
INSERT INTO ?:bm_blocks_content (`snapping_id`, `object_id`, `object_type`, `block_id`, `lang_code`, `content`) VALUES ('0', '0', '', '51', 'EN', 'a:1:{s:7:\"content\";s:210:\"<p><a class=\"social-link facebook\" href=\"http://www.facebook.com/pages/CS-Cart/156687676230\">Find us on Facebook</a> <a class=\"social-link twitter\" href=\"https://twitter.com/cscart\">Follow us on Twitter</a></p>\";}');
INSERT INTO ?:bm_blocks_content (`snapping_id`, `object_id`, `object_type`, `block_id`, `lang_code`, `content`) VALUES ('0', '0', '', '42', 'EN', '');
INSERT INTO ?:bm_blocks_content (`snapping_id`, `object_id`, `object_type`, `block_id`, `lang_code`, `content`) VALUES ('0', '0', '', '52', 'EN', 'a:1:{s:4:\"menu\";s:1:\"1\";}');
INSERT INTO ?:bm_blocks_content (`snapping_id`, `object_id`, `object_type`, `block_id`, `lang_code`, `content`) VALUES ('0', '0', '', '53', 'EN', '');
INSERT INTO ?:bm_blocks_content (`snapping_id`, `object_id`, `object_type`, `block_id`, `lang_code`, `content`) VALUES ('0', '0', '', '54', 'EN', '');
INSERT INTO ?:bm_blocks_content (`snapping_id`, `object_id`, `object_type`, `block_id`, `lang_code`, `content`) VALUES ('0', '0', '', '55', 'EN', 'a:1:{s:5:\"items\";a:4:{s:7:\"filling\";s:6:\"newest\";s:6:\"period\";s:1:\"A\";s:9:\"last_days\";s:2:\"10\";s:5:\"limit\";s:1:\"6\";}}');
INSERT INTO ?:bm_blocks_content (`snapping_id`, `object_id`, `object_type`, `block_id`, `lang_code`, `content`) VALUES ('0', '0', '', '57', 'EN', '');
INSERT INTO ?:bm_blocks_content (`snapping_id`, `object_id`, `object_type`, `block_id`, `lang_code`, `content`) VALUES ('0', '0', '', '58', 'EN', '');
INSERT INTO ?:bm_blocks_content (`snapping_id`, `object_id`, `object_type`, `block_id`, `lang_code`, `content`) VALUES ('0', '0', '', '61', 'EN', '');
INSERT INTO ?:bm_blocks_content (`snapping_id`, `object_id`, `object_type`, `block_id`, `lang_code`, `content`) VALUES ('0', '0', '', '59', 'EN', 'a:1:{s:5:\"items\";a:1:{s:7:\"filling\";s:3:\"all\";}}');
INSERT INTO ?:bm_blocks_content (`snapping_id`, `object_id`, `object_type`, `block_id`, `lang_code`, `content`) VALUES ('0', '0', '', '60', 'EN', 'a:1:{s:7:\"content\";s:210:\"<div class=\"company-info\">
\n<h4>Become a vendor</h4>
\n<ul>
\n<li>Access your personal administrator area</li>
\n<li>Use the common storefront to sell your goods</li>
\n<li>Get your profit share</li>
\n</ul>
\n</div>\";}');

UPDATE `?:bm_blocks_content` SET lang_code = (SELECT value FROM ?:settings WHERE option_name = 'admin_default_language' AND section_id = 'Appearance');

DROP TABLE IF EXISTS ?:bm_blocks_descriptions;
CREATE TABLE `?:bm_blocks_descriptions` (
  `block_id` int(11) unsigned NOT NULL,
  `lang_code` varchar(2) NOT NULL default 'EN',
  `name` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`block_id`,`lang_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO ?:bm_blocks_descriptions (`block_id`, `lang_code`, `name`) VALUES ('18', 'EN', 'Top menu');
INSERT INTO ?:bm_blocks_descriptions (`block_id`, `lang_code`, `name`) VALUES ('16', 'EN', 'Logo');
INSERT INTO ?:bm_blocks_descriptions (`block_id`, `lang_code`, `name`) VALUES ('38', 'EN', 'Cart content');
INSERT INTO ?:bm_blocks_descriptions (`block_id`, `lang_code`, `name`) VALUES ('19', 'EN', 'Main Content');
INSERT INTO ?:bm_blocks_descriptions (`block_id`, `lang_code`, `name`) VALUES ('20', 'EN', 'Main banners');
INSERT INTO ?:bm_blocks_descriptions (`block_id`, `lang_code`, `name`) VALUES ('40', 'EN', 'Payment icons');
INSERT INTO ?:bm_blocks_descriptions (`block_id`, `lang_code`, `name`) VALUES ('24', 'EN', 'Copyright');
INSERT INTO ?:bm_blocks_descriptions (`block_id`, `lang_code`, `name`) VALUES ('26', 'EN', 'Breadcrumbs');
INSERT INTO ?:bm_blocks_descriptions (`block_id`, `lang_code`, `name`) VALUES ('27', 'EN', 'Product filters');
INSERT INTO ?:bm_blocks_descriptions (`block_id`, `lang_code`, `name`) VALUES ('28', 'EN', 'My Account');
INSERT INTO ?:bm_blocks_descriptions (`block_id`, `lang_code`, `name`) VALUES ('29', 'EN', 'Recently Viewed');
INSERT INTO ?:bm_blocks_descriptions (`block_id`, `lang_code`, `name`) VALUES ('37', 'EN', 'Search');
INSERT INTO ?:bm_blocks_descriptions (`block_id`, `lang_code`, `name`) VALUES ('35', 'EN', 'Currencies');
INSERT INTO ?:bm_blocks_descriptions (`block_id`, `lang_code`, `name`) VALUES ('41', 'EN', 'Demo store');
INSERT INTO ?:bm_blocks_descriptions (`block_id`, `lang_code`, `name`) VALUES ('42', 'EN', 'Bottom my account');
INSERT INTO ?:bm_blocks_descriptions (`block_id`, `lang_code`, `name`) VALUES ('43', 'EN', 'Customer service');
INSERT INTO ?:bm_blocks_descriptions (`block_id`, `lang_code`, `name`) VALUES ('44', 'EN', 'About CS-Cart');
INSERT INTO ?:bm_blocks_descriptions (`block_id`, `lang_code`, `name`) VALUES ('47', 'EN', 'Subscribe');
INSERT INTO ?:bm_blocks_descriptions (`block_id`, `lang_code`, `name`) VALUES ('48', 'EN', 'Order summary');
INSERT INTO ?:bm_blocks_descriptions (`block_id`, `lang_code`, `name`) VALUES ('49', 'EN', 'Products in your order');
INSERT INTO ?:bm_blocks_descriptions (`block_id`, `lang_code`, `name`) VALUES ('51', 'EN', 'Social links');
INSERT INTO ?:bm_blocks_descriptions (`block_id`, `lang_code`, `name`) VALUES ('52', 'EN', 'Quick links');
INSERT INTO ?:bm_blocks_descriptions (`block_id`, `lang_code`, `name`) VALUES ('53', 'EN', 'Languages');
INSERT INTO ?:bm_blocks_descriptions (`block_id`, `lang_code`, `name`) VALUES ('54', 'EN', 'Order information');
INSERT INTO ?:bm_blocks_descriptions (`block_id`, `lang_code`, `name`) VALUES ('55', 'EN', 'Hot deals');
INSERT INTO ?:bm_blocks_descriptions (`block_id`, `lang_code`, `name`) VALUES ('57', 'EN', 'Profile information');
INSERT INTO ?:bm_blocks_descriptions (`block_id`, `lang_code`, `name`) VALUES ('58', 'EN', 'Auth information');
INSERT INTO ?:bm_blocks_descriptions (`block_id`, `lang_code`, `name`) VALUES ('61', 'EN', '404');
INSERT INTO ?:bm_blocks_descriptions (`block_id`, `lang_code`, `name`) VALUES ('59', 'EN', 'Vendors');
INSERT INTO ?:bm_blocks_descriptions (`block_id`, `lang_code`, `name`) VALUES ('60', 'EN', 'Vendor account information');

UPDATE `?:bm_blocks_descriptions` SET lang_code = (SELECT value FROM ?:settings WHERE option_name = 'admin_default_language' AND section_id = 'Appearance');

DROP TABLE IF EXISTS ?:bm_containers;
CREATE TABLE `?:bm_containers` (
  `container_id` mediumint(9) unsigned NOT NULL auto_increment,
  `location_id` mediumint(9) unsigned NOT NULL,
  `position` enum('TOP','CENTRAL','BOTTOM') NOT NULL,
  `width` tinyint(4) NOT NULL,
  `user_class` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`container_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO ?:bm_containers (`container_id`, `location_id`, `position`, `width`, `user_class`) VALUES ('1', '1', 'TOP', '16', '');
INSERT INTO ?:bm_containers (`container_id`, `location_id`, `position`, `width`, `user_class`) VALUES ('2', '1', 'CENTRAL', '16', '');
INSERT INTO ?:bm_containers (`container_id`, `location_id`, `position`, `width`, `user_class`) VALUES ('3', '1', 'BOTTOM', '16', '');
INSERT INTO ?:bm_containers (`container_id`, `location_id`, `position`, `width`, `user_class`) VALUES ('4', '2', 'TOP', '16', '');
INSERT INTO ?:bm_containers (`container_id`, `location_id`, `position`, `width`, `user_class`) VALUES ('5', '2', 'CENTRAL', '16', '');
INSERT INTO ?:bm_containers (`container_id`, `location_id`, `position`, `width`, `user_class`) VALUES ('6', '2', 'BOTTOM', '16', '');
INSERT INTO ?:bm_containers (`container_id`, `location_id`, `position`, `width`, `user_class`) VALUES ('7', '3', 'TOP', '16', '');
INSERT INTO ?:bm_containers (`container_id`, `location_id`, `position`, `width`, `user_class`) VALUES ('8', '3', 'CENTRAL', '16', '');
INSERT INTO ?:bm_containers (`container_id`, `location_id`, `position`, `width`, `user_class`) VALUES ('9', '3', 'BOTTOM', '16', '');
INSERT INTO ?:bm_containers (`container_id`, `location_id`, `position`, `width`, `user_class`) VALUES ('10', '4', 'TOP', '16', '');
INSERT INTO ?:bm_containers (`container_id`, `location_id`, `position`, `width`, `user_class`) VALUES ('11', '4', 'CENTRAL', '16', '');
INSERT INTO ?:bm_containers (`container_id`, `location_id`, `position`, `width`, `user_class`) VALUES ('12', '4', 'BOTTOM', '16', '');
INSERT INTO ?:bm_containers (`container_id`, `location_id`, `position`, `width`, `user_class`) VALUES ('16', '6', 'TOP', '16', '');
INSERT INTO ?:bm_containers (`container_id`, `location_id`, `position`, `width`, `user_class`) VALUES ('17', '6', 'CENTRAL', '16', '');
INSERT INTO ?:bm_containers (`container_id`, `location_id`, `position`, `width`, `user_class`) VALUES ('18', '6', 'BOTTOM', '16', '');
INSERT INTO ?:bm_containers (`container_id`, `location_id`, `position`, `width`, `user_class`) VALUES ('19', '7', 'TOP', '16', '');
INSERT INTO ?:bm_containers (`container_id`, `location_id`, `position`, `width`, `user_class`) VALUES ('20', '7', 'CENTRAL', '16', '');
INSERT INTO ?:bm_containers (`container_id`, `location_id`, `position`, `width`, `user_class`) VALUES ('21', '7', 'BOTTOM', '16', '');
INSERT INTO ?:bm_containers (`container_id`, `location_id`, `position`, `width`, `user_class`) VALUES ('22', '8', 'TOP', '16', '');
INSERT INTO ?:bm_containers (`container_id`, `location_id`, `position`, `width`, `user_class`) VALUES ('23', '8', 'CENTRAL', '16', '');
INSERT INTO ?:bm_containers (`container_id`, `location_id`, `position`, `width`, `user_class`) VALUES ('24', '8', 'BOTTOM', '16', '');
INSERT INTO ?:bm_containers (`container_id`, `location_id`, `position`, `width`, `user_class`) VALUES ('25', '9', 'TOP', '16', '');
INSERT INTO ?:bm_containers (`container_id`, `location_id`, `position`, `width`, `user_class`) VALUES ('26', '9', 'CENTRAL', '16', '');
INSERT INTO ?:bm_containers (`container_id`, `location_id`, `position`, `width`, `user_class`) VALUES ('27', '9', 'BOTTOM', '16', '');
INSERT INTO ?:bm_containers (`container_id`, `location_id`, `position`, `width`, `user_class`) VALUES ('28', '10', 'TOP', '16', '');
INSERT INTO ?:bm_containers (`container_id`, `location_id`, `position`, `width`, `user_class`) VALUES ('29', '10', 'CENTRAL', '16', '');
INSERT INTO ?:bm_containers (`container_id`, `location_id`, `position`, `width`, `user_class`) VALUES ('30', '10', 'BOTTOM', '16', '');
INSERT INTO ?:bm_containers (`container_id`, `location_id`, `position`, `width`, `user_class`) VALUES ('31', '11', 'TOP', '16', '');
INSERT INTO ?:bm_containers (`container_id`, `location_id`, `position`, `width`, `user_class`) VALUES ('32', '11', 'CENTRAL', '16', '');
INSERT INTO ?:bm_containers (`container_id`, `location_id`, `position`, `width`, `user_class`) VALUES ('33', '11', 'BOTTOM', '16', '');
INSERT INTO ?:bm_containers (`container_id`, `location_id`, `position`, `width`, `user_class`) VALUES ('37', '13', 'TOP', '16', '');
INSERT INTO ?:bm_containers (`container_id`, `location_id`, `position`, `width`, `user_class`) VALUES ('38', '13', 'CENTRAL', '16', '');
INSERT INTO ?:bm_containers (`container_id`, `location_id`, `position`, `width`, `user_class`) VALUES ('39', '13', 'BOTTOM', '16', '');
INSERT INTO ?:bm_containers (`container_id`, `location_id`, `position`, `width`, `user_class`) VALUES ('34', '12', 'TOP', '16', '');
INSERT INTO ?:bm_containers (`container_id`, `location_id`, `position`, `width`, `user_class`) VALUES ('35', '12', 'CENTRAL', '16', '');
INSERT INTO ?:bm_containers (`container_id`, `location_id`, `position`, `width`, `user_class`) VALUES ('36', '12', 'BOTTOM', '16', '');

DROP TABLE IF EXISTS ?:bm_grids;
CREATE TABLE `?:bm_grids` (
  `grid_id` int(11) unsigned NOT NULL auto_increment,
  `container_id` mediumint(9) unsigned NOT NULL,
  `parent_id` int(11) unsigned NOT NULL default '0',
  `order` mediumint(9) unsigned NOT NULL default '0',
  `width` tinyint(4) unsigned NOT NULL default '1',
  `suffix` tinyint(4) unsigned NOT NULL default '0',
  `prefix` tinyint(4) unsigned NOT NULL default '0',
  `user_class` varchar(128) NOT NULL default '',
  `omega` tinyint(1) unsigned NOT NULL default '0',
  `alpha` tinyint(1) unsigned NOT NULL default '0',
  `wrapper` varchar(128) NOT NULL default '',
  `content_align` enum('LEFT','RIGHT','FULL_WIDTH') NOT NULL default 'FULL_WIDTH',
  `html_element` varchar(8) NOT NULL default 'div',
  `clear` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`grid_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('43', '1', '0', '0', '11', '0', '0', '', '0', '0', '', 'FULL_WIDTH', 'div', '1');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('2', '2', '0', '0', '16', '0', '0', '', '0', '0', '', 'FULL_WIDTH', 'div', '1');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('4', '4', '0', '0', '16', '0', '0', '', '0', '0', '', 'FULL_WIDTH', 'div', '0');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('5', '5', '0', '0', '16', '0', '0', '', '0', '0', '', 'FULL_WIDTH', 'div', '1');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('6', '6', '0', '0', '16', '0', '0', '', '0', '0', '', 'FULL_WIDTH', 'div', '0');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('7', '7', '0', '0', '16', '0', '0', '', '0', '0', '', 'FULL_WIDTH', 'div', '0');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('8', '8', '0', '0', '16', '0', '0', '', '0', '0', '', 'FULL_WIDTH', 'div', '1');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('9', '9', '0', '0', '16', '0', '0', '', '0', '0', '', 'FULL_WIDTH', 'div', '0');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('10', '10', '0', '0', '16', '0', '0', '', '0', '0', '', 'FULL_WIDTH', 'div', '0');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('11', '11', '0', '0', '16', '0', '0', '', '0', '0', '', 'FULL_WIDTH', 'div', '1');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('12', '12', '0', '0', '16', '0', '0', '', '0', '0', '', 'FULL_WIDTH', 'div', '0');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('53', '3', '0', '0', '16', '0', '0', 'footer-menu', '0', '0', '', 'FULL_WIDTH', 'div', '1');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('16', '16', '0', '0', '16', '0', '0', '', '0', '0', '', 'FULL_WIDTH', 'div', '0');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('17', '17', '0', '0', '16', '0', '0', '', '0', '0', '', 'FULL_WIDTH', 'div', '1');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('18', '18', '0', '0', '16', '0', '0', '', '0', '0', '', 'FULL_WIDTH', 'div', '0');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('19', '19', '0', '0', '16', '0', '0', '', '0', '0', '', 'FULL_WIDTH', 'div', '0');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('20', '20', '0', '0', '11', '0', '0', '', '0', '0', '', 'FULL_WIDTH', 'div', '0');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('21', '21', '0', '0', '16', '0', '0', '', '0', '0', '', 'FULL_WIDTH', 'div', '0');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('22', '22', '0', '0', '16', '0', '0', '', '0', '0', '', 'FULL_WIDTH', 'div', '0');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('23', '23', '0', '0', '16', '0', '0', '', '0', '0', '', 'FULL_WIDTH', 'div', '0');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('24', '24', '0', '0', '16', '0', '0', '', '0', '0', '', 'FULL_WIDTH', 'div', '0');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('44', '1', '0', '0', '16', '0', '0', '', '0', '0', '', 'FULL_WIDTH', 'div', '1');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('42', '1', '0', '0', '5', '0', '0', '', '0', '0', '', 'FULL_WIDTH', 'div', '0');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('31', '26', '0', '0', '16', '0', '0', '', '0', '0', '', 'FULL_WIDTH', 'div', '1');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('32', '8', '0', '0', '4', '0', '0', '', '0', '0', '', 'FULL_WIDTH', 'div', '0');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('33', '8', '0', '0', '12', '0', '0', '', '0', '0', '', 'FULL_WIDTH', 'div', '0');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('34', '5', '0', '0', '16', '0', '0', '', '0', '0', '', 'FULL_WIDTH', 'div', '0');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('36', '11', '0', '0', '4', '0', '0', '', '0', '0', '', 'FULL_WIDTH', 'div', '0');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('37', '11', '0', '0', '12', '0', '0', '', '0', '0', '', 'FULL_WIDTH', 'div', '0');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('39', '17', '0', '0', '16', '0', '0', '', '0', '0', '', 'FULL_WIDTH', 'div', '0');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('41', '2', '0', '0', '16', '0', '0', '', '0', '0', '', 'FULL_WIDTH', 'div', '1');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('45', '1', '43', '0', '11', '0', '0', 'top-links-grid', '1', '1', '', 'RIGHT', 'div', '0');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('47', '1', '43', '0', '6', '0', '0', 'search-block-grid', '0', '1', '', 'FULL_WIDTH', 'div', '0');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('52', '1', '43', '0', '5', '0', '0', 'cart-content-grid', '1', '0', '', 'RIGHT', 'div', '1');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('55', '3', '0', '0', '10', '0', '0', '', '0', '0', '', 'LEFT', 'div', '0');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('56', '3', '0', '0', '6', '0', '0', '', '0', '0', '', 'RIGHT', 'div', '1');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('57', '3', '53', '0', '3', '0', '0', '', '0', '1', '', 'LEFT', 'div', '0');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('62', '3', '53', '0', '3', '0', '0', '', '0', '0', '', 'LEFT', 'div', '0');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('63', '3', '53', '0', '3', '0', '0', '', '0', '0', '', 'LEFT', 'div', '0');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('64', '3', '53', '0', '3', '0', '0', '', '0', '0', '', 'LEFT', 'div', '0');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('65', '3', '53', '0', '4', '0', '0', '', '1', '0', '', 'LEFT', 'div', '1');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('68', '20', '0', '0', '5', '0', '0', '', '0', '0', '', 'FULL_WIDTH', 'div', '1');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('71', '29', '0', '0', '16', '0', '0', '', '0', '0', '', 'FULL_WIDTH', 'div', '1');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('72', '29', '0', '0', '8', '0', '0', '', '0', '0', '', 'FULL_WIDTH', 'div', '0');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('74', '29', '0', '0', '8', '0', '0', '', '0', '0', '', 'FULL_WIDTH', 'div', '1');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('75', '32', '0', '0', '16', '0', '0', '', '0', '0', '', 'FULL_WIDTH', 'div', '1');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('76', '32', '0', '0', '8', '0', '0', '', '0', '0', '', 'FULL_WIDTH', 'div', '0');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('77', '32', '0', '0', '8', '0', '0', '', '0', '0', '', 'FULL_WIDTH', 'div', '0');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('86', '38', '0', '0', '16', '0', '0', '', '0', '0', '', 'FULL_WIDTH', 'div', '0');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('78', '26', '31', '0', '16', '0', '0', '', '1', '1', '', 'FULL_WIDTH', 'div', '0');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('79', '26', '31', '0', '16', '0', '0', '', '1', '1', '', 'FULL_WIDTH', 'div', '0');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('80', '26', '31', '0', '16', '0', '0', '', '1', '1', '', 'FULL_WIDTH', 'div', '1');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('81', '26', '80', '0', '4', '0', '0', '', '0', '1', '', 'FULL_WIDTH', 'div', '0');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('82', '26', '80', '0', '12', '0', '0', '', '1', '0', '', 'FULL_WIDTH', 'div', '0');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('83', '35', '0', '0', '16', '0', '0', '', '0', '0', '', 'FULL_WIDTH', 'div', '1');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('84', '35', '0', '0', '8', '0', '0', '', '0', '0', '', 'FULL_WIDTH', 'div', '0');
INSERT INTO ?:bm_grids (`grid_id`, `container_id`, `parent_id`, `order`, `width`, `suffix`, `prefix`, `user_class`, `omega`, `alpha`, `wrapper`, `content_align`, `html_element`, `clear`) VALUES ('85', '35', '0', '0', '8', '0', '0', '', '0', '0', '', 'FULL_WIDTH', 'div', '0');

DROP TABLE IF EXISTS ?:bm_grids_descriptions;
CREATE TABLE `?:bm_grids_descriptions` (
  `grid_id` int(11) unsigned NOT NULL,
  `lang_code` varchar(2) NOT NULL default 'EN',
  `name` varchar(64) NOT NULL,
  PRIMARY KEY  (`grid_id`,`lang_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS ?:bm_locations;
CREATE TABLE `?:bm_locations` (
  `location_id` mediumint(8) unsigned NOT NULL auto_increment,
  `dispatch` varchar(64) NOT NULL,
  `is_default` tinyint(1) NOT NULL,
  `company_id` int(11) unsigned default NULL,
  `object_ids` text NOT NULL,
  PRIMARY KEY  (`location_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO ?:bm_locations (`location_id`, `dispatch`, `is_default`, `company_id`, `object_ids`) VALUES ('1', 'default', '1', NULL, '');
INSERT INTO ?:bm_locations (`location_id`, `dispatch`, `is_default`, `company_id`, `object_ids`) VALUES ('2', 'products.view', '0', NULL, '');
INSERT INTO ?:bm_locations (`location_id`, `dispatch`, `is_default`, `company_id`, `object_ids`) VALUES ('3', 'categories.view', '0', NULL, '');
INSERT INTO ?:bm_locations (`location_id`, `dispatch`, `is_default`, `company_id`, `object_ids`) VALUES ('4', 'pages.view', '0', NULL, '');
INSERT INTO ?:bm_locations (`location_id`, `dispatch`, `is_default`, `company_id`, `object_ids`) VALUES ('6', 'checkout.cart', '0', NULL, '');
INSERT INTO ?:bm_locations (`location_id`, `dispatch`, `is_default`, `company_id`, `object_ids`) VALUES ('7', 'checkout', '0', NULL, '');
INSERT INTO ?:bm_locations (`location_id`, `dispatch`, `is_default`, `company_id`, `object_ids`) VALUES ('8', 'checkout.complete', '0', NULL, '');
INSERT INTO ?:bm_locations (`location_id`, `dispatch`, `is_default`, `company_id`, `object_ids`) VALUES ('9', 'index.index', '0', NULL, '');
INSERT INTO ?:bm_locations (`location_id`, `dispatch`, `is_default`, `company_id`, `object_ids`) VALUES ('10', 'profiles', '0', NULL, '');
INSERT INTO ?:bm_locations (`location_id`, `dispatch`, `is_default`, `company_id`, `object_ids`) VALUES ('11', 'auth', '0', NULL, '');
INSERT INTO ?:bm_locations (`location_id`, `dispatch`, `is_default`, `company_id`, `object_ids`) VALUES ('13', 'no_page', '0', NULL, '');
INSERT INTO ?:bm_locations (`location_id`, `dispatch`, `is_default`, `company_id`, `object_ids`) VALUES ('12', 'companies.apply_for_vendor', '0', NULL, '');

DROP TABLE IF EXISTS ?:bm_locations_descriptions;
CREATE TABLE `?:bm_locations_descriptions` (
  `location_id` int(10) unsigned NOT NULL auto_increment,
  `lang_code` varchar(2) NOT NULL,
  `name` varchar(64) NOT NULL,
  `title` text NOT NULL,
  `meta_description` text NOT NULL,
  `meta_keywords` text NOT NULL,
  PRIMARY KEY  (`location_id`,`lang_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO ?:bm_locations_descriptions (`location_id`, `lang_code`, `name`, `title`, `meta_description`, `meta_keywords`) VALUES ('1', 'EN', 'Default', '', '', '');
INSERT INTO ?:bm_locations_descriptions (`location_id`, `lang_code`, `name`, `title`, `meta_description`, `meta_keywords`) VALUES ('2', 'EN', 'Products', '', '', '');
INSERT INTO ?:bm_locations_descriptions (`location_id`, `lang_code`, `name`, `title`, `meta_description`, `meta_keywords`) VALUES ('3', 'EN', 'Categories', '', '', '');
INSERT INTO ?:bm_locations_descriptions (`location_id`, `lang_code`, `name`, `title`, `meta_description`, `meta_keywords`) VALUES ('4', 'EN', 'Pages', '', '', '');
INSERT INTO ?:bm_locations_descriptions (`location_id`, `lang_code`, `name`, `title`, `meta_description`, `meta_keywords`) VALUES ('6', 'EN', 'Cart', '', '', '');
INSERT INTO ?:bm_locations_descriptions (`location_id`, `lang_code`, `name`, `title`, `meta_description`, `meta_keywords`) VALUES ('7', 'EN', 'Checkout', '', '', '');
INSERT INTO ?:bm_locations_descriptions (`location_id`, `lang_code`, `name`, `title`, `meta_description`, `meta_keywords`) VALUES ('8', 'EN', 'Order landing page', '', '', '');
INSERT INTO ?:bm_locations_descriptions (`location_id`, `lang_code`, `name`, `title`, `meta_description`, `meta_keywords`) VALUES ('9', 'EN', 'Homepage', '', '', '');
INSERT INTO ?:bm_locations_descriptions (`location_id`, `lang_code`, `name`, `title`, `meta_description`, `meta_keywords`) VALUES ('10', 'EN', 'Profiles', '', '', '');
INSERT INTO ?:bm_locations_descriptions (`location_id`, `lang_code`, `name`, `title`, `meta_description`, `meta_keywords`) VALUES ('11', 'EN', 'Auth', '', '', '');
INSERT INTO ?:bm_locations_descriptions (`location_id`, `lang_code`, `name`, `title`, `meta_description`, `meta_keywords`) VALUES ('13', 'EN', '404', 'Page not found', '', '');
INSERT INTO ?:bm_locations_descriptions (`location_id`, `lang_code`, `name`, `title`, `meta_description`, `meta_keywords`) VALUES ('12', 'EN', 'Vendor account', '', '', '');

UPDATE `?:bm_locations_descriptions` SET lang_code = (SELECT value FROM ?:settings WHERE option_name = 'admin_default_language' AND section_id = 'Appearance');

DROP TABLE IF EXISTS ?:bm_snapping;
CREATE TABLE `?:bm_snapping` (
  `snapping_id` int(11) unsigned NOT NULL auto_increment,
  `block_id` int(11) unsigned NOT NULL,
  `grid_id` int(11) unsigned NOT NULL,
  `wrapper` varchar(128) NOT NULL default '',
  `user_class` varchar(128) NOT NULL default '',
  `order` mediumint(8) unsigned NOT NULL default '0',
  `status` varchar(1) NOT NULL default 'A',
  PRIMARY KEY  (`snapping_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('46', '16', '42', '', '', '0', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('45', '18', '44', '', '', '0', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('88', '59', '81', 'blocks/wrappers/mainbox_general.tpl', 'homepage-vendors', '0', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('60', '42', '57', '', '', '3', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('61', '43', '63', '', 'footer-no-wysiwyg', '0', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('20', '26', '5', '', '', '0', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('21', '26', '8', '', '', '0', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('23', '19', '33', 'blocks/wrappers/mainbox_general.tpl', '', '0', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('24', '27', '32', 'blocks/wrappers/sidebox_general.tpl', '', '0', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('25', '28', '32', 'blocks/wrappers/sidebox_general.tpl', '', '0', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('26', '29', '32', 'blocks/wrappers/sidebox_general.tpl', '', '0', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('28', '19', '34', '', '', '0', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('29', '26', '11', '', '', '0', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('30', '28', '36', 'blocks/wrappers/sidebox_general.tpl', '', '0', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('31', '29', '36', 'blocks/wrappers/sidebox_general.tpl', '', '1', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('32', '19', '37', 'blocks/wrappers/mainbox_general.tpl', '', '0', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('33', '26', '17', '', '', '0', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('36', '19', '39', 'blocks/wrappers/mainbox_general.tpl', '', '0', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('38', '19', '20', 'blocks/wrappers/mainbox_general.tpl', '', '1', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('39', '26', '23', '', '', '0', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('40', '19', '23', 'blocks/wrappers/mainbox_general.tpl', '', '1', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('41', '26', '2', '', '', '0', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('44', '19', '41', 'blocks/wrappers/mainbox_general.tpl', '', '0', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('56', '38', '52', '', '', '0', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('54', '37', '47', '', '', '1', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('48', '28', '45', 'blocks/wrappers/onclick_dropdown.tpl', '', '0', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('74', '52', '45', '', 'quick-links-top', '2', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('52', '35', '45', '', '', '1', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('59', '41', '62', '', 'footer-no-wysiwyg', '1', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('62', '44', '64', '', 'footer-no-wysiwyg', '0', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('71', '47', '65', '', '', '0', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('65', '24', '55', '', '', '0', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('66', '40', '56', '', '', '0', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('68', '48', '68', 'blocks/wrappers/sidebox_general.tpl', 'order-summary', '0', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('69', '49', '68', 'blocks/wrappers/sidebox_general.tpl', 'order-products', '0', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('72', '51', '65', '', '', '0', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('76', '53', '45', '', '', '0', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('77', '54', '68', 'blocks/wrappers/sidebox_general.tpl', 'order-information', '0', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('86', '19', '78', '', '', '0', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('87', '20', '79', '', 'homepage-banners', '0', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('79', '19', '72', 'blocks/wrappers/mainbox_general.tpl', '', '2', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('81', '26', '71', '', '', '0', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('82', '57', '74', '', '', '0', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('83', '26', '75', '', '', '0', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('84', '19', '76', 'blocks/wrappers/mainbox_general.tpl', '', '0', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('85', '58', '77', '', '', '0', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('93', '61', '86', '', '', '0', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('89', '55', '82', 'blocks/wrappers/mainbox_general.tpl', 'homepage-hotdeals', '0', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('90', '26', '83', '', '', '0', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('91', '19', '84', '', '', '0', 'A');
INSERT INTO ?:bm_snapping (`snapping_id`, `block_id`, `grid_id`, `wrapper`, `user_class`, `order`, `status`) VALUES ('92', '60', '85', '', '', '0', 'A');

DROP TABLE IF EXISTS ?:product_tabs;
CREATE TABLE `?:product_tabs` (
   `tab_id` mediumint(8) unsigned NOT NULL auto_increment,
   `tab_type` char(1) NOT NULL default 'B',
   `block_id` mediumint(8) unsigned NOT NULL default '0',
   `template` varchar(255) NOT NULL default '',
   `addon` varchar(32) NOT NULL default '',
   `position` int(11) NOT NULL default '0',
   `status` char(1) NOT NULL default 'A',
   `is_primary` char(1) NOT NULL default 'N',
   `product_ids` text NOT NULL,
   `company_id` int(11) unsigned default NULL,
   `show_in_popup` char(1) NOT NULL default 'N',
   PRIMARY KEY  (`tab_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO ?:product_tabs (`tab_id`, `tab_type`, `block_id`, `template`, `addon`, `position`, `status`, `is_primary`, `product_ids`, `company_id`, `show_in_popup`) VALUES ('1', 'T', '0', 'blocks/product_tabs/description.tpl', '', '1', 'A', 'Y', '', NULL, 'N');
INSERT INTO ?:product_tabs (`tab_id`, `tab_type`, `block_id`, `template`, `addon`, `position`, `status`, `is_primary`, `product_ids`, `company_id`, `show_in_popup`) VALUES ('2', 'T', '0', 'blocks/product_tabs/features.tpl', '', '2', 'A', 'Y', '', NULL, 'N');
INSERT INTO ?:product_tabs (`tab_id`, `tab_type`, `block_id`, `template`, `addon`, `position`, `status`, `is_primary`, `product_ids`, `company_id`, `show_in_popup`) VALUES ('3', 'T', '0', 'blocks/product_tabs/files.tpl', '', '3', 'A', 'Y', '', NULL, 'N');

DROP TABLE IF EXISTS ?:product_tabs_descriptions;
CREATE TABLE `?:product_tabs_descriptions` (
   `tab_id` mediumint(8) unsigned NOT NULL default '0',
   `lang_code` char(2) NOT NULL default '',
   `name` varchar(255) NOT NULL default '',
   PRIMARY KEY  (`tab_id`,`lang_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO ?:product_tabs_descriptions (`tab_id`, `lang_code`, `name`) VALUES ('1', 'EN', 'Description');
INSERT INTO ?:product_tabs_descriptions (`tab_id`, `lang_code`, `name`) VALUES ('2', 'EN', 'Features');
INSERT INTO ?:product_tabs_descriptions (`tab_id`, `lang_code`, `name`) VALUES ('3', 'EN', 'Files');

UPDATE `?:product_tabs_descriptions` SET lang_code = (SELECT value FROM ?:settings WHERE option_name = 'admin_default_language' AND section_id = 'Appearance');

ALTER TABLE `?:categories`
  ADD COLUMN `company_id` int(11) unsigned NULL DEFAULT '0';

DROP INDEX `catalog_mode` ON `?:companies`;

ALTER TABLE `?:companies`
  DROP COLUMN `catalog_mode`
  , ADD COLUMN `storefront` varchar(255) NULL DEFAULT ''
  , ADD COLUMN `secure_storefront` varchar(255) NULL DEFAULT ''
  , ADD COLUMN `entry_page` varchar(50) NULL DEFAULT 'none'
  , ADD COLUMN `redirect_customer` char(1) NULL DEFAULT 'Y'
  , ADD COLUMN `countries_list` text NULL;

ALTER TABLE `?:images_links`
  ADD COLUMN `position` int(11) NULL DEFAULT '0';

CREATE TABLE `?:menus` (
  `menu_id` mediumint(8) unsigned NOT NULL auto_increment,
  `status` char(1) NOT NULL DEFAULT 'A',
  `company_id` int(11) unsigned NULL,
  PRIMARY KEY (`menu_id`)
) ENGINE=MyISAM;

CREATE TABLE `?:menus_descriptions` (
  `menu_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `lang_code` char(2) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`menu_id`,`lang_code`)
) ENGINE=MyISAM;

ALTER TABLE `?:orders`
  ADD COLUMN `s_address_type` varchar(32) NULL DEFAULT '';

ALTER TABLE `?:pages`
  ADD COLUMN `show_in_popup` char(1) NULL DEFAULT 'N';

ALTER TABLE `?:payment_descriptions`
  ADD COLUMN `surcharge_title` varchar(255) NULL DEFAULT '';

ALTER TABLE `?:payments`
  ADD COLUMN `tax_ids` varchar(255) NULL DEFAULT ''
  , ADD COLUMN `payment_category` varchar(20) NULL DEFAULT 'tab1';

ALTER TABLE `?:product_features`
  ADD COLUMN `company_id` int(11) unsigned NULL;

CREATE INDEX `company_id` ON `?:product_features`(`company_id`);

ALTER TABLE `?:product_filters`
  ADD COLUMN `company_id` int(11) unsigned NULL DEFAULT '0'
  , ADD COLUMN `round_to` smallint(5) unsigned NULL DEFAULT 1
  , ADD COLUMN `display` char(1) NULL DEFAULT 'Y'
  , ADD COLUMN `display_count` smallint(5) unsigned NULL DEFAULT 10
  , ADD COLUMN `display_more_count` smallint(5) unsigned NOT NULL DEFAULT '20';
  
CREATE INDEX `company_id` ON `?:product_filters`(`company_id`);

ALTER TABLE `?:product_prices`
  ADD COLUMN `percentage_discount` int(2) unsigned NULL DEFAULT '0';

CREATE TABLE `?:product_tabs` (
  `tab_id` mediumint(8) unsigned NOT NULL auto_increment,
  `tab_type` char(1) NOT NULL DEFAULT 'B',
  `block_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `template` varchar(255) NOT NULL DEFAULT '',
  `addon` varchar(32) NOT NULL DEFAULT '',
  `position` int(11) NOT NULL DEFAULT '0',
  `status` char(1) NOT NULL DEFAULT 'A',
  `is_primary` char(1) NOT NULL DEFAULT 'N',
  `product_ids` text NOT NULL,
  `company_id` int(11) unsigned NULL,
  `show_in_popup` char(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (`tab_id`)
) ENGINE=MyISAM;

CREATE TABLE `?:product_tabs_descriptions` (
  `tab_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `lang_code` char(2) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`tab_id`,`lang_code`)
) ENGINE=MyISAM;

ALTER TABLE `?:products`
  DROP COLUMN `buy_now_url`
  , ADD COLUMN `updated_timestamp` int(11) unsigned NULL DEFAULT '0';

ALTER TABLE `?:profile_fields`
  ADD COLUMN `class` varchar(100) NULL DEFAULT '';

ALTER TABLE `?:shipping_services`
  DROP COLUMN `intershipper_code`;

ALTER TABLE `?:user_profiles`
  DROP COLUMN `credit_cards`;

ALTER TABLE `?:users`
  DROP COLUMN `card_name`
  , DROP COLUMN `card_type`
  , DROP COLUMN `card_number`
  , DROP COLUMN `card_expire`
  , DROP COLUMN `card_cvv2`
  , DROP COLUMN `credit_value`
  , DROP COLUMN `credit_used`
  , ADD COLUMN `salt` varchar(10) NULL DEFAULT '';

REPLACE INTO `?:status_data` (`status`, `type`, `param`, `value`) VALUES ('B', 'O', 'color', '28abf6');
REPLACE INTO `?:status_data` (`status`, `type`, `param`, `value`) VALUES ('C', 'O', 'color', '97cf4d');
REPLACE INTO `?:status_data` (`status`, `type`, `param`, `value`) VALUES ('D', 'O', 'color', 'ff5215');
REPLACE INTO `?:status_data` (`status`, `type`, `param`, `value`) VALUES ('F', 'O', 'color', 'ff5215');
REPLACE INTO `?:status_data` (`status`, `type`, `param`, `value`) VALUES ('I', 'O', 'color', 'c2c2c2');
REPLACE INTO `?:status_data` (`status`, `type`, `param`, `value`) VALUES ('O', 'O', 'color', 'ff9522');
REPLACE INTO `?:status_data` (`status`, `type`, `param`, `value`) VALUES ('P', 'O', 'color', '97cf4d');
REPLACE INTO `?:status_data` (`status`, `type`, `param`, `value`) VALUES ('A', 'G', 'color', '97cf4d');
REPLACE INTO `?:status_data` (`status`, `type`, `param`, `value`) VALUES ('C', 'G', 'color', 'c2c2c2');
REPLACE INTO `?:status_data` (`status`, `type`, `param`, `value`) VALUES ('P', 'G', 'color', 'ff9522');
REPLACE INTO `?:status_data` (`status`, `type`, `param`, `value`) VALUES ('U', 'G', 'color', '28abf6');

UPDATE `?:users` SET user_type = 'V' WHERE user_type = 'A' AND company_id <> 0;

DROP TABLE IF EXISTS ?:settings_descriptions;
CREATE TABLE `?:settings_descriptions` (
  `object_id` mediumint(8) unsigned NOT NULL auto_increment,
  `object_type` varchar(1) NOT NULL default '',
  `lang_code` char(2) NOT NULL default 'EN',
  `value` text NOT NULL,
  `tooltip` text NOT NULL,
  PRIMARY KEY  (`object_id`,`object_type`,`lang_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('1', 'S', 'EN', 'E-mails', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('2', 'S', 'EN', 'General', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('3', 'S', 'EN', 'Dynamic HTML', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('4', 'S', 'EN', 'Appearance', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('5', 'S', 'EN', 'Company', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('6', 'S', 'EN', 'Google base', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('7', 'S', 'EN', 'Shipping settings', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('8', 'S', 'EN', 'Site map', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('9', 'S', 'EN', 'Thumbnails', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('10', 'S', 'EN', 'Reports', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('11', 'S', 'EN', 'Image verification', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('12', 'S', 'EN', 'Logging', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('13', 'S', 'EN', 'Upgrade center', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('15', 'S', 'EN', 'Security settings', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('16', 'S', 'EN', 'Vendors', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('1', 'O', 'EN', 'Allow users to create shipments', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('2', 'O', 'EN', 'Allow customer to signup for user group', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('3', 'O', 'EN', 'Exception style', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('4', 'O', 'EN', 'News', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('7', 'O', 'EN', 'Help us improve CS-Cart', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('8', 'O', 'EN', 'Hostname', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('9', 'O', 'EN', 'Username', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('10', 'O', 'EN', 'Password', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('11', 'O', 'EN', 'Directory', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('12', 'O', 'EN', 'License number', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('14', 'O', 'EN', 'Check for updates automatically', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('15', 'O', 'EN', 'Alternative currency display format', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('16', 'O', 'EN', 'Weight symbol', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('17', 'O', 'EN', 'Default address', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('18', 'O', 'EN', 'Default zipcode', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('19', 'O', 'EN', 'Default city', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('20', 'O', 'EN', 'Default country', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('21', 'O', 'EN', 'Default state', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('22', 'O', 'EN', 'Products per page', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('23', 'O', 'EN', 'Products per page', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('24', 'O', 'EN', 'Elements per page', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('25', 'O', 'EN', 'Company state', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('26', 'O', 'EN', 'Company city', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('27', 'O', 'EN', 'Company address', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('28', 'O', 'EN', 'Company phone', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('29', 'O', 'EN', 'Company phone 2', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('30', 'O', 'EN', 'Company fax', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('31', 'O', 'EN', 'Company name', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('32', 'O', 'EN', 'Company website', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('33', 'O', 'EN', 'Company zip code', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('34', 'O', 'EN', 'Company country', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('35', 'O', 'EN', 'User department e-mail address', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('36', 'O', 'EN', 'Site administrator e-mail address', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('37', 'O', 'EN', 'Order department e-mail address', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('38', 'O', 'EN', 'Help/Support department e-mail address', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('39', 'O', 'EN', 'Reply-To newsletter e-mail address', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('40', 'O', 'EN', 'Year when the store started its operation', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('41', 'O', 'EN', 'Default phone', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('42', 'O', 'EN', 'Grams in the unit of weight defined by the weight symbol', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('43', 'O', 'EN', 'CMS pages per page', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('44', 'O', 'EN', 'Allow negative amount in inventory', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('45', 'O', 'EN', 'Orders per page', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('46', 'O', 'EN', 'Orders per page', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('47', 'O', 'EN', 'Elements per page', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('48', 'O', 'EN', 'Notice displaying time (to turn off the autohide function enter 0)', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('49', 'O', 'EN', 'Default wysiwyg editor', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('50', 'O', 'EN', 'Default image previewer', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('51', 'O', 'EN', 'Display quick menu', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('52', 'O', 'EN', 'Date format', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('53', 'O', 'EN', 'Customer area default language', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('54', 'O', 'EN', 'Administration panel default language', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('55', 'O', 'EN', 'Enable secure connection at checkout (SSL certificate is required to be installed on your server)', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('56', 'O', 'EN', 'Enable secure connection in the administration panel (SSL certificate is required to be installed on your server)', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('57', 'O', 'EN', 'Keep HTTPS connection once a secure page is visited', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('58', 'O', 'EN', 'Enable inventory tracking', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('59', 'O', 'EN', 'Template debugging console', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('60', 'O', 'EN', 'Disable shipping', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('61', 'O', 'EN', 'Access key to temporarily closed store. Use: http://www.company.com/index.php?store_access_key=key_value.', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('62', 'O', 'EN', 'Initial order ID value', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('63', 'O', 'EN', 'Number of columns in the product list', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('64', 'O', 'EN', 'Product list default sorting', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('65', 'O', 'EN', 'Download key TTL (for electronically distributed products), hours', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('66', 'O', 'EN', 'Time format', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('67', 'O', 'EN', 'Allow users to create multiple profiles (shipping and billing addresses) for one account', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('69', 'O', 'EN', 'Minimum order amount', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('70', 'O', 'EN', 'Define minimum order amount by', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('71', 'O', 'EN', 'Allow shopping for unlogged customers', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('72', 'O', 'EN', 'Show products from subcategories of the selected category', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('73', 'O', 'EN', 'Disable anonymous checkout', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('77', 'O', 'EN', 'Low stock notification threshold', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('78', 'O', 'EN', 'Administrator must activate new user accounts', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('79', 'O', 'EN', 'User e-mail is used as login', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('83', 'O', 'EN', 'AJAX(Javascript)-based pagination', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('84', 'O', 'EN', 'Day', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('85', 'O', 'EN', 'Week', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('86', 'O', 'EN', 'Month', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('87', 'O', 'EN', 'Year', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('89', 'O', 'EN', 'JPEG format quality (0-100)', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('90', 'O', 'EN', 'Thumbnail format', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('91', 'O', 'EN', 'Thumbnail background color', 'Leave emtpy for transparent background (requires PHP GD library version > 2.0.1)');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('93', 'O', 'EN', 'Display modifiers for product options', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('94', 'O', 'EN', 'Show out of stock products', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('95', 'O', 'EN', 'AJAX(Javascript)-based the \'Add to cart\' button', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('96', 'O', 'EN', 'AJAX(Javascript)-based the \'Add to compare list\' button', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('97', 'O', 'EN', 'Enable secure connection for authentication, profile and orders pages (SSL certificate is required to be installed on your server)', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('98', 'O', 'EN', 'Allow customers to use single discount coupon only', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('100', 'O', 'EN', 'Ask customers to agree with terms &amp; conditions during checkout', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('102', 'O', 'EN', 'Enable FedEx', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('103', 'O', 'EN', 'Enable UPS', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('104', 'O', 'EN', 'Enable USPS', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('105', 'O', 'EN', 'Show the \'Categories\' section', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('106', 'O', 'EN', 'Show only root level categories links in the \'Categories\' section', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('107', 'O', 'EN', 'Show the \'Site info\' section', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('108', 'O', 'EN', 'Method of sending e-mails', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('109', 'O', 'EN', 'SMTP host', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('110', 'O', 'EN', 'Use SMTP authentication', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('111', 'O', 'EN', 'SMTP username', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('112', 'O', 'EN', 'SMTP password', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('113', 'O', 'EN', 'Path to sendmail program', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('114', 'O', 'EN', 'Enable DHL', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('115', 'O', 'EN', 'Enable Australia Post', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('116', 'O', 'EN', 'Display prices with taxes on category/product pages if the method of calculating taxes is based on a unit\'s price', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('117', 'O', 'EN', 'Image height', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('118', 'O', 'EN', 'String length', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('119', 'O', 'EN', 'Minimum font size', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('120', 'O', 'EN', 'Maximum font size', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('121', 'O', 'EN', 'String type', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('122', 'O', 'EN', 'Character shadows', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('123', 'O', 'EN', 'Color', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('124', 'O', 'EN', 'Path to background image (relative to CS-Cart root directory)', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('125', 'O', 'EN', 'Number of grid lines', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('126', 'O', 'EN', 'Grid color (hexadecimal code)', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('127', 'O', 'EN', 'Login form', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('128', 'O', 'EN', 'Register form', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('129', 'O', 'EN', 'Custom forms', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('130', 'O', 'EN', 'Send to friend form', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('131', 'O', 'EN', 'Comments and reviews forms', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('132', 'O', 'EN', 'Checkout (user information) form', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('133', 'O', 'EN', 'Image width', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('134', 'O', 'EN', 'Do not use verification if user is logged in', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('135', 'O', 'EN', 'Polls', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('136', 'O', 'EN', 'Track my order form', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('137', 'O', 'EN', 'Allow customers to pay order again if transaction was declined', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('138', 'O', 'EN', 'Checkout style', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('139', 'O', 'EN', 'Time zone', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('140', 'O', 'EN', 'Estimate shipping cost on cart page', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('141', 'O', 'EN', 'Allow guest to create an account after successful order', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('145', 'O', 'EN', 'Display prices with taxes on cart/checkout pages if the method of calculating taxes is based on a unit\'s price', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('146', 'O', 'EN', 'Display In stock as a field', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('147', 'O', 'EN', 'Display mini thumbnail images as a gallery', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('148', 'O', 'EN', 'Use \'Value changer\' for the Quantity field', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('149', 'O', 'EN', 'Display the \'Pagination section\' on the top of the listed object', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('150', 'O', 'EN', 'Estimate taxes using default address on cart/checkout pages', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('151', 'O', 'EN', 'Proxy host', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('152', 'O', 'EN', 'Proxy port', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('153', 'O', 'EN', 'Proxy user', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('154', 'O', 'EN', 'Proxy password', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('155', 'O', 'EN', 'Enable Canada Post', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('156', 'O', 'EN', 'Search also in', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('157', 'O', 'EN', 'Activate revisions for:', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('158', 'O', 'EN', 'Orders', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('159', 'O', 'EN', 'Users', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('160', 'O', 'EN', 'Products', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('161', 'O', 'EN', 'Categories', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('162', 'O', 'EN', 'Database', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('163', 'O', 'EN', 'Requests', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('164', 'O', 'EN', 'Enable Swiss Post', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('165', 'O', 'EN', 'Do not use verification after first valid answer', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('166', 'O', 'EN', 'Calendar date format', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('167', 'O', 'EN', 'Calendar week starts from', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('169', 'O', 'EN', 'Product list default layout', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('170', 'O', 'EN', 'Use the selected layout for current category or search page only', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('171', 'O', 'EN', 'Available product list layouts', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('172', 'O', 'EN', 'Redirect customer to the cart contents page if non-AJAX addition to a cart is used', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('173', 'O', 'EN', 'When the customer clicks on any Checkout button/link in the store, redirect to the Cart content page first', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('174', 'O', 'EN', 'Minimum administrator password length', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('175', 'O', 'EN', 'Administrator password must contain both letters and numbers', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('176', 'O', 'EN', 'Force administrators to change password on the first login', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('177', 'O', 'EN', 'Password validity period in days (0 - unlimited)', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('178', 'O', 'EN', 'Access key to cron script which sends e-mail notifications of password change. Use: http://www.company.com/admin.php?dispatch=profiles.password_reminder&cron_password=key_value.', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('179', 'O', 'EN', 'Tax calculation method based on', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('180', 'O', 'EN', 'Product detailed page layout', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('181', 'O', 'EN', 'Send feedback to cs-cart.com', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('182', 'O', 'EN', 'Profile address section order', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('183', 'O', 'EN', 'Unsaved changes warning', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('184', 'O', 'EN', 'Products list (category, search, etc) thumbnail width', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('185', 'O', 'EN', 'Products list (category, search, etc) thumbnail height', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('186', 'O', 'EN', 'Product details page thumbnail width', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('187', 'O', 'EN', 'Product details page thumbnail height', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('188', 'O', 'EN', 'Detailed product image width', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('189', 'O', 'EN', 'Detailed product image height', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('190', 'O', 'EN', 'Product cart page thumbnail width', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('191', 'O', 'EN', 'Product cart page thumbnail height', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('192', 'O', 'EN', 'Categories list thumbnail width', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('193', 'O', 'EN', 'Categories list thumbnail height', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('194', 'O', 'EN', 'Category details page thumbnail width', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('195', 'O', 'EN', 'Category details page thumbnail height', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('196', 'O', 'EN', 'Detailed category image width', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('197', 'O', 'EN', 'Detailed category image height', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('199', 'O', 'EN', 'Show menu description', 'If you disable this check box, description of menu items will be hidden');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('202', 'O', 'EN', 'Shipping processors', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('203', 'O', 'EN', 'Administrator  settings', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('204', 'O', 'EN', 'Format of time intervals', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('205', 'O', 'EN', 'Customer settings', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('206', 'O', 'EN', 'SMTP server settings', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('207', 'O', 'EN', 'Default location', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('208', 'O', 'EN', 'Customer settings', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('209', 'O', 'EN', 'Sendmail settings', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('210', 'O', 'EN', 'Catalog', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('211', 'O', 'EN', 'Common settings', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('212', 'O', 'EN', 'Promotions', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('213', 'O', 'EN', 'Users/cart', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('215', 'O', 'EN', 'Use for', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('216', 'O', 'EN', 'Proxy server for outgoing connections', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('217', 'O', 'EN', 'Search options', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('218', 'O', 'EN', 'Revisions', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('219', 'O', 'EN', 'FTP server options', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('221', 'O', 'EN', 'Products list layouts settings', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('223', 'O', 'EN', 'Disregard product options when calculating quantity discounts', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('1001', 'O', 'EN', 'Allow users to apply for vendor account', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('225', 'O', 'EN', 'Display product vendor', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('1000', 'O', 'EN', 'Vendors per page', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('272', 'O', 'EN', 'Display track my orders section', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('274', 'O', 'EN', 'General', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('275', 'O', 'EN', 'Quick registration', 'Require only contact information on registration');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('288', 'O', 'EN', 'Display product details in tabs', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('289', 'O', 'EN', 'Available product list sortings', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('290', 'O', 'EN', 'Disable quick view', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('1', 'V', 'EN', 'Hide exception', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('2', 'V', 'EN', 'Show warning on exception', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('3', 'V', 'EN', 'Create', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('4', 'V', 'EN', 'Delete', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('5', 'V', 'EN', 'Update', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('6', 'V', 'EN', 'Manual feedback', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('7', 'V', 'EN', 'Automatic feedback', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('8', 'V', 'EN', 'Show prices in default and selected currencies', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('9', 'V', 'EN', 'Show prices in selected currency only', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('10', 'V', 'EN', '29/09/2005 (day/month/year)', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('11', 'V', 'EN', '29-09-2005 (day-month-year)', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('12', 'V', 'EN', '29.09.2005 (day.month.year)', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('13', 'V', 'EN', '09/29/2005 (month/day/year)', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('14', 'V', 'EN', '09-29-2005 (month-day-year)', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('15', 'V', 'EN', '09.29.2005 (month.day.year)', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('16', 'V', 'EN', '2005/09/29 (year/month/day)', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('17', 'V', 'EN', '2005-09-29 (year-month-day)', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('18', 'V', 'EN', '2005.09.29 (year.month.day)', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('19', 'V', 'EN', 'Sep 29, 2005 (month day, year)', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('20', 'V', 'EN', '29 Sep 2005 (day month year)', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('21', 'V', 'EN', 'Thursday, September 29, 2005 (day of week, month day, year)', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('22', 'V', 'EN', 'Thursday, 29 September 2005 (day of week, day month year)', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('23', 'V', 'EN', 'Default', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('24', 'V', 'EN', 'Product name', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('25', 'V', 'EN', 'Price', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('26', 'V', 'EN', '15:43', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('27', 'V', 'EN', '15.43', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('28', 'V', 'EN', '3:43 PM', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('29', 'V', 'EN', '3.43 PM', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('30', 'V', 'EN', '15:43:55', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('31', 'V', 'EN', '15.43.55', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('32', 'V', 'EN', '3:43:55 PM', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('33', 'V', 'EN', '3.43.55 PM', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('34', 'V', 'EN', 'Products', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('35', 'V', 'EN', 'Products with shipping', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('36', 'V', 'EN', 'Allow', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('37', 'V', 'EN', 'Hide price and the \"Add to cart\" button', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('38', 'V', 'EN', 'Hide the \"Add to cart\" button', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('39', 'V', 'EN', 'Tue, Jan 3', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('40', 'V', 'EN', 'January 3, 2006', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('41', 'V', 'EN', '01.03.06', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('42', 'V', 'EN', 'Tue Jan 3, 2006', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('43', 'V', 'EN', 'Jan 3', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('44', 'V', 'EN', '1 week', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('45', 'V', 'EN', '01.03', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('46', 'V', 'EN', '1, Jan', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('47', 'V', 'EN', 'January, 2006', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('48', 'V', 'EN', 'January', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('49', 'V', 'EN', '01', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('50', 'V', 'EN', 'Jan', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('51', 'V', 'EN', '01-2006', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('52', 'V', 'EN', '2006', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('53', 'V', 'EN', '06', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('54', 'V', 'EN', 'via SMTP server', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('55', 'V', 'EN', 'via php mail function', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('56', 'V', 'EN', 'via sendmail program', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('57', 'V', 'EN', 'Digits only', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('58', 'V', 'EN', 'Letters only', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('59', 'V', 'EN', 'Mixed', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('60', 'V', 'EN', 'One-page checkout', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('62', 'V', 'EN', 'Multi-page checkout', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('63', 'V', 'EN', '(GMT+10:00) Hobart', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('64', 'V', 'EN', '(GMT+10:00) Guam, Port Moresby', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('65', 'V', 'EN', '(GMT+10:00) Canberra, Melbourne, Sydney', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('66', 'V', 'EN', '(GMT+10:00) Brisbane', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('67', 'V', 'EN', '(GMT+09:30) Darwin', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('68', 'V', 'EN', '(GMT+09:30) Adelaide', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('69', 'V', 'EN', '(GMT+09:00) Yakutsk', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('70', 'V', 'EN', '(GMT+09:00) Seoul', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('71', 'V', 'EN', '(GMT+09:00) Osaka, Sapporo, Tokyo', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('72', 'V', 'EN', '(GMT+08:00) Taipei', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('73', 'V', 'EN', '(GMT+08:00) Perth', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('74', 'V', 'EN', '(GMT+08:00) Kuala Lumpur, Singapore', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('75', 'V', 'EN', '(GMT+08:00) Irkutsk, Ulaan Bataar', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('76', 'V', 'EN', '(GMT+08:00) Beijing, Chongqing, Hong Kong, Urumqi', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('77', 'V', 'EN', '(GMT+07:00) Krasnoyarsk', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('78', 'V', 'EN', '(GMT+07:00) Bangkok, Hanoi, Jakarta', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('79', 'V', 'EN', '(GMT+06:30) Rangoon', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('80', 'V', 'EN', '(GMT+06:00) Sri Jayawardenepura', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('81', 'V', 'EN', '(GMT+06:00) Astana, Dhaka', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('82', 'V', 'EN', '(GMT+06:00) Almaty, Novosibirsk', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('83', 'V', 'EN', '(GMT+05:45) Kathmandu', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('84', 'V', 'EN', '(GMT+05:30) Chennai, Kolkata, Mumbai, New Delhi', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('85', 'V', 'EN', '(GMT+05:00) Islamabad, Karachi, Tashkent', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('86', 'V', 'EN', '(GMT+05:00) Ekaterinburg', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('87', 'V', 'EN', '(GMT+04:30) Kabul', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('88', 'V', 'EN', '(GMT+04:00) Baku, Tbilisi, Yerevan', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('89', 'V', 'EN', '(GMT+04:00) Abu Dhabi, Muscat', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('90', 'V', 'EN', '(GMT+03:30) Tehran', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('91', 'V', 'EN', '(GMT+03:00) Nairobi', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('92', 'V', 'EN', '(GMT+03:00) Moscow, St. Petersburg, Volgograd', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('93', 'V', 'EN', '(GMT+03:00) Kuwait, Riyadh', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('94', 'V', 'EN', '(GMT+03:00) Baghdad', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('95', 'V', 'EN', '(GMT+02:00) Jerusalem', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('96', 'V', 'EN', '(GMT+02:00) Helsinki, Kyiv, Riga, Sofia, Tallinn, Vilnius', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('97', 'V', 'EN', '(GMT+02:00) Harare, Pretoria', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('98', 'V', 'EN', '(GMT+02:00) Cairo', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('99', 'V', 'EN', '(GMT+02:00) Bucharest', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('100', 'V', 'EN', '(GMT+02:00) Athens, Beirut, Istanbul, Minsk', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('101', 'V', 'EN', '(GMT+01:00) West Central Africa', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('102', 'V', 'EN', '(GMT+01:00) Brussels, Copenhagen, Madrid, Paris', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('103', 'V', 'EN', '(GMT+01:00) Sarajevo, Skopje, Warsaw, Zagreb', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('104', 'V', 'EN', '(GMT+01:00) Belgrade, Bratislava, Budapest, Ljubljana, Prague', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('105', 'V', 'EN', '(GMT+01:00) Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('106', 'V', 'EN', '(GMT) Greenwich Mean Time : Dublin, Edinburgh, Lisbon, London', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('107', 'V', 'EN', '(GMT) Casablanca, Monrovia', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('108', 'V', 'EN', '(GMT-01:00) Azores', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('109', 'V', 'EN', '(GMT-01:00) Cape Verde Is.', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('110', 'V', 'EN', '(GMT-02:00) Mid-Atlantic', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('111', 'V', 'EN', '(GMT-03:00) Brasilia', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('112', 'V', 'EN', '(GMT-03:00) Buenos Aires, Georgetown', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('113', 'V', 'EN', '(GMT-03:00) Greenland', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('114', 'V', 'EN', '(GMT-03:30) Newfoundland', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('115', 'V', 'EN', '(GMT-04:00) Atlantic Time (Canada)', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('116', 'V', 'EN', '(GMT-04:00) Caracas, La Paz', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('117', 'V', 'EN', '(GMT-04:00) Santiago', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('118', 'V', 'EN', '(GMT-05:00) Bogota, Lima, Quito', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('119', 'V', 'EN', '(GMT-05:00) Eastern Time (US & Canada)', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('120', 'V', 'EN', '(GMT-05:00) Indiana (East)', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('121', 'V', 'EN', '(GMT-06:00) Central America', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('122', 'V', 'EN', '(GMT-06:00) Central Time (US & Canada)', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('123', 'V', 'EN', '(GMT-06:00) Guadalajara, Mexico City, Monterrey', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('124', 'V', 'EN', '(GMT-06:00) Saskatchewan', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('125', 'V', 'EN', '(GMT-07:00) Arizona', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('126', 'V', 'EN', '(GMT-07:00) Chihuahua, La Paz, Mazatlan', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('127', 'V', 'EN', '(GMT-07:00) Mountain Time (US & Canada)', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('128', 'V', 'EN', '(GMT-08:00) Pacific Time (US & Canada); Tijuana', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('129', 'V', 'EN', '(GMT-09:00) Alaska', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('130', 'V', 'EN', '(GMT-10:00) Hawaii', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('131', 'V', 'EN', '(GMT-11:00) Midway Island, Samoa', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('132', 'V', 'EN', '(GMT-12:00) International Date Line West', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('133', 'V', 'EN', '(GMT+10:00) Vladivostok', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('134', 'V', 'EN', '(GMT+11:00) Magadan, Solomon Is., New Caledonia', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('135', 'V', 'EN', '(GMT+12:00) Auckland, Wellington', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('136', 'V', 'EN', '(GMT+12:00) Fiji, Kamchatka, Marshall Is.', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('137', 'V', 'EN', '(GMT+13:00) Nuku\'alofa', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('138', 'V', 'EN', 'Create', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('139', 'V', 'EN', 'Delete', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('140', 'V', 'EN', 'Update', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('141', 'V', 'EN', 'Change', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('142', 'V', 'EN', 'Create', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('143', 'V', 'EN', 'Delete', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('144', 'V', 'EN', 'Update', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('145', 'V', 'EN', 'Session', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('146', 'V', 'EN', 'Failed logins', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('147', 'V', 'EN', 'Create', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('148', 'V', 'EN', 'Delete', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('149', 'V', 'EN', 'Update', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('150', 'V', 'EN', 'Low stock', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('151', 'V', 'EN', 'Create', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('152', 'V', 'EN', 'Delete', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('153', 'V', 'EN', 'Update', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('154', 'V', 'EN', 'Restore', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('155', 'V', 'EN', 'Backup', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('156', 'V', 'EN', 'Optimize', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('157', 'V', 'EN', 'Errors', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('158', 'V', 'EN', 'HTTP/HTTPS', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('159', 'V', 'EN', '09/30/2008 (month/day/year)', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('160', 'V', 'EN', '30/09/2008 (day/month/year)', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('161', 'V', 'EN', 'Sunday', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('162', 'V', 'EN', 'Monday', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('163', 'V', 'EN', 'Unit price', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('164', 'V', 'EN', 'Subtotal', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('165', 'V', 'EN', 'Billing first', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('166', 'V', 'EN', 'Shipping first', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('167', 'V', 'EN', 'Runtime', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('168', 'V', 'EN', 'Deprecated features', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('70024', 'O', 'EN', '{HD:TIMESTAMP}', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('38751', 'O', 'EN', 'eciton_lairt', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('276', 'O', 'EN', 'Enable EMS(Russian post)', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('1002', 'O', 'EN', 'Automatically create the administrator account for the new approved vendor. (If the \"Allow users to apply for vendor account\" setting is enabled)', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('1003', 'O', 'EN', 'Apply for a vendor account form', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('1004', 'O', 'EN', 'Include shipping cost in vendors commission', '');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('1005', 'O', 'EN', 'Take payment surcharge from vendors', 'If enabled a customers will not be able see payment surcharge and it will not be applied to order total');

UPDATE `?:settings_descriptions` SET lang_code = (SELECT value FROM ?:settings WHERE option_name = 'admin_default_language' AND section_id = 'Appearance');

DROP TABLE IF EXISTS ?:settings_objects;
CREATE TABLE `?:settings_objects` (
  `object_id` mediumint(8) unsigned NOT NULL auto_increment,
  `edition_type` set('NONE','ROOT','VENDOR','PRO:NONE','PRO:ROOT','MVE:NONE','MVE:ROOT','ULT:NONE','ULT:ROOT','ULT:VENDOR','ULT:VENDORONLY') NOT NULL default 'ROOT',
  `name` varchar(128) NOT NULL default '',
  `section_id` smallint(4) unsigned NOT NULL,
  `section_tab_id` smallint(4) unsigned NOT NULL,
  `type` char(1) NOT NULL default 'I',
  `value` varchar(255) NOT NULL default '',
  `position` smallint(5) unsigned NOT NULL default '0',
  `is_global` char(1) NOT NULL default 'Y',
  `handler` varchar(128) NOT NULL,
  PRIMARY KEY  (`object_id`),
  KEY `is_global` (`is_global`),
  KEY `position` (`position`),
  KEY `section_id` (`section_id`,`section_tab_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('1', 'ROOT,ULT:VENDOR', 'use_shipments', '2', '0', 'C', 'Y', '55', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('2', 'ROOT,ULT:VENDOR', 'allow_usergroup_signup', '2', '0', 'C', 'Y', '249', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('3', 'ROOT,ULT:VENDOR', 'exception_style', '2', '0', 'S', 'hide', '160', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('4', 'ROOT', 'log_type_news', '12', '0', 'N', '#M#create=Y&delete=Y&update=Y', '10', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('7', 'ROOT', 'feedback_type', '2', '0', 'S', 'manual', '57', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('1001', 'ROOT', 'apply_for_vendor', '16', '0', 'C', 'Y', '130', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('225', 'ROOT', 'display_supplier', '16', '0', 'C', 'Y', '57', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('1000', 'ROOT', 'vendors_per_page', '16', '0', 'I', '10', '150', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('8', 'ROOT', 'ftp_hostname', '13', '0', 'I', '', '30', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('9', 'ROOT', 'ftp_username', '13', '0', 'I', '', '40', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('10', 'ROOT', 'ftp_password', '13', '0', 'P', '', '50', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('11', 'ROOT', 'ftp_directory', '13', '0', 'I', '', '60', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('12', 'ROOT', 'license_number', '13', '0', 'I', '', '10', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('14', 'ROOT', 'auto_check_updates', '2', '0', 'C', 'Y', '58', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('15', 'ROOT,ULT:VENDOR', 'alternative_currency', '2', '0', 'S', 'N', '10', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('16', 'ROOT,ULT:VENDOR', 'weight_symbol', '2', '0', 'I', 'lbs', '30', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('17', 'ROOT,ULT:VENDOR', 'default_address', '2', '0', 'I', 'Boston street', '70', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('18', 'ROOT,ULT:VENDOR', 'default_zipcode', '2', '0', 'I', '02125', '80', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('19', 'ROOT,ULT:VENDOR', 'default_city', '2', '0', 'I', 'Boston', '90', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('20', 'ROOT,ULT:VENDOR', 'default_country', '2', '0', 'X', 'US', '100', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('21', 'ROOT,ULT:VENDOR', 'default_state', '2', '0', 'W', 'MA', '110', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('22', 'ROOT,ULT:VENDOR', 'products_per_page', '4', '0', 'I', '12', '100', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('23', 'ROOT', 'admin_products_per_page', '4', '0', 'I', '10', '30', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('24', 'ROOT', 'admin_elements_per_page', '4', '0', 'I', '10', '60', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('25', 'ROOT,ULT:VENDOR', 'company_state', '5', '0', 'W', 'MA', '40', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('26', 'ROOT,ULT:VENDOR', 'company_city', '5', '0', 'I', 'Boston', '20', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('27', 'ROOT,ULT:VENDOR', 'company_address', '5', '0', 'I', '44 Main street', '10', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('28', 'ROOT,ULT:VENDOR', 'company_phone', '5', '0', 'I', '6175556985', '60', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('29', 'ROOT,ULT:VENDOR', 'company_phone_2', '5', '0', 'I', '', '70', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('30', 'ROOT,ULT:VENDOR', 'company_fax', '5', '0', 'I', '', '80', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('31', 'ROOT,ULT:VENDOR', 'company_name', '5', '0', 'I', 'Simbirsk Technologies Ltd', '0', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('32', 'ROOT,ULT:VENDOR', 'company_website', '5', '0', 'I', 'http://www.cs-cart.com/', '90', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('33', 'ROOT,ULT:VENDOR', 'company_zipcode', '5', '0', 'I', '02116', '50', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('34', 'ROOT,ULT:VENDOR', 'company_country', '5', '0', 'X', 'US', '30', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('35', 'ROOT,ULT:VENDOR', 'company_users_department', '5', '0', 'I', 'no-reply@cs-cart.com', '100', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('36', 'ROOT,ULT:VENDOR', 'company_site_administrator', '5', '0', 'I', 'no-reply@cs-cart.com', '110', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('37', 'ROOT,ULT:VENDOR', 'company_orders_department', '5', '0', 'I', 'no-reply@cs-cart.com', '120', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('38', 'ROOT,ULT:VENDOR', 'company_support_department', '5', '0', 'I', 'no-reply@cs-cart.com', '130', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('39', 'ROOT,ULT:VENDOR', 'company_newsletter_email', '5', '0', 'I', 'no-reply@cs-cart.com', '140', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('40', 'ROOT,ULT:VENDOR', 'company_start_year', '5', '0', 'I', '2004', '95', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('41', 'ROOT,ULT:VENDOR', 'default_phone', '2', '0', 'I', '6175556985', '120', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('42', 'ROOT,ULT:VENDOR', 'weight_symbol_grams', '2', '0', 'I', '453.6', '40', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('43', 'ROOT', 'admin_pages_per_page', '4', '0', 'I', '10', '50', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('44', 'ROOT', 'allow_negative_amount', '2', '0', 'C', 'N', '135', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('45', 'ROOT', 'admin_orders_per_page', '4', '0', 'I', '10', '20', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('46', 'ROOT,ULT:VENDOR', 'orders_per_page', '4', '0', 'I', '10', '90', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('47', 'ROOT,ULT:VENDOR', 'elements_per_page', '4', '0', 'I', '10', '130', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('48', 'ROOT', 'notice_displaying_time', '4', '0', 'I', '5', '260', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('49', 'ROOT', 'default_wysiwyg_editor', '4', '0', 'K', 'tinymce', '60', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('50', 'ROOT', 'default_image_previewer', '4', '0', 'K', 'fancybox', '189', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('51', 'ROOT', 'show_quick_menu', '4', '0', 'C', 'N', '60', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('52', 'ROOT', 'date_format', '4', '0', 'S', '%m/%d/%Y', '200', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('53', 'ROOT,ULT:VENDOR', 'customer_default_language', '4', '0', 'S', 'EN', '80', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('54', 'ROOT', 'admin_default_language', '4', '0', 'S', 'EN', '15', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('55', 'ROOT,ULT:VENDOR', 'secure_checkout', '2', '0', 'C', 'N', '1', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('56', 'ROOT', 'secure_admin', '2', '0', 'C', 'N', '2', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('57', 'ROOT,ULT:VENDOR', 'keep_https', '2', '0', 'C', 'N', '5', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('58', 'ROOT,ULT:VENDOR', 'inventory_tracking', '2', '0', 'C', 'Y', '130', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('59', 'ROOT,ULT:VENDOR', 'debugging_console', '2', '0', 'C', 'N', '6', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('60', 'ROOT', 'disable_shipping', '7', '0', 'C', 'N', '5', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('61', 'ROOT,ULT:VENDOR', 'store_access_key', '2', '0', 'I', '', '51', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('62', 'ROOT', 'order_start_id', '2', '0', 'I', '', '52', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('63', 'ROOT,ULT:VENDOR', 'columns_in_products_list', '4', '0', 'I', '3', '150', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('64', 'ROOT,ULT:VENDOR', 'default_products_sorting', '4', '0', 'K', 'product-asc', '198', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('65', 'ROOT,ULT:VENDOR', 'edp_key_ttl', '2', '0', 'I', '24', '140', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('66', 'ROOT', 'time_format', '4', '0', 'S', '%H:%M', '210', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('67', 'ROOT,ULT:VENDOR', 'user_multiple_profiles', '2', '0', 'C', 'N', '240', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('69', 'ROOT,ULT:VENDOR', 'min_order_amount', '2', '0', 'I', '0', '242', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('70', 'ROOT,ULT:VENDOR', 'min_order_amount_type', '2', '0', 'S', 'S', '242', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('71', 'ROOT,ULT:VENDOR', 'allow_anonymous_shopping', '2', '0', 'S', 'Y', '243', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('72', 'ROOT,ULT:VENDOR', 'show_products_from_subcategories', '2', '0', 'C', 'Y', '150', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('73', 'ROOT,ULT:VENDOR', 'disable_anonymous_checkout', '2', '0', 'C', 'N', '244', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('74', 'ROOT', 'skin_name_admin', '0', '0', 'I', 'basic', '0', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('75', 'ROOT,ULT:VENDOR', 'skin_name_customer', '0', '0', 'I', 'basic', '0', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('76', 'ROOT', 'show_menu_mouseover', '0', '0', 'C', 'N', '0', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('77', 'ROOT,ULT:VENDOR', 'low_stock_threshold', '2', '0', 'I', '0', '145', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('78', 'ROOT,ULT:VENDOR', 'approve_user_profiles', '2', '0', 'C', 'N', '250', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('79', 'ROOT', 'use_email_as_login', '2', '0', 'C', 'Y', '260', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('83', 'ROOT,ULT:VENDOR', 'customer_ajax_based_pagination', '3', '0', 'C', 'Y', '40', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('84', 'ROOT,ULT:VENDOR', 'day', '10', '0', 'S', '%a, %b %e', '20', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('85', 'ROOT,ULT:VENDOR', 'week', '10', '0', 'S', '%U, %b', '30', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('86', 'ROOT,ULT:VENDOR', 'month', '10', '0', 'S', '%B', '40', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('87', 'ROOT,ULT:VENDOR', 'year', '10', '0', 'S', '%Y', '50', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('89', 'ROOT,ULT:VENDOR', 'jpeg_quality', '9', '0', 'U', '80', '90', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('90', 'ROOT,ULT:VENDOR', 'convert_to', '9', '0', 'S', 'original', '80', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('91', 'ROOT,ULT:VENDOR', 'thumbnail_background_color', '9', '0', 'I', '#ffffff', '70', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('92', 'ROOT', 'gift_registry_next_check', '0', '0', 'I', '1299789068', '20', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('93', 'ROOT,ULT:VENDOR', 'display_options_modifiers', '2', '0', 'C', 'Y', '155', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('94', 'ROOT,ULT:VENDOR', 'show_out_of_stock_products', '2', '0', 'C', 'Y', '180', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('95', 'ROOT,ULT:VENDOR', 'ajax_add_to_cart', '3', '0', 'C', 'Y', '20', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('96', 'ROOT,ULT:VENDOR', 'ajax_comparison_list', '3', '0', 'C', 'Y', '30', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('97', 'ROOT,ULT:VENDOR', 'secure_auth', '2', '0', 'C', 'N', '3', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('98', 'ROOT,ULT:VENDOR', 'use_single_coupon', '2', '0', 'C', 'Y', '225', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('99', 'ROOT', 'lh_visitor_data_last_clean', '0', '0', 'I', '1160220364', '0', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('100', 'ROOT,ULT:VENDOR', 'agree_terms_conditions', '2', '0', 'C', 'N', '270', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('102', 'ROOT', 'fedex_enabled', '7', '0', 'C', 'N', '20', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('103', 'ROOT', 'ups_enabled', '7', '0', 'C', 'N', '30', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('104', 'ROOT', 'usps_enabled', '7', '0', 'C', 'N', '40', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('105', 'ROOT,ULT:VENDOR', 'show_cats', '8', '0', 'C', 'Y', '10', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('106', 'ROOT,ULT:VENDOR', 'show_rootcats_only', '8', '0', 'C', 'N', '20', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('107', 'ROOT,ULT:VENDOR', 'show_site_info', '8', '0', 'C', 'Y', '35', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('108', 'ROOT', 'mailer_send_method', '1', '0', 'S', 'mail', '10', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('109', 'ROOT', 'mailer_smtp_host', '1', '0', 'I', '', '30', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('110', 'ROOT', 'mailer_smtp_auth', '1', '0', 'C', 'N', '60', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('111', 'ROOT', 'mailer_smtp_username', '1', '0', 'I', '', '40', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('112', 'ROOT', 'mailer_smtp_password', '1', '0', 'I', '', '50', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('113', 'ROOT', 'mailer_sendmail_path', '1', '0', 'I', '/usr/sbin/sendmail', '80', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('114', 'ROOT', 'dhl_enabled', '7', '0', 'C', 'N', '50', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('115', 'ROOT', 'aup_enabled', '7', '0', 'C', 'N', '60', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('116', 'ROOT,ULT:VENDOR', 'show_prices_taxed_clean', '4', '0', 'C', 'N', '184', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('117', 'ROOT,ULT:VENDOR', 'height', '11', '0', 'I', '25', '20', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('118', 'ROOT,ULT:VENDOR', 'string_length', '11', '0', 'I', '5', '30', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('119', 'ROOT,ULT:VENDOR', 'min_font_size', '11', '0', 'I', '14', '40', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('120', 'ROOT,ULT:VENDOR', 'max_font_size', '11', '0', 'I', '16', '50', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('121', 'ROOT,ULT:VENDOR', 'string_type', '11', '0', 'S', 'mixed', '60', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('122', 'ROOT,ULT:VENDOR', 'char_shadow', '11', '0', 'C', 'N', '65', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('123', 'ROOT,ULT:VENDOR', 'colour', '11', '0', 'C', 'N', '70', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('124', 'ROOT,ULT:VENDOR', 'background_image', '11', '0', 'F', '', '80', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('125', 'ROOT,ULT:VENDOR', 'lines_number', '11', '0', 'I', '20', '35', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('126', 'ROOT,ULT:VENDOR', 'grid_color', '11', '0', 'I', 'cccccc', '36', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('127', 'ROOT,ULT:VENDOR', 'use_for_login', '11', '0', 'C', 'Y', '100', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('128', 'ROOT,ULT:VENDOR', 'use_for_register', '11', '0', 'C', 'Y', '110', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('129', 'ROOT,ULT:VENDOR', 'use_for_form_builder', '11', '0', 'C', 'Y', '120', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('130', 'ROOT,ULT:VENDOR', 'use_for_send_to_friend', '11', '0', 'C', 'Y', '130', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('131', 'ROOT,ULT:VENDOR', 'use_for_discussion', '11', '0', 'C', 'Y', '140', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('132', 'ROOT,ULT:VENDOR', 'use_for_checkout', '11', '0', 'C', 'Y', '150', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('133', 'ROOT,ULT:VENDOR', 'width', '11', '0', 'I', '100', '10', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('134', 'ROOT,ULT:VENDOR', 'hide_if_logged', '11', '0', 'C', 'Y', '85', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('135', 'ROOT,ULT:VENDOR', 'use_for_polls', '11', '0', 'C', 'Y', '160', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('136', 'ROOT,ULT:VENDOR', 'use_for_track_orders', '11', '0', 'C', 'N', '170', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('137', 'ROOT,ULT:VENDOR', 'repay', '2', '0', 'C', 'Y', '280', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('138', 'ROOT,ULT:VENDOR', 'checkout_style', '2', '0', 'S', 'one_page', '290', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('139', 'ROOT', 'timezone', '4', '0', 'S', 'Europe/Moscow', '220', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('140', 'ROOT,ULT:VENDOR', 'estimate_shipping_cost', '2', '0', 'C', 'Y', '300', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('141', 'ROOT,ULT:VENDOR', 'allow_create_account_after_order', '2', '0', 'C', 'Y', '305', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('142', 'ROOT', 'cart_products_next_check', '0', '0', 'I', '1312846170', '0', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('143', 'ROOT,ULT:VENDOR', 'translation_mode', '0', '0', 'C', 'N', '0', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('144', 'ROOT,ULT:VENDOR', 'customization_mode', '0', '0', 'C', 'N', '0', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('145', 'ROOT,ULT:VENDOR', 'cart_prices_w_taxes', '4', '0', 'C', 'N', '185', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('146', 'ROOT,ULT:VENDOR', 'in_stock_field', '4', '0', 'C', 'N', '186', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('147', 'ROOT,ULT:VENDOR', 'thumbnails_gallery', '4', '0', 'C', 'N', '186', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('148', 'ROOT,ULT:VENDOR', 'quantity_changer', '4', '0', 'C', 'Y', '186', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('149', 'ROOT,ULT:VENDOR', 'top_pagination', '4', '0', 'C', 'N', '187', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('150', 'ROOT,ULT:VENDOR', 'taxes_using_default_address', '4', '0', 'C', 'N', '183', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('151', 'ROOT', 'proxy_host', '2', '0', 'I', '', '320', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('152', 'ROOT', 'proxy_port', '2', '0', 'I', '', '330', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('153', 'ROOT', 'proxy_user', '2', '0', 'I', '', '340', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('154', 'ROOT', 'proxy_password', '2', '0', 'P', '', '350', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('155', 'ROOT', 'can_enabled', '7', '0', 'C', 'N', '70', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('156', 'ROOT,ULT:VENDOR', 'search_objects', '2', '0', 'N', '', '1010', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('158', 'ROOT', 'log_type_orders', '12', '0', 'N', '#M#create=Y&delete=Y&update=Y&status=Y', '10', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('159', 'ROOT', 'log_type_users', '12', '0', 'N', '#M#create=Y&delete=Y&update=Y&session=Y&failed_login=Y', '10', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('160', 'ROOT', 'log_type_products', '12', '0', 'N', '#M#create=Y&delete=Y&update=Y&low_stock=Y', '10', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('161', 'ROOT', 'log_type_categories', '12', '0', 'N', '#M#create=Y&delete=Y&update=Y', '10', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('162', 'ROOT', 'log_type_database', '12', '0', 'N', '#M#restore=Y&backup=Y&optimize=Y&errors=Y', '10', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('163', 'ROOT', 'log_type_requests', '12', '0', 'N', '#M#http=Y', '10', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('164', 'ROOT', 'swisspost_enabled', '7', '0', 'C', 'N', '70', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('165', 'ROOT,ULT:VENDOR', 'hide_after_validation', '11', '0', 'C', 'Y', '87', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('166', 'ROOT', 'calendar_date_format', '4', '0', 'S', 'month_first', '230', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('167', 'ROOT', 'calendar_week_format', '4', '0', 'S', 'monday_first', '240', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('168', 'ROOT', 'store_mode', '0', '0', 'I', 'opened', '15', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('169', 'ROOT,ULT:VENDOR', 'default_products_layout', '4', '0', 'K', 'products_multicolumns', '195', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('170', 'ROOT,ULT:VENDOR', 'save_selected_layout', '4', '0', 'C', 'Y', '196', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('171', 'ROOT,ULT:VENDOR', 'default_products_layout_templates', '4', '0', 'G', 'products_multicolumns=Y&products_without_options=Y&short_list=Y', '194', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('172', 'ROOT,ULT:VENDOR', 'redirect_to_cart', '2', '0', 'C', 'N', '245', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('173', 'ROOT,ULT:VENDOR', 'checkout_redirect', '2', '0', 'C', 'N', '246', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('174', 'ROOT', 'min_admin_password_length', '15', '0', 'I', '5', '10', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('175', 'ROOT', 'admin_passwords_must_contain_mix', '15', '0', 'C', 'N', '20', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('176', 'ROOT', 'change_admin_password_on_first_login', '15', '0', 'C', 'N', '30', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('177', 'ROOT', 'admin_password_expiration_period', '15', '0', 'I', '0', '40', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('178', 'ROOT', 'cron_password', '15', '0', 'I', 'MYPASS', '50', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('179', 'ROOT', 'tax_calculation', '2', '0', 'S', 'subtotal', '55', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('180', 'ROOT,ULT:VENDOR', 'default_product_details_layout', '4', '0', 'S', 'default_template', '191', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('288', 'ROOT,ULT:VENDOR', 'product_details_in_tab', '4', '0', 'C', 'Y', '191', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('181', 'ROOT', 'send_feedback', '0', '0', 'C', '0', '10', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('182', 'ROOT,ULT:VENDOR', 'address_position', '2', '0', 'S', 'billing_first', '250', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('183', 'ROOT', 'changes_warning', '4', '0', 'C', 'Y', '250', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('184', 'ROOT,ULT:VENDOR', 'product_lists_thumbnail_width', '9', '0', 'U', '150', '100', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('185', 'ROOT,ULT:VENDOR', 'product_lists_thumbnail_height', '9', '0', 'U', '150', '110', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('186', 'ROOT,ULT:VENDOR', 'product_details_thumbnail_width', '9', '0', 'U', '320', '120', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('187', 'ROOT,ULT:VENDOR', 'product_details_thumbnail_height', '9', '0', 'U', '320', '130', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('188', 'ROOT,ULT:VENDOR', 'product_detailed_image_width', '9', '0', 'U', '', '140', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('189', 'ROOT,ULT:VENDOR', 'product_detailed_image_height', '9', '0', 'U', '', '150', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('190', 'ROOT,ULT:VENDOR', 'product_cart_thumbnail_width', '9', '0', 'U', '120', '160', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('191', 'ROOT,ULT:VENDOR', 'product_cart_thumbnail_height', '9', '0', 'U', '', '170', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('192', 'ROOT,ULT:VENDOR', 'category_lists_thumbnail_width', '9', '0', 'U', '120', '180', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('193', 'ROOT,ULT:VENDOR', 'category_lists_thumbnail_height', '9', '0', 'U', '', '190', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('194', 'ROOT,ULT:VENDOR', 'category_details_thumbnail_width', '9', '0', 'U', '120', '200', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('195', 'ROOT,ULT:VENDOR', 'category_details_thumbnail_height', '9', '0', 'U', '', '210', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('196', 'ROOT,ULT:VENDOR', 'category_detailed_image_width', '9', '0', 'U', '', '220', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('197', 'ROOT,ULT:VENDOR', 'category_detailed_image_height', '9', '0', 'U', '', '230', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('198', 'ROOT', 'store_optimization', '0', '0', 'I', 'dev', '15', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('199', 'ROOT', 'show_menu_descriptions', '4', '0', 'C', 'Y', '255', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('200', 'ROOT', 'hd_request_code', '0', '0', 'I', '', '0', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('201', 'ROOT', 'store_key', '0', '0', 'I', '', '0', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('202', 'ROOT', 'header_7006', '7', '0', 'H', '', '9', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('203', 'ROOT', 'header_1', '4', '0', 'H', '', '10', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('204', 'ROOT,VENDOR', 'header_7010', '10', '0', 'H', '', '10', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('205', 'ROOT,VENDOR', 'header_7018', '3', '0', 'H', '', '10', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('206', 'ROOT', 'header_10003', '1', '0', 'H', '', '20', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('207', 'ROOT,VENDOR', 'header_7003', '2', '0', 'H', '', '60', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('208', 'ROOT,VENDOR', 'header_2', '4', '0', 'H', '', '70', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('209', 'ROOT', 'header_10004', '1', '0', 'H', '', '70', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('210', 'ROOT,VENDOR', 'header_7004', '2', '0', 'H', '', '125', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('211', 'ROOT', 'header_10080', '4', '0', 'H', '', '199', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('212', 'ROOT,VENDOR', 'header_7024', '2', '0', 'H', '', '215', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('213', 'ROOT,VENDOR', 'header_7005', '2', '0', 'H', '', '235', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('215', 'ROOT,VENDOR', 'header_8048', '11', '0', 'H', '', '90', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('216', 'ROOT', 'header_8057', '2', '0', 'H', '', '310', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('217', 'ROOT,VENDOR', 'header_15000', '2', '0', 'H', '', '1000', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('219', 'ROOT', 'header_8158', '13', '0', 'H', '', '20', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('220', 'ROOT,VENDOR', 'header_8159', '4', '0', 'D', '', '190', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('221', 'ROOT,VENDOR', 'header_10070', '4', '0', 'H', '', '192', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('222', 'ROOT', 'header_10086', '0', '0', 'H', '', '190', 'N', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('223', 'ROOT,ULT:VENDOR', 'disregard_options_for_discounts', '2', '0', 'C', 'N', '306', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('272', 'ROOT', 'display_track_orders', '4', '0', 'C', 'Y', '191', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('274', 'ROOT', 'log_type_general', '12', '0', 'N', '#M#runtime=Y&deprecated=Y&', '10', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('275', 'ROOT,ULT:VENDOR', 'quick_registration', '2', '0', 'C', 'Y', '260', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('276', 'ROOT', 'ems_enabled', '7', '0', 'C', 'N', '80', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('289', 'ROOT,ULT:VENDOR', 'available_product_list_sortings', '4', '0', 'G', '#M#timestamp-desc=Y&product-asc=Y&product-desc=Y&price-asc=Y&price-desc=Y&popularity-desc=Y', '197', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('290', 'ROOT,ULT:VENDOR', 'disable_quick_view', '4', '0', 'C', 'N', '198', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('1002', 'ROOT', 'create_vendor_administrator_account', '16', '0', 'C', 'Y', '140', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('1003', 'ROOT', 'use_for_apply_for_vendor_account', '11', '0', 'C', 'Y', '180', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('1004', 'ROOT', 'include_shipping', '16', '0', 'C', 'Y', '200', 'Y', '');
INSERT INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('1005', 'ROOT', 'include_payment_surcharge', '16', '0', 'C', 'N', '200', 'Y', '');

DROP TABLE IF EXISTS ?:settings_sections;
CREATE TABLE `?:settings_sections` (
  `section_id` smallint(4) unsigned NOT NULL auto_increment,
  `parent_id` smallint(4) unsigned NOT NULL,
  `edition_type` set('NONE','ROOT','VENDOR','PRO:NONE','PRO:ROOT','MVE:NONE','MVE:ROOT','ULT:NONE','ULT:ROOT','ULT:VENDOR','ULT:VENDORONLY') NOT NULL default 'ROOT',
  `name` varchar(128) NOT NULL default '',
  `position` smallint(5) unsigned NOT NULL default '0',
  `type` enum('CORE','ADDON','TAB','SEPARATE_TAB') NOT NULL default 'CORE',
  PRIMARY KEY  (`section_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO ?:settings_sections (`section_id`, `parent_id`, `edition_type`, `name`, `position`, `type`) VALUES ('1', '0', 'ROOT', 'Emails', '0', 'CORE');
INSERT INTO ?:settings_sections (`section_id`, `parent_id`, `edition_type`, `name`, `position`, `type`) VALUES ('2', '0', 'ROOT,VENDOR', 'General', '10', 'CORE');
INSERT INTO ?:settings_sections (`section_id`, `parent_id`, `edition_type`, `name`, `position`, `type`) VALUES ('3', '0', 'ROOT,VENDOR', 'DHTML', '15', 'CORE');
INSERT INTO ?:settings_sections (`section_id`, `parent_id`, `edition_type`, `name`, `position`, `type`) VALUES ('4', '0', 'ROOT,VENDOR', 'Appearance', '20', 'CORE');
INSERT INTO ?:settings_sections (`section_id`, `parent_id`, `edition_type`, `name`, `position`, `type`) VALUES ('5', '0', 'ROOT', 'Company', '30', 'CORE');
INSERT INTO ?:settings_sections (`section_id`, `parent_id`, `edition_type`, `name`, `position`, `type`) VALUES ('7', '0', 'ROOT', 'Shippings', '40', 'CORE');
INSERT INTO ?:settings_sections (`section_id`, `parent_id`, `edition_type`, `name`, `position`, `type`) VALUES ('8', '0', 'ROOT,VENDOR', 'Sitemap', '80', 'CORE');
INSERT INTO ?:settings_sections (`section_id`, `parent_id`, `edition_type`, `name`, `position`, `type`) VALUES ('9', '0', 'ROOT,VENDOR', 'Thumbnails', '110', 'CORE');
INSERT INTO ?:settings_sections (`section_id`, `parent_id`, `edition_type`, `name`, `position`, `type`) VALUES ('10', '0', 'ROOT,VENDOR', 'Reports', '140', 'CORE');
INSERT INTO ?:settings_sections (`section_id`, `parent_id`, `edition_type`, `name`, `position`, `type`) VALUES ('11', '0', 'ROOT,VENDOR', 'Image_verification', '0', 'CORE');
INSERT INTO ?:settings_sections (`section_id`, `parent_id`, `edition_type`, `name`, `position`, `type`) VALUES ('12', '0', 'ROOT', 'Logging', '100', 'CORE');
INSERT INTO ?:settings_sections (`section_id`, `parent_id`, `edition_type`, `name`, `position`, `type`) VALUES ('13', '0', 'ROOT', 'Upgrade_center', '0', 'CORE');
INSERT INTO ?:settings_sections (`section_id`, `parent_id`, `edition_type`, `name`, `position`, `type`) VALUES ('15', '0', 'ROOT', 'Security', '0', 'CORE');
INSERT INTO ?:settings_sections (`section_id`, `parent_id`, `edition_type`, `name`, `position`, `type`) VALUES ('16', '0', 'ROOT', 'Suppliers', '0', 'CORE');

DROP TABLE IF EXISTS ?:settings_variants;
CREATE TABLE `?:settings_variants` (
  `variant_id` mediumint(8) unsigned NOT NULL auto_increment,
  `object_id` mediumint(8) unsigned NOT NULL default '0',
  `name` varchar(64) NOT NULL default '',
  `position` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`variant_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('1', '3', 'hide', '10');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('2', '3', 'warning', '20');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('3', '4', 'create', '5');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('4', '4', 'delete', '5');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('5', '4', 'update', '5');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('6', '7', 'manual', '1');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('7', '7', 'auto', '1');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('8', '15', 'Y', '20');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('9', '15', 'N', '10');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('10', '52', '%d/%m/%Y', '1');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('11', '52', '%d-%m-%Y', '2');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('12', '52', '%d.%m.%Y', '3');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('13', '52', '%m/%d/%Y', '4');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('14', '52', '%m-%d-%Y', '5');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('15', '52', '%m.%d.%Y', '6');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('16', '52', '%Y/%m/%d', '7');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('17', '52', '%Y-%m-%d', '8');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('18', '52', '%Y.%m.%d', '9');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('19', '52', '%b %e, %Y', '10');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('20', '52', '%d %b %Y', '11');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('21', '52', '%A, %B %e, %Y', '12');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('22', '52', '%A, %e %B %Y', '13');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('23', '64', 'position', '0');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('24', '64', 'product', '10');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('25', '64', 'price', '20');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('26', '66', '%H:%M', '1');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('27', '66', '%H.%M', '2');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('28', '66', '%I:%M %p', '3');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('29', '66', '%I.%M %p', '4');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('30', '66', '%H:%M:%S', '5');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('31', '66', '%H.%M.%S', '6');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('32', '66', '%I:%M:%S %p', '7');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('33', '66', '%I.%M.%S %p', '8');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('34', '70', 'P', '3');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('35', '70', 'S', '2');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('36', '71', 'Y', '1');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('37', '71', 'P', '3');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('38', '71', 'B', '2');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('39', '84', '%a, %b %e', '10');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('40', '84', '%B %e, %Y', '15');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('41', '84', '%m.%e.%y', '20');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('42', '84', '%a %b %e, %Y', '30');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('43', '84', '%b %e', '40');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('44', '85', '%U', '10');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('45', '85', '%m.%e', '20');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('46', '85', '%U, %b', '30');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('47', '86', '%B, %Y', '5');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('48', '86', '%B', '10');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('49', '86', '%m', '20');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('50', '86', '%b', '30');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('51', '86', '%m-%Y', '40');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('52', '87', '%Y', '10');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('53', '87', '%y', '20');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('54', '108', 'smtp', '10');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('55', '108', 'mail', '20');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('56', '108', 'sendmail', '30');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('57', '121', 'digits', '1');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('58', '121', 'letters', '2');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('59', '121', 'mixed', '3');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('60', '138', 'one_page', '10');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('62', '138', 'multi_page', '30');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('63', '139', 'Australia/Hobart', '700');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('64', '139', 'Pacific/Port_Moresby', '690');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('65', '139', 'Australia/Sydney', '680');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('66', '139', 'Australia/Brisbane', '670');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('67', '139', 'Australia/Darwin', '660');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('68', '139', 'Australia/Adelaide', '650');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('69', '139', 'Asia/Yakutsk', '640');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('70', '139', 'Asia/Seoul', '630');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('71', '139', 'Asia/Tokyo', '620');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('72', '139', 'Asia/Taipei', '610');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('73', '139', 'Australia/Perth', '600');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('74', '139', 'Asia/Singapore', '590');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('75', '139', 'Asia/Irkutsk', '580');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('76', '139', 'Asia/Shanghai', '570');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('77', '139', 'Asia/Krasnoyarsk', '560');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('78', '139', 'Asia/Bangkok', '550');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('79', '139', 'Asia/Rangoon', '540');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('80', '139', 'Asia/Colombo', '530');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('81', '139', 'Asia/Dhaka', '520');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('82', '139', 'Asia/Novosibirsk', '510');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('83', '139', 'Asia/Katmandu', '500');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('84', '139', 'Asia/Calcutta', '490');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('85', '139', 'Asia/Tashkent', '480');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('86', '139', 'Asia/Yekaterinburg', '470');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('87', '139', 'Asia/Kabul', '460');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('88', '139', 'Asia/Yerevan', '450');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('89', '139', 'Asia/Dubai', '440');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('90', '139', 'Asia/Tehran', '430');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('91', '139', 'Africa/Nairobi', '420');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('92', '139', 'Europe/Moscow', '410');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('93', '139', 'Asia/Riyadh', '400');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('94', '139', 'Asia/Baghdad', '390');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('95', '139', 'Asia/Jerusalem', '380');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('96', '139', 'Europe/Kiev', '370');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('97', '139', 'Africa/Johannesburg', '360');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('98', '139', 'Africa/Cairo', '350');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('99', '139', 'Europe/Minsk', '340');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('100', '139', 'Europe/Istanbul', '330');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('101', '139', 'Africa/Lagos', '320');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('102', '139', 'Europe/Paris', '300');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('103', '139', 'Europe/Warsaw', '310');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('104', '139', 'Europe/Budapest', '290');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('105', '139', 'Europe/Berlin', '280');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('106', '139', 'Europe/London', '270');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('107', '139', 'Africa/Reykjavik', '260');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('108', '139', 'Atlantic/Azores', '250');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('109', '139', 'Atlantic/Cape_Verde', '240');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('110', '139', 'Atlantic/South_Georgia', '230');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('111', '139', 'America/Sao_Paulo', '220');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('112', '139', 'Etc/GMT+3', '210');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('113', '139', 'America/Godthab', '200');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('114', '139', 'America/St_Johns', '190');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('115', '139', 'America/Halifax', '180');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('116', '139', 'America/La_Paz', '170');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('117', '139', 'America/Santiago', '160');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('118', '139', 'America/Bogota', '150');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('119', '139', 'America/New_York', '140');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('120', '139', 'Etc/GMT+5', '130');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('121', '139', 'America/Guatemala', '120');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('122', '139', 'America/Chicago', '110');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('123', '139', 'America/Mexico_City', '100');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('124', '139', 'America/Regina', '90');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('125', '139', 'America/Phoenix', '80');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('126', '139', 'America/Chihuahua', '70');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('127', '139', 'America/Denver', '60');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('128', '139', 'America/Los_Angeles', '50');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('129', '139', 'America/Anchorage', '40');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('130', '139', 'Pacific/Honolulu', '30');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('131', '139', 'Pacific/Apia', '20');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('132', '139', 'Etc/GMT+12', '10');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('133', '139', 'Asia/Vladivostok', '710');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('134', '139', 'Pacific/Guadalcanal', '720');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('135', '139', 'Pacific/Auckland', '730');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('136', '139', 'Pacific/Fiji', '740');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('137', '139', 'Pacific/Tongatapu', '750');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('138', '158', 'create', '5');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('139', '158', 'delete', '5');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('140', '158', 'update', '5');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('141', '158', 'status', '5');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('142', '159', 'create', '5');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('143', '159', 'delete', '5');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('144', '159', 'update', '5');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('145', '159', 'session', '5');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('146', '159', 'failed_login', '5');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('147', '160', 'create', '5');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('148', '160', 'delete', '5');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('149', '160', 'update', '5');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('150', '160', 'low_stock', '5');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('151', '161', 'create', '5');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('152', '161', 'delete', '5');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('153', '161', 'update', '5');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('154', '162', 'restore', '5');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('155', '162', 'backup', '5');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('156', '162', 'optimize', '5');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('157', '162', 'error', '5');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('158', '163', 'http', '5');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('159', '166', 'month_first', '1');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('160', '166', 'day_first', '2');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('161', '167', 'sunday_first', '1');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('162', '167', 'monday_first', '2');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('163', '179', 'unit_price', '10');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('164', '179', 'subtotal', '20');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('165', '182', 'billing_first', '1');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('166', '182', 'shipping_first', '2');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('167', '274', 'runtime', '1');
INSERT INTO ?:settings_variants (`variant_id`, `object_id`, `name`, `position`) VALUES ('168', '274', 'deprecated', '2');

DROP TABLE IF EXISTS ?:settings_vendor_values;
CREATE TABLE `?:settings_vendor_values` (
  `object_id` mediumint(8) unsigned NOT NULL,
  `company_id` int(11) unsigned NOT NULL,
  `value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`object_id`,`company_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO ?:states (`country_code`, `code`, `status`) VALUES ('BG', 'SF', 'A');
INSERT INTO ?:state_descriptions (`state_id`, `lang_code`, `state`) VALUES ((SELECT LAST_INSERT_ID()), (SELECT value FROM ?:settings WHERE option_name = 'admin_default_language' AND section_id = 'Appearance'), 'Sofia');

DROP TABLE IF EXISTS `?:block_descriptions`;
DROP TABLE IF EXISTS `?:block_links`;
DROP TABLE IF EXISTS `?:block_location_descriptions`;
DROP TABLE IF EXISTS `?:block_location_properties`;
DROP TABLE IF EXISTS `?:block_positions`;
DROP TABLE IF EXISTS `?:blocks`;
DROP TABLE IF EXISTS `?:quick_search`;
DROP TABLE IF EXISTS `?:se_queue`;
DROP TABLE IF EXISTS `?:settings`;
DROP TABLE IF EXISTS `?:settings_elements`;
DROP TABLE IF EXISTS `?:settings_subsections`;

DELETE FROM `?:payment_descriptions` WHERE payment_id IN (SELECT payment_id FROM ?:payments WHERE processor_id IN (SELECT processor_id FROM `?:payment_processors` WHERE processor_script IN ('linkpoint_api.php')));
DELETE FROM `?:payments` WHERE processor_id IN (SELECT processor_id FROM `?:payment_processors` WHERE processor_script IN ('linkpoint_api.php'));
DELETE FROM `?:payment_processors` WHERE processor_script IN ('linkpoint_api.php');
INSERT INTO `?:payment_processors` (`processor_id`, `processor`, `processor_script`, `processor_template`, `admin_template`, `callback`, `type`) VALUES
(80, 'eWAY Hosted Payment', 'eway_nz_uk.php', 'cc_outside.tpl', 'eway_nz_uk.tpl', 'Y', 'P'),
(82, 'DirectOne', 'direct_one.php', 'cc_outside.tpl', 'direct_one.tpl', 'N', 'P'),
(83, 'eMerchantPay (Secure Payment Form)', 'emerchantpay.php', 'cc_outside.tpl', 'emerchantpay.tpl', 'N', 'P'),
(84, 'PaysiteCash', 'paysitecash.php', 'cc_outside.tpl', 'paysitecash.tpl', 'N', 'P');
UPDATE `?:payment_processors` SET processor = 'FirstData [Connect]', processor_script = 'firstdata_connect.php', processor_template = 'cc_outside.tpl', admin_template = 'firstdata_connect.tpl', callback = 'N', type = 'P' WHERE processor_script = 'linkpoint_connect.php';
