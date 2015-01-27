REPLACE INTO ?:polls (`page_id`, `start_date`, `end_date`, `show_results`) VALUES ('40', '0', '0', 'V');

REPLACE INTO ?:poll_items (`item_id`, `parent_id`, `type`, `position`, `required`, `page_id`) VALUES ('1', '40', 'Q', '1', 'Y', '40');
REPLACE INTO ?:poll_items (`item_id`, `parent_id`, `type`, `position`, `required`, `page_id`) VALUES ('2', '1', 'A', '1', '', '40');
REPLACE INTO ?:poll_items (`item_id`, `parent_id`, `type`, `position`, `required`, `page_id`) VALUES ('3', '1', 'A', '2', '', '40');
REPLACE INTO ?:poll_items (`item_id`, `parent_id`, `type`, `position`, `required`, `page_id`) VALUES ('4', '1', 'A', '3', '', '40');
REPLACE INTO ?:poll_items (`item_id`, `parent_id`, `type`, `position`, `required`, `page_id`) VALUES ('5', '1', 'A', '4', '', '40');
REPLACE INTO ?:poll_items (`item_id`, `parent_id`, `type`, `position`, `required`, `page_id`) VALUES ('6', '1', 'O', '5', '', '40');
REPLACE INTO ?:pages (page_id, parent_id, id_path, status, page_type, position, timestamp, localization, new_window, related_ids, use_avail_period, avail_from_timestamp, avail_till_timestamp) VALUES ('40', '0', '40', 'A', 'P', '0', '1251115200', '', '0', '', 'N', '0', '0');
