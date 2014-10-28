<?php
require(dirname(__FILE__) . '/../MadMimi.class.php');

// There are a total of four arguments that can be used on the next line. The first two are shown here, the second two
// are optional. The first of them is a debugger, which defaults to false, and the second, allows you to print
// the transaction ID when sending a message. It also defaults to false.
$mailer = new MadMimi('YOUR USERNAME (OR E-MAIL ADDRESS)', 'YOUR API KEY'); 

// Let's create a new user array, and add that user to a list.
// Note, in this user's array, we have a bunch of custom fields, and an add_list key - that lets us
// add this user to a specific list! If the user is already a member of your audience, just give it the
// email and add_list keys, and you're good to go.
$user = array('email' => 'emailaddress@example.com', 'firstName' => 'nicholas', 'lastName' => 'young', 'Music' => 'Rock and roll', 'add_list' => 'Test List 2');

$mailer->AddUser($user);
?>