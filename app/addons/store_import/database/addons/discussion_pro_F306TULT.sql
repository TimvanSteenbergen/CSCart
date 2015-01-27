ALTER TABLE `?:discussion` ADD COLUMN `company_id` int(11) unsigned NOT NULL DEFAULT '0', ADD KEY `company_id` (`company_id`), DROP INDEX `object_id`, ADD UNIQUE `object_id` (`object_id`, `object_type`, `company_id`);
UPDATE `?:discussion` SET company_id = 1;
