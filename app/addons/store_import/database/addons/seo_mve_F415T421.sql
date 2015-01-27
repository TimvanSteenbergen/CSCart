ALTER TABLE ?:seo_names ADD `path` varchar(255) NOT NULL DEFAULT '';
DROP TABLE IF EXISTS ?:seo_redirects;
CREATE TABLE ?:seo_redirects (
    `redirect_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
    `src` varchar(255) NOT NULL DEFAULT '',
    `dest` varchar(255) NOT NULL DEFAULT '',
    `type` char(1) NOT NULL DEFAULT 's',
    `object_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
    `company_id` int(11) unsigned NOT NULL DEFAULT '0',
    `lang_code` char(2) not null default '',
    PRIMARY KEY (`redirect_id`),
    KEY `src`  (`src`,`company_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

UPDATE ?:settings_objects_upg SET value = 'category_nohtml' WHERE value = 'category' AND name = 'seo_category_type';
UPDATE ?:settings_objects_upg SET value = 'category' WHERE value = 'file' AND name = 'seo_category_type';

DELETE FROM ?:seo_names WHERE company_id NOT IN (SELECT company_id FROM ?:companies) AND company_id != 0;
DELETE FROM ?:seo_names WHERE lang_code NOT IN (SELECT lang_code FROM ?:languages);
