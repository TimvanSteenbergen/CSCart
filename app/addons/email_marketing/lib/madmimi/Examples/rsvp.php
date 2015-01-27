<?php
/*
	Mimi RSVP
	
	Description:
	So many people want to use Mimi to create an RSVP system, 
	that I decided to just do an example. :-) This is far from
	perfect or complete, but a very good starting point for doing
	so via the API.
	
	Author:
	Nicholas Young <nicholas@madmimi.com>
	
	Last Updated:
	June 16, 2010
	
	Copyright 2010 Mad Mimi LLC <nicholas@madmimi.com>. 
	Released under the MIT license. Check it, yo.
*/

/*
	Require and set up the API wrapper. This is important stuff,
	so pay attention!
*/
require('MadMimi.class.php');
$mimi = new MadMimi('your mimi email', 'APIKEY');

/*
	First, a few extensions to the Mad Mimi for PHP core...
	This keeps things simple, and clean.
*/
function check_lists($mimi_object, $lists_array) {
	foreach($lists_array as $list) {
		if (!strstr($mimi_object->Lists(), $list)) {
			$mimi_object->NewList($list);
		}
	}
}

$params = $_GET;
$action_values = array('yes' => 'yes', 'no' => 'no', 'maybe' => 'maybe');
$lists = array('yes' => 'Yes RSVP', 'no' => 'No RSVP', 'maybe' => 'Maybe RSVP');
$messages = array(	"yes" => "Dude! Thanks for confirming!",
					"no" => "Ahhh, bummer. You will be missed.",
					"maybe" => "Can't you make up your mind? I mean, seriously my event will kick ass!");
/*
	First off, let's make sure we have the lists
*/
if ($params['setup']) {
	check_lists($mimi, $lists);
	echo "All done!";
}

/* 
	To confirm your RSVP (see below), it's just a GET request to 
	rsvp.php?rsvp=yes&email=nicholas@madmimi.com 
	(but replace with the recipient's email).
*/
if ($params['rsvp'] == 'yes') {
	$user = array('email' => $params['email'], 'add_list' => $lists['yes']);

	$mimi->AddUser($user); // I would actually consider doing requests like this in the background, unless real-time is essential.
	// Feed this back out however you want. I'm just echoing for an example.
	// (and it's the same for all instances below this, too.)
	echo $messages[$params['rsvp']];
}

/*
	So, a user RSVP'd with no. Let's add 'em to the proper list
*/
if ($params['rsvp'] == 'no') {
	$user = array('email' => $params['email'], 'add_list' => $lists['no']);
	$mimi->AddUser($user);
	echo $messages[$params['rsvp']];
}

/*
	Finally, let's take care of those stupid noncommittal folks.
	This action is a tribute to their undecided-ness. To you, decide
	will ya?
*/
if ($params['rsvp'] == 'maybe') {
	$user = array('email' => $params['email'], 'add_list' => $lists['maybe']);
	$mimi->AddUser($user);
	echo $messages[$params['rsvp']];
}
?>