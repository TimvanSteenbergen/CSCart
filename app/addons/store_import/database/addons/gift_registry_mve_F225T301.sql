
ALTER TABLE `?:giftreg_event_products`
  MODIFY COLUMN `extra` text NULL;

ALTER TABLE `?:giftreg_events`
  ADD COLUMN `company_id` int(11) unsigned NULL DEFAULT '0';
