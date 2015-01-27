ALTER TABLE ?:recurring_events` ADD KEY (`subscription_id`);

CREATE INDEX `status` ON `?:recurring_subscriptions`(`status`);
