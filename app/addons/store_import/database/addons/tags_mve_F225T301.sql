ALTER TABLE `?:tags` DROP INDEX `tag`;

ALTER TABLE `?:tags`
  ADD COLUMN `company_id` int(11) unsigned NULL DEFAULT '0';

ALTER TABLE `?:tags` ADD UNIQUE KEY `tag`(`tag`,`company_id`);