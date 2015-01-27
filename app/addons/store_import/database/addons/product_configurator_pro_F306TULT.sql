ALTER TABLE `?:conf_classes` ADD COLUMN `company_id` int(11) unsigned NOT NULL DEFAULT '0', ADD KEY `company_id` (`company_id`);
ALTER TABLE `?:conf_groups` ADD COLUMN `company_id` int(11) unsigned NOT NULL DEFAULT '0', ADD KEY `company_id` (`company_id`);
ALTER TABLE `?:conf_steps` ADD COLUMN `company_id` int(11) unsigned NOT NULL DEFAULT '0', ADD KEY `company_id` (`company_id`);
UPDATE `?:conf_classes` SET company_id = 1;
UPDATE `?:conf_groups` SET company_id = 1;
UPDATE `?:conf_steps` SET company_id = 1;
