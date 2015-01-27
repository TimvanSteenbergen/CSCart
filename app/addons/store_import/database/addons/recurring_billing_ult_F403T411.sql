DROP TABLE IF EXISTS ?:recurring_plans;
DELETE FROM ?:common_descriptions WHERE object_holder = 'recurring_plans';
DROP TABLE IF EXISTS ?:recurring_subscriptions;
DROP TABLE IF EXISTS ?:recurring_events;
ALTER TABLE ?:usergroups DROP COLUMN `recurring_plans_ids`;

DELETE FROM ?:privileges WHERE privilege = 'manage_recurring_plans';
DELETE FROM ?:privileges WHERE privilege = 'manage_subscriptions';
DELETE FROM ?:usergroup_privileges WHERE privilege = 'manage_recurring_plans';
DELETE FROM ?:usergroup_privileges WHERE privilege = 'manage_subscriptions';

DELETE FROM ?:addons WHERE addon = 'recurring_billing';
DELETE FROM ?:addon_descriptions WHERE addon = 'recurring_billing';

DELETE FROM ?:product_tabs_descriptions WHERE tab_id IN (SELECT tab_id FROM ?:product_tabs WHERE addon = 'recurring_billing');
DELETE FROM ?:product_tabs WHERE addon = 'recurring_billing';
