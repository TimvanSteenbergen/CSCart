UPDATE `?:shipping_services` SET status='A', carrier='USP', module='usps', code='Standard Post M', sp_file='' WHERE service_id='48';
UPDATE `?:shipping_services` SET status='A', carrier='USP', module='usps', code='Standard Post N', sp_file='' WHERE service_id='49';
UPDATE `?:shipping_services` SET status='A', carrier='USP', module='usps', code='First Class Package International Service', sp_file='' WHERE service_id='204';

UPDATE `?:language_values` SET value='Machinable (First-Class Mail or Standard Post)' WHERE name='ship_usps_machinable';
UPDATE `?:language_values` SET value='USPS Tracking/Delivery confirmation' WHERE name='usps_service_delivery_confirmation';

UPDATE `?:shipping_service_descriptions` SET description='USPS Standard Machinable' WHERE service_id='48';
UPDATE `?:shipping_service_descriptions` SET description='USPS Standard Non Machinable' WHERE service_id='49';
