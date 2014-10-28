ALTER TABLE `?:recurring_plans` ADD COLUMN `company_id` int(11) unsigned NOT NULL DEFAULT '0', ADD KEY `company_id` (`company_id`);
UPDATE `?:recurring_plans` SET company_id = 1;
