UPDATE ?:pages SET company_id = 1 WHERE page_id = 40;

REPLACE INTO ?:ult_objects_sharing (`share_company_id`, `share_object_id`, `share_object_type`)
	SELECT cc.company_id, '40', 'pages' FROM ?:companies cc;
