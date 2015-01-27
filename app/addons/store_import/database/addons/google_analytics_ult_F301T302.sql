UPDATE ?:settings_sections SET edition_type = 'ROOT,ULT:VENDOR' WHERE name = 'google_analytics';
UPDATE ?:settings_sections SET edition_type = 'ROOT,ULT:VENDOR' WHERE parent_id IN (SELECT * FROM (SELECT section_id FROM ?:settings_sections WHERE name = 'google_analytics') as t);
UPDATE ?:settings_objects SET edition_type = 'ROOT,ULT:VENDOR' WHERE section_id IN (SELECT section_id FROM ?:settings_sections WHERE name = 'google_analytics');
