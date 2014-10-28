REPLACE INTO ?:news (news_id, company_id, date, separate, status) VALUES ('2', '1', '1328126400', 'N', 'A');
REPLACE INTO ?:news (news_id, company_id, date, separate, status) VALUES ('3', '1', '1333137600', 'N', 'A');
REPLACE INTO ?:news (news_id, company_id, date, separate, status) VALUES ('4', '1', '1335211200', 'N', 'A');

REPLACE INTO `?:newsletters` (`newsletter_id`, `sent_date`, `status`, `type`, `mailing_lists`, `abandoned_type`, `abandoned_days`) VALUES ('1','0','A','N','', 'both', 0);
REPLACE INTO `?:newsletters` (`newsletter_id`, `sent_date`, `status`, `type`, `mailing_lists`, `abandoned_type`, `abandoned_days`) VALUES ('2','0','A','T','', 'both', 0);

REPLACE INTO ?:mailing_lists (`list_id`, `timestamp`, `from_email`, `from_name`, `reply_to`, `show_on_checkout`, `show_on_registration`, `status`) VALUES ('1', '0', 'no-reply@example.com', 'Acme', 'no-reply@example.com', '1', '1', 'A');