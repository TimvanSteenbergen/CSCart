ALTER TABLE `?:banner_descriptions` ADD `url` varchar(255) NOT NULL DEFAULT '';
DROP TABLE IF EXISTS `?:banner_images`;
CREATE TABLE `?:banner_images` (  `banner_image_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,  `banner_id` mediumint(8) unsigned NOT NULL DEFAULT '0',  `lang_code` char(2) NOT NULL DEFAULT '',  PRIMARY KEY (`banner_image_id`),  UNIQUE KEY `banner` (`banner_id`,`lang_code`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;
