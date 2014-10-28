<?php
require(dirname(__FILE__) . '/../MadMimi.class.php');

$body_array = array('dumped_text' => 'This is my YAML value! It has :colons : and a real messy t37953784625&*^#%*Q^%# bunch of stuff.');

$options = array(	'promotion_name' => 'Spyc YAML Test', 'recipients' => 'Nicholas Young <nicholas@madmimi.com>',
					'from' => 'Mad Mailer <madmailer@madmimi.com>', 'subject' => 'Spyc YAML Test');

$mailer = new MadMimi('YOUR EMAIL ADDRESS', 'FAKE API KEY');

$mailer->SendMessage($options, $body_array);
?>