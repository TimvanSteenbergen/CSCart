ALTER TABLE `?:banners` ADD COLUMN `company_id` int(11) unsigned NOT NULL DEFAULT '0', ADD KEY `company_id` (`company_id`);
UPDATE `?:banners` SET company_id = 1;
