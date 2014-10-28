ALTER TABLE `?:sessions` CHANGE `session_id` `session_id` varchar(64) NOT NULL DEFAULT '';
ALTER TABLE `?:stored_sessions` CHANGE `session_id` `session_id` varchar(64) NOT NULL;
ALTER TABLE `?:user_session_products` CHANGE `session_id` `session_id` varchar(64) NOT NULL DEFAULT '';

UPDATE `?:privileges` SET privilege = 'edit_files' WHERE privilege = 'edit_templates';
