DELETE FROM ?:privileges WHERE privilege LIKE 'manage_statistics' OR privilege LIKE 'view_statistics';
DELETE FROM ?:usergroup_privileges WHERE privilege LIKE 'manage_statistics' OR privilege LIKE 'view_statistics';

DROP TABLE IF EXISTS ?:stat_browsers;
DROP TABLE IF EXISTS ?:stat_ips;
DROP TABLE IF EXISTS ?:stat_languages;
DROP TABLE IF EXISTS ?:stat_product_search;
DROP TABLE IF EXISTS ?:stat_requests;
DROP TABLE IF EXISTS ?:stat_search_engines;
DROP TABLE IF EXISTS ?:stat_search_phrases;
DROP TABLE IF EXISTS ?:stat_sessions;
DROP TABLE IF EXISTS ?:stat_banners_log;
DELETE FROM ?:addons WHERE addon = 'statistics';
DELETE FROM ?:addon_descriptions WHERE addon = 'statistics';