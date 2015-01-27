DELETE FROM ?:privileges WHERE privilege LIKE 'view_events' OR privilege LIKE 'manage_events';
DELETE FROM ?:usergroup_privileges WHERE privilege LIKE 'view_events' OR privilege LIKE 'manage_events';

DROP TABLE IF EXISTS ?:giftreg_descriptions;
DROP TABLE IF EXISTS ?:giftreg_event_fields;
DROP TABLE IF EXISTS ?:giftreg_event_products;
DROP TABLE IF EXISTS ?:giftreg_event_subscribers;
DROP TABLE IF EXISTS ?:giftreg_events;
DROP TABLE IF EXISTS ?:giftreg_field_variants;
DROP TABLE IF EXISTS ?:giftreg_fields;

DELETE FROM ?:addons WHERE addon = 'gift_registry';
DELETE FROM ?:addon_descriptions WHERE addon = 'gift_registry';