ALTER TABLE `?:reward_points` DROP INDEX `unique_key`;

ALTER TABLE `?:reward_points`
  ADD COLUMN `company_id` int(11) unsigned NULL DEFAULT '0';

ALTER TABLE `?:reward_points` ADD UNIQUE KEY `unique_key`(`object_id`,`usergroup_id`,`object_type`,`company_id`);
