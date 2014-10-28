UPDATE ?:reward_points SET object_type = 'A' WHERE object_type = 'G';
UPDATE ?:reward_points SET company_id = 0 WHERE object_type != 'A';
