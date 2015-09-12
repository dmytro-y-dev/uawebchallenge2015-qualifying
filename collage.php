<?php

require_once 'collage.inc.php';

$config = array(
	'userId' 												 => $GET['login'],
	'avatarsMaximumCount' 					 => $GET['size'],
	'avatarsSizeReflectsTweetsCount' => $GET['proportion']
);

$users = GetUsersFromTwitterWall($config['userId'], $config['avatarsMaximumCount']);

if ($config['avatarsSizeReflectsTweetsCount']) {
	$avatarSizes = GetUsersAvatarSizes($users);

	include 'views/collage-page-with-proportion.phtml';
} else {
	include 'views/collage-page.phtml';
}
