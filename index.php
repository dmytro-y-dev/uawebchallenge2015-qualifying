<?php

require_once 'collage.inc.php';

$config = array(
	'userId' 												 => 'aldanisdarkwood',
	'avatarsMaximumCount' 					 => 10, // if 0, then all from the wall
	'avatarsSizeReflectsTweetsCount' => false
);

$users = GetUsersFromTwitterWall($config['userId'], $config['avatarsMaximumCount']);

if ($config['avatarsSizeReflectsTweetsCount']) {
	$avatarSizes = GetUsersAvatarSizes($users);

	include 'views/collage-page-with-proportion.phtml';
} else {
	include 'views/collage-page.phtml';
}
