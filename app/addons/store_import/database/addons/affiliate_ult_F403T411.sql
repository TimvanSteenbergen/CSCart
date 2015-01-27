DROP TABLE IF EXISTS ?:affiliate_payouts;
DROP TABLE IF EXISTS ?:affiliate_plans;
DROP TABLE IF EXISTS ?:aff_action_links;
DROP TABLE IF EXISTS ?:aff_banners;
DROP TABLE IF EXISTS ?:aff_banner_descriptions;
DROP TABLE IF EXISTS ?:aff_group_descriptions;
DROP TABLE IF EXISTS ?:aff_groups;
DROP TABLE IF EXISTS ?:aff_partner_actions;
DROP TABLE IF EXISTS ?:aff_partner_profiles;

DELETE FROM ?:privileges WHERE privilege = 'manage_affiliate';
DELETE FROM ?:usergroup_privileges WHERE privilege = 'manage_affiliate';

DELETE FROM ?:addons WHERE addon = 'affiliate';
DELETE FROM ?:addon_descriptions WHERE addon = 'affiliate';

DELETE FROM ?:user_profiles WHERE user_id IN (SELECT user_id FROM ?:users WHERE user_type = 'P');
DELETE FROM ?:user_session_products WHERE user_id IN (SELECT user_id FROM ?:users WHERE user_type = 'P');
DELETE FROM ?:user_data WHERE user_id IN (SELECT user_id FROM ?:users WHERE user_type = 'P');
UPDATE ?:orders SET user_id = 0 WHERE user_id IN (SELECT user_id FROM ?:users WHERE user_type = 'P');
DELETE FROM ?:usergroup_links WHERE user_id IN (SELECT user_id FROM ?:users WHERE user_type = 'P');
DELETE FROM ?:users WHERE user_type = 'P';
