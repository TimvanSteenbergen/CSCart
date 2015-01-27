<?php
require(dirname(__FILE__) . '/../MadMimi.class.php');

// There are a total of four arguments that can be used on the next line. The first two are shown here, the second two
// are optional. The first of them is a debugger, which defaults to false, and the second, allows you to print
// the transaction ID when sending a message. It also defaults to false.
$mailer = new MadMimi('', ''); 

// Create a list
$mailer->NewList('My Awesome List');

// Adding an audience member with just an email address
// NOTE: This list must exist
$mailer->AddMembership('My Awesome List', 'support@madmimi.com');

// Adding an audience member with some additional fields
// NOTE:
$mailer->AddMembership('My Awesome List', 'help@madmimi.com', array('first_name' => 'Mad Mimi', 'last_name' => 'Help!'));

// Removing an audience member
// NOTE: This does not delete the audience member, just removes them from the list
$mailer->RemoveMembership('My Awesome List', 'support@madmimi.com');

// Deleting the list
// NOTE: This does not delete the audience members
$mailer->DeleteList('My Awesome List');
?>