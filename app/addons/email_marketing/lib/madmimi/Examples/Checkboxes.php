<?php
/*
	In this file, we're going to make some checkboxes, and auto-select which ones the user belongs to.
	Note: My execution probably isn't the prettiest, and I encourage you to modify this for your own usage,
	rather than just using it as-is.
*/
require(dirname(__FILE__) . '/../MadMimi.class.php');

// There are a total of four arguments that can be used on the next line. The first two are shown here, the second two
// are optional. The first of them is a debugger, which defaults to false, and the second, allows you to print
// the transaction ID when sending a message. It also defaults to false.
$mailer = new MadMimi('YOUR USERNAME (OR E-MAIL ADDRESS)', 'YOUR API KEY'); 

// Get all lists for this account..
$lists = $mailer->Lists();
$memberships = $mailer->Memberships('rockandroll@example.com');
echo '<form name="user_lists" method="POST" action="">';
foreach ($lists as $list) {
	foreach ($memberships as $membership) {
		if ((int)$list['id'] == (int)$membership['id']) {
			echo $list['name'] . ' <input type="checkbox" name="' . $list['name'] . '" checked="true"> <br />';
		} else {
			echo $list['name'] . ' <input type="checkbox" name="' . $list['name'] . '"> <br />';
		}
	}
}
echo '<input type="submit" value="Submit">';
echo '</form>';
?>