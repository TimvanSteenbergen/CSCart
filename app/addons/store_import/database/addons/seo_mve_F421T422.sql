ALTER TABLE ?:seo_redirects ADD COLUMN lang_code char(2) NOT NULL default "";
UPDATE ?:seo_redirects SET lang_code = (SELECT value FROM ?:settings_objects WHERE name = "frontend_default_language");
