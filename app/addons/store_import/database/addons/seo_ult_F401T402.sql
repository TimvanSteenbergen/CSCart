UPDATE IGNORE ?:seo_names SET company_id = 0 WHERE type IN ('e', 'a', 'n');
DELETE FROM ?:seo_names WHERE company_id != 0 AND type IN ('e', 'a', 'n');
