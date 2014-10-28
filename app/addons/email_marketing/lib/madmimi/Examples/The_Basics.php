<?php
require(dirname(__FILE__) . '/../MadMimi.class.php');

// There are a total of four arguments that can be used on the next line. The first two are shown here, the second two
// are optional. The first of them is a debugger, which defaults to false, and the second, allows you to print
// the transaction ID when sending a message. It also defaults to false.
$mailer = new MadMimi('email', 'APIKEY'); 

$options = array('recipients' => 'Nicholas Young <nicholas@madmimi.com>', 
				 'promotion_name' => 'Untitled Promotion', 'subject' => 'You Gotta Read This', 
				 'from' => 'Mad Mailer <noreply@example.com>');
$body = array('greeting' => 'Hello', 'name' => 'Nicholas');
$mailer->SendMessage($options, $body);
?>