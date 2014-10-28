ALTER TABLE ?:store_locations ADD `company_id` int(11) unsigned NOT NULL default '0';
UPDATE ?:addons SET version = '2.0' WHERE addon = 'store_locator';
