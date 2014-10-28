<?php
require(dirname(__FILE__) . '/../MadMimi.class.php');

// There are a total of four arguments that can be used on the next line. The first two are shown here, the second two
// are optional. The first of them is a debugger, which defaults to false, and the second, allows you to print
// the transaction ID when sending a message. It also defaults to false.
$mailer = new MadMimi('YOUR USERNAME (OR E-MAIL ADDRESS)', 'YOUR API KEY'); 

// Let's make a new list...
$mailer->NewList('Test');

// ...and then get all of the lists on this account (which should include the one we just created.)
$lists = $mailer->Lists();

// ...and loop through them.
foreach ($lists as $list) {
	echo $list['name'] . " => " . $list['id'] . "<br />";
}
?>