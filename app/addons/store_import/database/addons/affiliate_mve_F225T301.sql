ALTER TABLE `?:aff_banners`
  ADD COLUMN `company_id` int(11) unsigned NULL DEFAULT '0';

ALTER TABLE `?:aff_groups`
  ADD COLUMN `company_id` int(11) unsigned NULL DEFAULT '0';

ALTER TABLE `?:affiliate_plans`
  ADD COLUMN `company_id` int(11) unsigned NULL DEFAULT '0';
