REPLACE INTO ?:banners (banner_id, status, type, target, timestamp) VALUES (2, 'D', 'G', 'T', '1184702400');
REPLACE INTO ?:banners (banner_id, status, type, target, timestamp) VALUES (3, 'A', 'G', 'T', '1328040000');
REPLACE INTO ?:banners (banner_id, status, type, target, timestamp) VALUES (4, 'A', 'G', 'T', '1328040000');
REPLACE INTO ?:banners (banner_id, status, type, target, timestamp) VALUES (5, 'A', 'G', 'T', '1328040000');

REPLACE INTO ?:images (`image_id`, `image_path`, `image_x`, `image_y`) VALUES(189, 'common_image_1.jpg', 171, 149);
REPLACE INTO ?:images (`image_id`, `image_path`, `image_x`, `image_y`) VALUES(190, 'common_image_2.gif', 171, 170);
REPLACE INTO ?:images (`image_id`, `image_path`, `image_x`, `image_y`) VALUES(233, 'banner_1.jpg', 940, 400);
REPLACE INTO ?:images (`image_id`, `image_path`, `image_x`, `image_y`) VALUES(234, 'banner_2.jpg', 940, 400);
REPLACE INTO ?:images (`image_id`, `image_path`, `image_x`, `image_y`) VALUES(235, 'banner_3.jpg', 940, 400);

REPLACE INTO ?:images_links (`pair_id`, `object_id`, `object_type`, `image_id`, `detailed_id`, `type`, `position`) VALUES(136, 1, 'promo', 189, 0, 'M', 0);
REPLACE INTO ?:images_links (`pair_id`, `object_id`, `object_type`, `image_id`, `detailed_id`, `type`, `position`) VALUES(137, 2, 'promo', 190, 0, 'M', 0);
REPLACE INTO ?:images_links (`pair_id`, `object_id`, `object_type`, `image_id`, `detailed_id`, `type`, `position`) VALUES(177, 3, 'promo', 233, 0, 'M', 0);
REPLACE INTO ?:images_links (`pair_id`, `object_id`, `object_type`, `image_id`, `detailed_id`, `type`, `position`) VALUES(178, 4, 'promo', 234, 0, 'M', 0);
REPLACE INTO ?:images_links (`pair_id`, `object_id`, `object_type`, `image_id`, `detailed_id`, `type`, `position`) VALUES(179, 5, 'promo', 235, 0, 'M', 0);