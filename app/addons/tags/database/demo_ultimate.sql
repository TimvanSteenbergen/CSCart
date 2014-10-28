UPDATE ?:tags SET company_id = 1;
REPLACE INTO ?:tags (`tag_id`, `company_id`, `tag`, `timestamp`, `status`) VALUES (4, 1, 'Sport', 1328701593, 'A');

UPDATE ?:tag_links SET tag_id = 4 WHERE tag_id = 3 AND object_type = 'P' AND object_id = 131 AND user_id = 3;
UPDATE ?:tag_links SET tag_id = 4 WHERE tag_id = 3 AND object_type = 'P' AND object_id = 187 AND user_id = 3;