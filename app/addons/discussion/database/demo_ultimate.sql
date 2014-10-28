UPDATE ?:discussion SET company_id = 1;
DELETE FROM ?:discussion WHERE thread_id = 6;
DELETE FROM ?:discussion_messages WHERE thread_id = 6;
DELETE FROM ?:discussion_posts WHERE thread_id = 6;
DELETE FROM ?:discussion_rating WHERE thread_id = 6;
