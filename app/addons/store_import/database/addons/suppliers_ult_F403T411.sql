ALTER TABLE ?:supplier_links DROP PRIMARY KEY;
ALTER TABLE ?:supplier_links ADD PRIMARY KEY (`supplier_id`,`object_id`,`object_type`);
ALTER TABLE ?:supplier_links ADD KEY `object_id` (`object_id`,`object_type`);
